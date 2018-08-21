<?php

/*
 * This file is part of the PHP-CLI package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

namespace Ahc\Cli;

use Ahc\Cli\Exception\InvalidArgumentException;
use Ahc\Cli\Helper\OutputHelper;
use Ahc\Cli\Input\Command;
use Ahc\Cli\IO\Interactor;

/**
 * A cli application.
 *
 * @author  Jitendra Adhikari <jiten.adhikary@gmail.com>
 * @license MIT
 *
 * @link    https://github.com/adhocore/cli
 */
class Application
{
    /** @var Command[] */
    protected $commands = [];

    /** @var array Raw argv sent to parse() */
    protected $argv = [];

    /** @var array Command aliases [alias => cmd] */
    protected $aliases = [];

    /** @var string */
    protected $name;

    /** @var string App version */
    protected $version = '0.0.1';

    /** @var string Ascii art logo */
    protected $logo = '';

    protected $default = '__default__';

    /** @var Interactor */
    protected $io;

    public function __construct(string $name, string $version = '', callable $onExit = null)
    {
        $this->name    = $name;
        $this->version = $version;

        // @codeCoverageIgnoreStart
        $this->onExit = $onExit ?? function ($exitCode = 0) {
            exit($exitCode);
        };
        // @codeCoverageIgnoreEnd

        $this->command('__default__', 'Default command', '', true)->on([$this, 'showHelp'], 'help');
    }

    /**
     * Get the name.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Get the version.
     *
     * @return string
     */
    public function version(): string
    {
        return $this->version;
    }

    /**
     * Get the commands.
     *
     * @return Command[]
     */
    public function commands(): array
    {
        $commands = $this->commands;

        unset($commands['__default__']);

        return $commands;
    }

    /**
     * Get the raw argv.
     *
     * @return array
     */
    public function argv(): array
    {
        return $this->argv;
    }

    /**
     * Sets or gets the ASCII art logo.
     *
     * @param string|null $logo
     *
     * @return string|self
     */
    public function logo(string $logo = null)
    {
        if (\func_num_args() === 0) {
            return $this->logo;
        }

        $this->logo = $logo;

        return $this;
    }

    /**
     * Add a command by its name desc alias etc.
     *
     * @param string $name
     * @param string $desc
     * @param string $alias
     * @param bool   $allowUnknown
     * @param bool   $default
     *
     * @return Command
     */
    public function command(
        string $name,
        string $desc = '',
        string $alias = '',
        bool $allowUnknown = false,
        bool $default = false
    ): Command {
        $command = new Command($name, $desc, $allowUnknown, $this);

        $this->add($command, $alias, $default);

        return $command;
    }

    /**
     * Add a prepred command.
     *
     * @param Command $command
     * @param string  $alias
     * @param bool    $default
     *
     * @return self
     */
    public function add(Command $command, string $alias = '', bool $default = false): self
    {
        $name = $command->name();

        if ($this->commands[$name] ?? $this->aliases[$name] ?? $this->commands[$alias] ?? $this->aliases[$alias] ?? null) {
            throw new InvalidArgumentException(\sprintf('Command "%s" already added', $name));
        }

        if ($alias) {
            $command->alias($alias);
            $this->aliases[$alias] = $name;
        }

        if ($default) {
            $this->default = $name;
        }

        $this->commands[$name] = $command->version($this->version)->onExit($this->onExit)->bind($this);

        return $this;
    }

    /**
     * Gets matching command for given argv.
     *
     * @param array $argv
     *
     * @return Command
     */
    public function commandFor(array $argv): Command
    {
        $argv += [null, null, null];

        return
             // cmd
            $this->commands[$argv[1]]
            // cmd alias
            ?? $this->commands[$this->aliases[$argv[1]] ?? null]
            // default.
            ?? $this->commands[$this->default];
    }

    /**
     * Gets or sets io.
     *
     * @param Interactor|null $io
     *
     * @return Interactor|self
     */
    public function io(Interactor $io = null)
    {
        if ($io || !$this->io) {
            $this->io = $io ?? new Interactor;
        }

        if (\func_num_args() === 0) {
            return $this->io;
        }

        return $this;
    }

    /**
     * Parse the arguments via the matching command but dont execute action..
     *
     * @param array $argv Cli arguments/options.
     *
     * @return Command The matched and parsed command (or default)
     */
    public function parse(array $argv): Command
    {
        $this->argv = $argv;

        $command = $this->commandFor($argv);
        $aliases = $this->aliasesFor($command);

        // Eat the cmd name!
        foreach ($argv as $i => $arg) {
            if (\in_array($arg, $aliases)) {
                unset($argv[$i]);

                break;
            }

            if ($arg[0] === '-') {
                break;
            }
        }

        return $command->parse($argv);
    }

    /**
     * Handle the request, invoke action and call exit handler.
     *
     * @param array $argv
     *
     * @return mixed
     */
    public function handle(array $argv)
    {
        if (\count($argv) < 2) {
            return $this->showHelp();
        }

        $exitCode = 255;

        try {
            $command = $this->parse($argv);
            $this->doAction($command);
            $exitCode = 0;
        } catch (\Throwable $e) {
            $this->outputHelper()->printTrace($e);
        }

        return ($this->onExit)($exitCode);
    }

    /**
     * Get aliases for given command.
     *
     * @param Command $command
     *
     * @return array
     */
    protected function aliasesFor(Command $command): array
    {
        $aliases = [$name = $command->name()];

        foreach ($this->aliases as $alias => $command) {
            if (\in_array($name, [$alias, $command])) {
                $aliases[] = $alias;
                $aliases[] = $command;
            }
        }

        return $aliases;
    }

    /**
     * Show help of all commands.
     *
     * @return mixed
     */
    public function showHelp()
    {
        $writer = $this->io()->writer();
        $header = "{$this->name}, version {$this->version}";
        $footer = 'Run `<command> --help` for specific help';

        if ($this->logo) {
            $writer->write($this->logo, true);
        }

        $this->outputHelper()->showCommandsHelp($this->commands(), $header, $footer);

        return ($this->onExit)();
    }

    protected function outputHelper(): OutputHelper
    {
        $writer = $this->io()->writer();

        return new OutputHelper($writer);
    }

    /**
     * Invoke command action.
     *
     * @param Command $command
     *
     * @return mixed
     */
    protected function doAction(Command $command)
    {
        if ($command->name() === '__default__') {
            return $this->notFound();
        }

        // Let the command collect more data (if missing or needs confirmation)
        $command->interact($this->io());

        if (!$command->action() && !\method_exists($command, 'execute')) {
            return;
        }

        $params = [];
        $values = $command->values();
        // We prioritize action to be in line with commander.js!
        $action = $command->action() ?? [$command, 'execute'];

        foreach ($this->getActionParameters($action) as $param) {
            $params[] = $values[$param->getName()] ?? null;
        }

        return $action(...$params);
    }

    /**
     * Command not found handler.
     *
     * @return mixed
     */
    protected function notFound()
    {
        $closest   = [];
        $attempted = $this->argv[1];
        $available = \array_keys($this->commands() + $this->aliases);

        foreach ($available as $cmd) {
            $lev = \levenshtein($attempted, $cmd);
            if ($lev > 0 || $lev < 5) {
                $closest[$cmd] = $lev;
            }
        }

        $this->io()->error("Command $attempted not found", true);
        if ($closest) {
            \asort($closest);
            $closest = \key($closest);
            $this->io()->bgRed("Did you mean $closest?", true);
        }

        return ($this->onExit)(127);
    }

    protected function getActionParameters(callable $action): array
    {
        $reflex = \is_array($action)
            ? (new \ReflectionClass($action[0]))->getMethod($action[1])
            : new \ReflectionFunction($action);

        return $reflex->getParameters();
    }
}
