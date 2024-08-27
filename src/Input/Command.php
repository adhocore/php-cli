<?php

/*
 * This file is part of the PHP-CLI package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

namespace Ahc\Cli\Input;

use Ahc\Cli\Application as App;
use Ahc\Cli\Exception\InvalidParameterException;
use Ahc\Cli\Exception\RuntimeException;
use Ahc\Cli\Helper\InflectsString;
use Ahc\Cli\Helper\OutputHelper;
use Ahc\Cli\IO\Interactor;
use Ahc\Cli\Output\ProgressBar;
use Ahc\Cli\Output\Writer;
use Closure;

use function array_filter;
use function array_keys;
use function end;
use function explode;
use function func_num_args;
use function sprintf;
use function str_contains;
use function strstr;

/**
 * Parser aware Command for the cli (based on tj/commander.js).
 *
 * @author  Jitendra Adhikari <jiten.adhikary@gmail.com>
 * @license MIT
 *
 * @link    https://github.com/adhocore/cli
 */
class Command extends Parser implements Groupable
{
    use InflectsString;

    protected $_action = null;

    protected string $_group;

    protected string $_version = '';

    protected string $_usage = '';

    protected ?string $_alias = null;

    private array $_events = [];

    private bool $_argVariadic = false;

    /**
     * Constructor.
     *
     * @param string $name
     * @param string $desc
     * @param bool   $allowUnknown
     * @param App    $app
     */
    public function __construct(
        protected string $_name,
        protected string $_desc = '',
        protected bool $_allowUnknown = false,
        protected ?App $_app = null
    ) {
        $this->defaults();
        $this->inGroup(str_contains($_name, ':') ? strstr($_name, ':', true) : '');
    }

    /**
     * Sets default options, actions and exit handler.
     */
    protected function defaults(): self
    {
        $this->option('-h, --help', 'Show help')->on([$this, 'showHelp']);
        $this->option('-V, --version', 'Show version')->on([$this, 'showVersion']);
        $this->option('-v, --verbosity', 'Verbosity level', null, 0)->on(
            fn () => $this->set('verbosity', ($this->verbosity ?? 0) + 1) && false
        );

        $this->onExit(static fn ($exitCode = 0) => exit($exitCode));

        return $this;
    }

    /**
     * Sets version.
     */
    public function version(string $version): self
    {
        $this->_version = $version;

        return $this;
    }

    /**
     * Gets command name.
     */
    public function name(): string
    {
        return $this->_name;
    }

    /**
     * Gets command description.
     */
    public function desc(): string
    {
        return $this->_desc;
    }

    /**
     * Sets command group.
     */
    public function inGroup(string $group): self
    {
        $this->_group = $group;

        return $this;
    }

    /**
     * Gets command group.
     */
    public function group(): string
    {
        return $this->_group;
    }

    /**
     * Get the app this command belongs to.
     */
    public function app(): ?App
    {
        return $this->_app;
    }

    /**
     * Bind command to the app.
     */
    public function bind(?App $app = null): self
    {
        $this->_app = $app;

        return $this;
    }

    /**
     * Registers argument definitions (all at once). Only last one can be variadic.
     */
    public function arguments(string $definitions): self
    {
        $definitions = explode(' ', $definitions);

        foreach ($definitions as $raw) {
            $this->argument($raw);
        }

        return $this;
    }

    /**
     * Register an argument.
     */
    public function argument(string $raw, string $desc = '', $default = null): self
    {
        $argument = new Argument($raw, $desc, $default);

        if ($this->_argVariadic) {
            throw new InvalidParameterException('Only last argument can be variadic');
        }

        if ($argument->variadic()) {
            $this->_argVariadic = true;
        }

        $this->register($argument);

        return $this;
    }

    /**
     * Registers new option.
     */
    public function option(string $raw, string $desc = '', ?callable $filter = null, $default = null): self
    {
        $option = new Option($raw, $desc, $default, $filter);

        $this->register($option);

        return $this;
    }

    /**
     * Gets user options (i.e without defaults).
     */
    public function userOptions(): array
    {
        $options = $this->allOptions();

        unset($options['help'], $options['version'], $options['verbosity']);

        return $options;
    }

    /**
     * Gets or sets usage info.
     *
     * @param string|null $usage
     *
     * @return string|self
     */
    public function usage(?string $usage = null)
    {
        if (func_num_args() === 0) {
            return $this->_usage;
        }

        $this->_usage = $usage;

        return $this;
    }

    /**
     * Gets or sets alias.
     *
     * @param string|null $alias
     *
     * @return string|self
     */
    public function alias(?string $alias = null)
    {
        if (func_num_args() === 0) {
            return $this->_alias;
        }

        $this->_alias = $alias;

        return $this;
    }

    /**
     * Sets event handler for last (or given) option.
     */
    public function on(callable $fn, ?string $option = null): self
    {
        $names = array_keys($this->allOptions());

        $this->_events[$option ?? end($names)] = $fn;

        return $this;
    }

    /**
     * Register exit handler.
     */
    public function onExit(callable $fn): self
    {
        $this->_events['_exit'] = $fn;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function handleUnknown(string $arg, ?string $value = null): mixed
    {
        if ($this->_allowUnknown) {
            return $this->set($this->toCamelCase($arg), $value);
        }

        $values = array_filter($this->values(false));

        // Has some value, error!
        if ($values) {
            throw new RuntimeException(
                sprintf('Option "%s" not registered', $arg)
            );
        }

        // Has no value, show help!
        return $this->showHelp();
    }

    /**
     * Shows command help then aborts.
     */
    public function showHelp(): mixed
    {
        $io     = $this->io();
        $helper = new OutputHelper($io->writer());

        $io->bold("Command {$this->_name}, version {$this->_version}", true)->eol();
        $io->comment($this->_desc, true)->eol();
        $io->bold('Usage: ')->yellow("{$this->_name} [OPTIONS...] [ARGUMENTS...]", true);

        $helper
            ->showArgumentsHelp($this->allArguments())
            ->showOptionsHelp($this->allOptions(), '', 'Legend: <required> [optional] variadic...');

        if ($this->_usage) {
            $helper->showUsage($this->_usage);
        }

        return $this->emit('_exit', 0);
    }

    /**
     * Shows command version then aborts.
     */
    public function showVersion(): mixed
    {
        $this->writer()->bold($this->_version, true);

        return $this->emit('_exit', 0);
    }

    /**
     * {@inheritdoc}
     */
    public function emit(string $event, $value = null): mixed
    {
        if (empty($this->_events[$event])) {
            return null;
        }

        return ($this->_events[$event])($value);
    }

    /**
     * Tap return given object or if that is null then app instance. This aids for chaining.
     */
    public function tap(?object $object = null)
    {
        return $object ?? $this->_app;
    }

    /**
     * Performs user interaction if required to set some missing values.
     */
    public function interact(Interactor $io): void
    {
        // Subclasses will do the needful.
    }

    /**
     * Get or set command action.
     *
     * @param callable|null $action If provided it is set
     *
     * @return callable|self If $action provided then self, otherwise the preset action.
     */
    public function action(?callable $action = null)
    {
        if (func_num_args() === 0) {
            return $this->_action;
        }

        $this->_action = $action instanceof Closure ? Closure::bind($action, $this) : $action;

        return $this;
    }

    /**
     * Get a writer instance.
     */
    protected function writer(): Writer
    {
        return $this->_app ? $this->_app->io()->writer() : new Writer;
    }

    /**
     * Get IO instance.
     */
    protected function io(): Interactor
    {
        return $this->_app ? $this->_app->io() : new Interactor;
    }

    /**
     * Get ProgressBar instance.
     */
    protected function progress(?int $total = null): ProgressBar
    {
        return new ProgressBar($total, $this->writer());
    }
}
