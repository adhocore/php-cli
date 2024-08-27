<?php

/*
 * This file is part of the PHP-CLI package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

namespace Ahc\Cli\Helper;

use Ahc\Cli\Exception;
use Ahc\Cli\Input\Argument;
use Ahc\Cli\Input\Command;
use Ahc\Cli\Input\Groupable;
use Ahc\Cli\Input\Option;
use Ahc\Cli\Input\Parameter;
use Ahc\Cli\Output\Writer;
use Throwable;

use function array_map;
use function array_shift;
use function asort;
use function explode;
use function get_class;
use function gettype;
use function implode;
use function is_array;
use function is_object;
use function is_scalar;
use function key;
use function levenshtein;
use function max;
use function method_exists;
use function preg_replace;
use function preg_replace_callback;
use function realpath;
use function str_contains;
use function str_pad;
use function str_replace;
use function strlen;
use function strrpos;
use function trim;
use function uasort;
use function var_export;

use const STR_PAD_LEFT;

/**
 * This helper helps you by showing you help information :).
 *
 * @author  Jitendra Adhikari <jiten.adhikary@gmail.com>
 * @license MIT
 *
 * @link    https://github.com/adhocore/cli
 */
class OutputHelper
{
    protected Writer $writer;

    /** @var int Max width of command name */
    protected int $maxCmdName = 0;

    public function __construct(?Writer $writer = null)
    {
        $this->writer = $writer ?? new Writer;
    }

    /**
     * Print stack trace and error msg of an exception.
     */
    public function printTrace(Throwable $e): void
    {
        $eClass = get_class($e);

        $this->writer->colors(
            "{$eClass} <red>{$e->getMessage()}</end><eol/>" .
            "(thrown in <yellow>{$e->getFile()}</end><white>:{$e->getLine()})</end>"
        );

        // @codeCoverageIgnoreStart
        if ($e instanceof Exception) {
            // Internal exception traces are not printed.
            return;
        }
        // @codeCoverageIgnoreEnd

        $traceStr = '<eol/><eol/><bold>Stack Trace:</end><eol/><eol/>';

        foreach ($e->getTrace() as $i => $trace) {
            $trace += ['class' => '', 'type' => '', 'function' => '', 'file' => '', 'line' => '', 'args' => []];
            $symbol = $trace['class'] . $trace['type'] . $trace['function'];
            $args   = $this->stringifyArgs($trace['args']);

            $traceStr .= "  <comment>$i)</end> <red>$symbol</end><comment>($args)</end>";
            if ('' !== $trace['file']) {
                $file      = realpath($trace['file']);
                $traceStr .= "<eol/>     <yellow>at $file</end><white>:{$trace['line']}</end><eol/>";
            }
        }

        $this->writer->colors($traceStr);
    }

    public function stringifyArgs(array $args): string
    {
        $holder = [];

        foreach ($args as $arg) {
            $holder[] = $this->stringifyArg($arg);
        }

        return implode(', ', $holder);
    }

    protected function stringifyArg($arg): string
    {
        if (is_scalar($arg)) {
            return var_export($arg, true);
        }

        if (is_object($arg)) {
            return method_exists($arg, '__toString') ? (string) $arg : get_class($arg);
        }

        if (is_array($arg)) {
            return '[' . $this->stringifyArgs($arg) . ']';
        }

        return gettype($arg);
    }

    /**
     * @param Argument[] $arguments
     * @param string     $header
     * @param string     $footer
     *
     * @return self
     */
    public function showArgumentsHelp(array $arguments, string $header = '', string $footer = ''): self
    {
        $this->showHelp('Arguments', $arguments, $header, $footer);

        return $this;
    }

    /**
     * @param Option[] $options
     * @param string   $header
     * @param string   $footer
     *
     * @return self
     */
    public function showOptionsHelp(array $options, string $header = '', string $footer = ''): self
    {
        $this->showHelp('Options', $options, $header, $footer);

        return $this;
    }

    /**
     * @param Command[] $commands
     * @param string    $header
     * @param string    $footer
     *
     * @return self
     */
    public function showCommandsHelp(array $commands, string $header = '', string $footer = ''): self
    {
        $this->maxCmdName = $commands ? max(array_map(static fn (Command $cmd) => strlen($cmd->name()), $commands)) : 0;

        $this->showHelp('Commands', $commands, $header, $footer);

        return $this;
    }

    /**
     * Show help with headers and footers.
     */
    protected function showHelp(string $for, array $items, string $header = '', string $footer = ''): void
    {
        if ($header) {
            $this->writer->bold($header, true);
        }

        $this->writer->eol()->boldGreen($for . ':', true);

        if (empty($items)) {
            $this->writer->bold('  (n/a)', true);

            return;
        }

        $space = 4;
        $group = $lastGroup = null;

        $withDefault = $for === 'Options' || $for === 'Arguments';
        foreach ($this->sortItems($items, $padLen, $for) as $item) {
            $name  = $this->getName($item);
            if ($for === 'Commands' && $lastGroup !== $group = $item->group()) {
                $this->writer->boldYellow($group ?: '*', true);
                $lastGroup = $group;
            }
            $desc  = str_replace(["\r\n", "\n"], str_pad("\n", $padLen + $space + 3), $item->desc($withDefault));

            $this->writer->bold('  ' . str_pad($name, $padLen + $space));
            $this->writer->comment($desc, true);
        }

        if ($footer) {
            $this->writer->eol()->yellow($footer, true);
        }
    }

    /**
     * Show usage examples of a Command.
     *
     * It replaces $0 with actual command name and properly pads ` ## ` segments.
     */
    public function showUsage(string $usage): self
    {
        $usage = str_replace('$0', $_SERVER['argv'][0] ?? '[cmd]', $usage);

        if (!str_contains($usage, ' ## ')) {
            $this->writer->eol()->boldGreen('Usage Examples:', true)->colors($usage)->eol();

            return $this;
        }

        $lines = explode("\n", str_replace(['<eol>', '<eol/>', '</eol>', "\r\n"], "\n", $usage));
        foreach ($lines as $i => &$pos) {
            if (false === $pos = strrpos(preg_replace('~</?\w+/?>~', '', $pos), ' ##')) {
                unset($lines[$i]);
            }
        }

        $maxlen = ($lines ? max($lines) : 0) + 4;
        $usage  = preg_replace_callback('~ ## ~', static function () use (&$lines, $maxlen) {
            return str_pad('# ', $maxlen - array_shift($lines), ' ', STR_PAD_LEFT);
        }, $usage);

        $this->writer->eol()->boldGreen('Usage Examples:', true)->colors($usage)->eol();

        return $this;
    }

    public function showCommandNotFound(string $attempted, array $available): self
    {
        $closest = [];
        foreach ($available as $cmd) {
            $lev = levenshtein($attempted, $cmd);
            if ($lev > 0 || $lev < 5) {
                $closest[$cmd] = $lev;
            }
        }

        $this->writer->error("Command $attempted not found", true);
        if ($closest) {
            asort($closest);
            $closest = key($closest);
            $this->writer->bgRed("Did you mean $closest?", true);
        }

        return $this;
    }

    /**
     * Sort items by name. As a side effect sets max length of all names.
     *
     * @param Parameter[]|Command[] $items
     * @param int                   $max
     * @param string                $for
     *
     * @return array
     */
    protected function sortItems(array $items, &$max = 0, string $for = ''): array
    {
        $max = max(array_map(fn ($item) => strlen($this->getName($item)), $items));

        if ($for === 'Arguments') { // Arguments are positional so must not be sorted
            return $items;
        }

        uasort($items, static function ($a, $b) {
            $aName = $a instanceof Groupable ? $a->group() . $a->name() : $a->name();
            $bName = $b instanceof Groupable ? $b->group() . $b->name() : $b->name();

            return $aName <=> $bName;
        });

        return $items;
    }

    /**
     * Prepare name for different items.
     *
     * @param Parameter|Command $item
     *
     * @return string
     */
    protected function getName($item): string
    {
        $name = $item->name();

        if ($item instanceof Command) {
            return trim(str_pad($name, $this->maxCmdName) . ' ' . $item->alias());
        }

        return $this->label($item);
    }

    /**
     * Get parameter label for humans.
     */
    protected function label(Parameter $item): string
    {
        $name = $item->name();

        if ($item instanceof Option) {
            $name = $item->short() . '|' . $item->long();
        }

        $variad = $item->variadic() ? '...' : '';

        if ($item->required()) {
            return "<$name$variad>";
        }

        return "[$name$variad]";
    }
}
