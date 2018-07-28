<?php

namespace Ahc\Cli\Input;

use Ahc\Cli\Exception\InvalidParameterException;
use Ahc\Cli\Exception\RuntimeException;
use Ahc\Cli\Helper\Normalizer;

/**
 * Argv parser for the cli.
 *
 * @author  Jitendra Adhikari <jiten.adhikary@gmail.com>
 * @license MIT
 *
 * @link    https://github.com/adhocore/cli
 */
abstract class Parser
{
    /** @var string|null The last seen variadic option name */
    protected $_lastVariadic;

    /** @var Normalizer */
    protected $_normalizer;

    /** @var Option[] Registered options */
    private $_options = [];

    /** @var Argument[] Registered arguments */
    private $_arguments = [];

    /** @var array Parsed values indexed by option name */
    private $_values = [];

    /**
     * Parse the argv input.
     *
     * @param array $argv The first item is ignored.
     *
     * @throws \RuntimeException When argument is missing or invalid.
     *
     * @return self
     */
    public function parse(array $argv): self
    {
        $this->_normalizer = new Normalizer;

        \array_shift($argv);

        $argv    = $this->_normalizer->normalizeArgs($argv);
        $count   = \count($argv);
        $literal = false;

        for ($i = 0; $i < $count; $i++) {
            list($arg, $nextArg) = [$argv[$i], $argv[$i + 1] ?? null];

            if ($arg === '--') {
                $literal = true;
            } elseif ($arg[0] !== '-' || $literal) {
                $this->parseArgs($arg);
            } else {
                $i += (int) $this->parseOptions($arg, $nextArg);
            }
        }

        $this->validate();

        return $this;
    }

    /**
     * Parse single arg.
     *
     * @param string $arg
     *
     * @return mixed
     */
    protected function parseArgs(string $arg)
    {
        if ($this->_lastVariadic) {
            return $this->set($this->_lastVariadic, $arg, true);
        }

        if (!$argument = \reset($this->_arguments)) {
            return $this->set(null, $arg);
        }

        $this->setValue($argument, $arg);

        // Otherwise we will always collect same arguments again!
        if (!$argument->variadic()) {
            \array_shift($this->_arguments);
        }
    }

    /**
     * Parse an option, emit its event and set value.
     *
     * @param string      $arg
     * @param string|null $nextArg
     *
     * @return bool Whether to eat next arg.
     */
    protected function parseOptions(string $arg, string $nextArg = null): bool
    {
        $value = \substr($nextArg, 0, 1) === '-' ? null : $nextArg;

        if (null === $option  = $this->optionFor($arg)) {
            return $this->handleUnknown($arg, $value);
        }

        $this->_lastVariadic = $option->variadic() ? $option->attributeName() : null;

        return false === $this->emit($option->attributeName(), $value) ? false : $this->setValue($option, $value);
    }

    /**
     * Get matching option by arg (name) or null.
     *
     * @param string $arg
     *
     * @return Option|null
     */
    protected function optionFor(string $arg)
    {
        foreach ($this->_options as $option) {
            if ($option->is($arg)) {
                return $option;
            }
        }

        return null;
    }

    /**
     * Handle Unknown option.
     *
     * @param string      $arg   Option name
     * @param string|null $value Value
     *
     * @throws \RuntimeException When given arg is not registered and allow unkown flag is not set.
     *
     * @return mixed If true it will indicate that value has been eaten.
     */
    abstract protected function handleUnknown(string $arg, string $value = null);

    /**
     * Emit the event with value.
     *
     * @param string $event Event name (is option name technically)
     * @param mixed  $value Value (is option value technically)
     *
     * @return mixed
     */
    abstract protected function emit(string $event, $value = null);

    /**
     * Sets value of an option.
     *
     * @param Parameter   $parameter
     * @param string|null $value
     *
     * @return bool Indicating whether it has eaten adjoining arg to its right.
     */
    protected function setValue(Parameter $parameter, string $value = null): bool
    {
        $name  = $parameter->attributeName();
        $value = $this->_normalizer->normalizeValue($parameter, $value);

        return $this->set($name, $value, $parameter->variadic());
    }

    /**
     * Set a raw value.
     *
     * @param mixed $key
     * @param mixed $value
     * @param bool  $variadic
     *
     * @return bool
     */
    protected function set($key, $value, bool $variadic = false): bool
    {
        if (null === $key) {
            $this->_values[] = $value;
        } elseif ($variadic) {
            $this->_values[$key] = \array_merge($this->_values[$key], (array) $value);
        } else {
            $this->_values[$key] = $value;
        }

        return !\in_array($value, [true, false, null], true);
    }

    /**
     * Validate if all required arguments/options have proper values.
     *
     * @throw RuntimeException If value missing for required ones.
     */
    protected function validate()
    {
        /** @var Parameter[] $missingItems */
        $missingItems = \array_filter($this->_options + $this->_arguments, function ($item) {
            /* @var Parameter $item */
            return $item->required() && \in_array($this->_values[$item->attributeName()], [null, []]);
        });

        foreach ($missingItems as $item) {
            list($name, $label) = [$item->name(), 'Argument'];
            if ($item instanceof Option) {
                list($name, $label) = [$item->long(), 'Option'];
            }

            throw new RuntimeException(
                \sprintf('%s "%s" is required', $label, $name)
            );
        }
    }

    /**
     * Register a new argument/option.
     *
     * @param Parameter $param
     *
     * @return void
     */
    protected function register(Parameter $param)
    {
        $this->ifAlreadyRegistered($param);

        $name = $param->attributeName();
        if ($param instanceof Option) {
            $this->_options[$name] = $param;
        } else {
            $this->_arguments[$name] = $param;
        }

        $this->set($name, $param->default());
    }

    /**
     * What if the given name is already registered.
     *
     * @param Parameter $param
     *
     * @throws \InvalidArgumentException If given param name is already registered.
     */
    protected function ifAlreadyRegistered(Parameter $param)
    {
        if ($this->registered($param->attributeName())) {
            throw new InvalidParameterException(\sprintf(
                'The parameter "%s" is already registered',
                $param instanceof Option ? $param->long() : $param->name()
            ));
        }
    }

    /**
     * Check if either argument/option with given name is registered.
     *
     * @param string $attribute
     *
     * @return bool
     */
    public function registered($attribute): bool
    {
        return \array_key_exists($attribute, $this->_values);
    }

    /**
     * Get all options.
     *
     * @return Option[]
     */
    public function allOptions(): array
    {
        return $this->_options;
    }

    /**
     * Get all arguments.
     *
     * @return Argument[]
     */
    public function allArguments(): array
    {
        return $this->_arguments;
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

    /**
     * Get the command arguments i.e which is not an option.
     *
     * @return array
     */
    public function args(): array
    {
        return \array_diff_key($this->_values, $this->_options);
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
        $values['version'] = $this->_version ?? null;

        if (!$withDefaults) {
            unset($values['help'], $values['version'], $values['verbosity']);
        }

        return $values;
    }
}
