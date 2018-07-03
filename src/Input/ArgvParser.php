<?php

namespace Ahc\Cli\Input;

use Ahc\Cli\Helper\InflectsString;
use Ahc\Cli\Helper\OutputHelper;
use Ahc\Cli\Output\Writer;

/**
 * Argv parser for the cli.
 *
 * @author  Jitendra Adhikari <jiten.adhikary@gmail.com>
 * @license MIT
 *
 * @link    https://github.com/adhocore/cli
 */
class ArgvParser extends Parser
{
    use InflectsString;

    /** @var string */
    protected $_version;

    /** @var string */
    protected $_name;

    /** @var string */
    protected $_desc;

    /** @var callable[] Events for options */
    protected $_events = [];

    /** @var bool Whether to allow unknown (not registered) options */
    protected $_allowUnknown = false;

    /**
     * Constructor.
     *
     * @param string $name
     * @param string $desc
     * @param bool   $allowUnknown
     */
    public function __construct(string $name, string $desc = '', bool $allowUnknown = false)
    {
        $this->_name         = $name;
        $this->_desc         = $desc;
        $this->_allowUnknown = $allowUnknown;

        $this->defaults()->onExit(function () {
            exit(0);
        });
    }

    protected function defaults(): self
    {
        $this->option('-h, --help', 'Show help')->on([$this, 'showHelp']);
        $this->option('-V, --version', 'Show version')->on([$this, 'showVersion']);
        $this->option('-v, --verbosity', 'Verbosity level', null, 0)->on(function () {
            $this->_values['verbosity']++;

            return false;
        });

        return $this;
    }

    /**
     * Sets version.
     *
     * @param string $version
     *
     * @return self
     */
    public function version(string $version): self
    {
        $this->_version = $version;

        return $this;
    }

    public function name(): string
    {
        return $this->_name;
    }

    public function desc(): string
    {
        return $this->_desc;
    }

    /**
     * Registers argument definitions (all at once). Only last one can be variadic.
     *
     * @param string $definitions
     *
     * @return self
     */
    public function arguments(string $definitions): self
    {
        $definitions = \explode(' ', $definitions);
        foreach ($definitions as $i => $definition) {
            $argument = new Argument($definition);

            if ($argument->variadic() && isset($definitions[$i + 1])) {
                throw new \InvalidArgumentException('Only last argument can be variadic');
            }

            $name = $argument->attributeName();

            $this->ifAlreadyRegistered($name, $argument);

            $this->_arguments[$name] = $argument;
            $this->_values[$name]    = $argument->default();
        }

        return $this;
    }

    /**
     * Registers new option.
     *
     * @param string        $cmd
     * @param string        $desc
     * @param callable|null $filter
     * @param mixed         $default
     *
     * @return self
     */
    public function option(string $cmd, string $desc = '', callable $filter = null, $default = null): self
    {
        $option = new Option($cmd, $desc, $default, $filter);
        $name   = $option->attributeName();

        $this->ifAlreadyRegistered($name, $option);

        $this->_options[$name] = $option;
        $this->_values[$name]  = $option->default();

        return $this;
    }

    protected function ifAlreadyRegistered(string $name, Parameter $param)
    {
        if (\array_key_exists($name, $this->_values)) {
            throw new \InvalidArgumentException(\sprintf(
                'The parameter "%s" is already registered',
                $param instanceof Option ? $param->long() : $param->name()
            ));
        }
    }

    /**
     * Sets event handler for last option.
     *
     * @param callable $fn
     *
     * @return self
     */
    public function on(callable $fn): self
    {
        \end($this->_options);

        $this->_events[\key($this->_options)] = $fn;

        return $this;
    }

    /**
     * Register exit handler.
     *
     * @param callable $fn
     *
     * @return self
     */
    public function onExit(callable $fn): self
    {
        $this->_events['_exit'] = $fn;

        return $this;
    }

    protected function handleUnknown(string $arg, string $value = null)
    {
        if ($this->_allowUnknown) {
            $this->_values[$this->toCamelCase($arg)] = $value;

            return;
        }

        $values = \array_filter($this->_values, function ($value) {
            return $value !== null;
        });

        // Has some value, error!
        if ($values) {
            throw new \RuntimeException(
                \sprintf('Option "%s" not registered', $arg)
            );
        }

        // Has no value, show help!
        return $this->showHelp();
    }

    /**
     * Get values indexed by camelized attribute name.
     *
     * @param bool $withDefaults
     *
     * @return array
     */
    public function values(bool $withDefaults = true): array
    {
        $values            = $this->_values;
        $values['version'] = $this->_version;

        if (!$withDefaults) {
            unset($values['help'], $values['version']);
        }

        return $values;
    }

    /**
     * Magic getter for specific value by its key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->_values[$key] ?? null;
    }

    public function args(): array
    {
        return \array_diff_key($this->_values, $this->_options);
    }

    protected function showHelp()
    {
        (new Writer)
            ->bold("Command {$this->_name}, version {$this->_version}", true)->eol()
            ->comment($this->_desc, true)->eol()
            ->bold('Usage: ')->yellow("{$this->_name} [OPTIONS...] [ARGUMENTS...]", true);

        $helper = new OutputHelper;

        $helper->showArgumentsHelp($this->_arguments);
        $helper->showOptionsHelp($this->_options, '', 'Legend: <required> [optional]');

        return $this->emit('_exit');
    }

    protected function showVersion()
    {
        (new Writer)->bold($this->_version, true);

        return $this->emit('_exit');
    }

    public function emit(string $event, $value = null)
    {
        if (empty($this->_events[$event])) {
            return;
        }

        $callback = $this->_events[$event];

        return $callback($value);
    }

    public function tap($object)
    {
        return $object;
    }
}
