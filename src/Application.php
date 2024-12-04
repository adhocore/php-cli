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
use ReflectionClass;
use ReflectionFunction;
use Throwable;

use function array_diff_key;
use function array_fill_keys;
use function array_keys;
use function count;
use function func_num_args;
use function in_array;
use function is_array;
use function is_int;
use function method_exists;

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
    /**
     * Locale of CLI.
     */
    public static $locale = 'en';

    /**
     * list of translations for each supported locale.
     *
     * @var array<string, array>
     *
     * @example
     * ```php
     *  ['locale' => ['key1' => 'value1', 'key2' => 'value2']]
     * ```
     */
    public static $locales = [];

    /** @var Command[] */
    protected array $commands = [];

    /** @var array Raw argv sent to parse() */
    protected array $argv = [];

    /** @var array Command aliases [alias => cmd] */
    protected array $aliases = [];

    /** @var string Ascii art logo */
    protected string $logo = '';

    /** @var string Custom help screen */
    protected string $help = '';

    /** @var string Name of default command */
    protected string $default = '__default__';

    /** @var null|Interactor */
    protected ?Interactor $io = null;

    /** @var callable The callable to perform exit */
    protected $onExit;

    /** @var callable The callable to catch exception, receives exception & exit code, may rethrow exception or may exit program */
    protected $onException = null;

    public function __construct(protected string $name, protected string $version = '0.0.1', ?callable $onExit = null)
    {
        $this->onExit = $onExit ?? static fn (int $exitCode = 0) => exit($exitCode);

        $this->command('__default__', 'Default command', '', true)->on([$this, 'showHelp'], 'help');
    }

    /**
     * Get the name.
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Get the version.
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
    public function logo(?string $logo = null)
    {
        if (func_num_args() === 0) {
            return $this->logo;
        }

        $this->logo = $logo;

        return $this;
    }

    public static function addLocale(string $locale, array $texts, bool $default = false)
    {
        if ($default) {
            self::$locale = $locale;
        }

        self::$locales[$locale] = $texts;
    }

    /**
     * Add a command by its name desc alias etc and return command.
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
     * Add a prepared command and return itself.
     */
    public function add(Command $command, string $alias = '', bool $default = false): self
    {
        $name = $command->name();

        if (
            $this->commands[$name] ??
            $this->aliases[$name] ??
            $this->commands[$alias] ??
            $this->aliases[$alias] ??
            null
        ) {
            throw new InvalidArgumentException(t('Command "%s" already added', [$name]));
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
     * Set the default command.
     *
     * @param string $commandName The name of the default command
     *
     * @throws InvalidArgumentException If the specified command name does not exist
     *
     * @return self The application
     */
    public function defaultCommand(string $commandName): self
    {
        if (!isset($this->commands[$commandName])) {
            throw new InvalidArgumentException(t('Command "%s" does not exist', [$commandName]));
        }

        $this->default = $commandName;

        return $this;
    }

    /**
     * Get the default command.
     *
     * @return string|null The name of the default command, or null if not set
     */
    public function getDefaultCommand(): ?string
    {
        return $this->default;
    }

    /**
     * Groups commands set within the callable.
     *
     * @param string   $group The group name
     * @param callable $fn    The callable that recieves Application instance and adds commands.
     *
     * @return self
     */
    public function group(string $group, callable $fn): self
    {
        $old = array_fill_keys(array_keys($this->commands), true);

        $fn($this);
        foreach (array_diff_key($this->commands, $old) as $cmd) {
            $cmd->inGroup($group);
        }

        return $this;
    }

    /**
     * Gets matching command for given argv.
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
    public function io(?Interactor $io = null)
    {
        if ($io || !$this->io) {
            $this->io = $io ?? new Interactor;
        }

        if (func_num_args() === 0) {
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
            if (in_array($arg, $aliases)) {
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
     * Sets exception handler callback.
     *
     * The callback receives exception & exit code. It may rethrow exception
     * or may exit the program or just log exception and do nothing else.
     */
    public function onException(callable $fn): self
    {
        $this->onException = $fn;

        return $this;
    }

    /**
     * Handle the request, invoke action and call exit handler.
     */
    public function handle(array $argv): mixed
    {
        if ($this->default === '__default__' && count($argv) < 2) {
            return $this->showHelp();
        }

        $exitCode = 255;

        try {
            $command  = $this->parse($argv);
            $result   = $this->doAction($command);
            $exitCode = is_int($result) ? $result : 0;
        } catch (Throwable $e) {
            isset($this->onException) && ($this->onException)($e, $exitCode);
            $this->outputHelper()->printTrace($e);
        }

        return ($this->onExit)($exitCode);
    }

    /**
     * Get aliases for given command.
     */
    protected function aliasesFor(Command $command): array
    {
        $aliases = [$name = $command->name()];

        foreach ($this->aliases as $alias => $cmd) {
            if (in_array($name, [$alias, $cmd], true)) {
                $aliases[] = $alias;
                $aliases[] = $cmd;
            }
        }

        return $aliases;
    }

    /**
     * Sets or gets the custom help screen contents.
     *
     * @param string|null $help
     *
     * @return string|self
     */
    public function help(?string $help = null): mixed
    {
        if (func_num_args() === 0) {
            return $this->help;
        }

        $this->help = $help;

        return $this;
    }

    /**
     * Show custom help screen if one is set, otherwise shows the default one.
     */
    public function showHelp(): mixed
    {
        if ($help = $this->help()) {
            $writer = $this->io()->writer();
            $writer->write($help, true);

            return ($this->onExit)();
        }

        return $this->showDefaultHelp();
    }

    /**
     * Shows command help then aborts.
     */
    public function showDefaultHelp(): mixed
    {
        $writer = $this->io()->writer();
        $header = "{$this->name}, " . t('version') . " {$this->version}";
        $footer = t('Run `<command> --help` for specific help');

        if ($this->logo) {
            $writer->logo($this->logo, true);
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
     */
    protected function doAction(Command $command): mixed
    {
        if ($command->name() === '__default__') {
            return $this->notFound();
        }

        // Let the command collect more data (if missing or needs confirmation)
        $command->interact($this->io());

        if (!$command->action() && !method_exists($command, 'execute')) {
            return null;
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
     */
    protected function notFound(): mixed
    {
        $available = array_keys($this->commands() + $this->aliases);
        $this->outputHelper()->showCommandNotFound($this->argv[1], $available);

        return ($this->onExit)(127);
    }

    protected function getActionParameters(callable $action): array
    {
        $reflex = is_array($action)
            ? (new ReflectionClass($action[0]))->getMethod($action[1])
            : new ReflectionFunction($action);

        return $reflex->getParameters();
    }
}
