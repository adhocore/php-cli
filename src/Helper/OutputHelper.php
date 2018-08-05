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

use Ahc\Cli\Input\Argument;
use Ahc\Cli\Input\Command;
use Ahc\Cli\Input\Option;
use Ahc\Cli\Input\Parameter;
use Ahc\Cli\Output\Writer;

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
    /** @var Writer */
    protected $writer;

    /** @var int Max width of command name */
    protected $maxCmdName;

    public function __construct(Writer $writer = null)
    {
        $this->writer = $writer ?? new Writer;
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
        $this->maxCmdName = $commands ? \max(\array_map(function (Command $cmd) {
            return \strlen($cmd->name());
        }, $commands)) : 0;

        $this->showHelp('Commands', $commands, $header, $footer);

        return $this;
    }

    /**
     * Show help with headers and footers.
     *
     * @param string $for
     * @param array  $items
     * @param string $header
     * @param string $footer
     *
     * @return void
     */
    protected function showHelp(string $for, array $items, string $header = '', string $footer = '')
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
        foreach ($this->sortItems($items, $padLen) as $item) {
            $name = $this->getName($item);
            $this->writer->bold('  ' . \str_pad($name, $padLen + $space));
            $this->writer->comment($item->desc(), true);
        }

        if ($footer) {
            $this->writer->eol()->yellow($footer, true);
        }
    }

    /**
     * Sort items by name. As a side effect sets max length of all names.
     *
     * @param Parameter[]|Command[] $items
     * @param int                   $max
     *
     * @return array
     */
    protected function sortItems(array $items, &$max = 0): array
    {
        $max = \max(\array_map(function ($item) {
            return \strlen($this->getName($item));
        }, $items));

        \uasort($items, function ($a, $b) {
            /* @var Parameter $b */
            /* @var Parameter $a */
            return $a->name() <=> $b->name();
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
            return \trim(\str_pad($name, $this->maxCmdName) . ' ' . $item->alias());
        }

        return $this->label($item);
    }

    /**
     * Get parameter label for humans.
     *
     * @param Parameter $item
     *
     * @return string
     */
    protected function label(Parameter $item)
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
