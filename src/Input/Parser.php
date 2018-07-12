<?php

namespace Ahc\Cli\Input;

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
    /** @var Option|null The last seen option */
    protected $_lastOption;

    /** @var bool If the last seen option was variadic */
    private $_wasVariadic = false;

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
        \array_shift($argv);

        $argv  = $this->normalize($argv);
        $count = \count($argv);

        $literal = false;
        for ($i = 0; $i < $count; $i++) {
            list($arg, $nextArg) = [$argv[$i], $argv[$i + 1] ?? null];

            $literal = $literal ?: $arg === '--';
            if ($arg[0] !== '-' || $literal) {
                $this->parseArgs($arg);
            } else {
                $i += (int) $this->parseOptions($arg, $nextArg);
            }
        }

        $this->validate();

        return $this;
    }

    /**
     * Normalize argv args. Like splitting `-abc` and `--xyz=...`.
     *
     * @param array $args
     *
     * @return array
     */
    protected function normalize(array $args): array
    {
        $normalized = [];

        foreach ($args as $arg) {
            if (\preg_match('/^\-\w{2,}/', $arg)) {
                $normalized = \array_merge($normalized, $this->splitShort($arg));
            } elseif (\preg_match('/^\-\-\w{2,}\=/', $arg)) {
                $normalized = \array_merge($normalized, explode('=', $arg));
            } else {
                $normalized[] = $arg;
            }
        }

        return $normalized;
    }

    /**
     * Split joined short params like `-ab`.
     *
     * @param string $arg
     *
     * @return array
     */
    protected function splitShort(string $arg): array
    {
        $args = \str_split(\substr($arg, 1));

        return \array_map(function ($a) {
            return "-$a";
        }, $args);
    }

    /**
     * Parse single arg.
     *
     * @param string $arg
     *
     * @return void
     */
    protected function parseArgs(string $arg)
    {
        if ($arg === '--') {
            return;
        }

        if ($this->_wasVariadic) {
            return $this->set($this->_lastOption->attributeName(), $arg, true);
        }

        if (!$argument = \reset($this->_arguments)) {
            return $this->set(null, $arg);
        }

        $name   = $argument->attributeName();
        $variad = $argument->variadic();

        $this->set($name, $argument->filter($arg), $variad);

        // Otherwise we will always collect same arguments again!
        if (!$variad) {
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

        $this->_lastOption  = $option  = $this->optionFor($arg);
        $this->_wasVariadic = $option ? $option->variadic() : false;

        if (!$option) {
            $this->handleUnknown($arg, $value);

            return !\is_null($value);
        }

        if (false === $this->emit($option->attributeName(), $value)) {
            return false;
        }

        return $this->setValue($option, $value);
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
     * @return void
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
        $name = $parameter->attributeName();

        if (null !== $value = $this->prepareValue($parameter, $value)) {
            $this->set($name, $value);
        }

        return !\in_array($value, [true, false, null], true);
    }

    /**
     * Prepares value as per context and runs thorugh filter if possible.
     *
     * @param Parameter   $parameter
     * @param string|null $value
     *
     * @return mixed
     */
    protected function prepareValue(Parameter $parameter, string $value = null)
    {
        if (\is_bool($default = $parameter->default())) {
            return !$default;
        }

        if ($parameter->variadic()) {
            return (array) $value;
        }

        if (null === $value && !$parameter->required()) {
            return true;
        }

        return null === $value ? null : $parameter->filter($value);
    }

    /**
     * Set a value.
     *
     * @param mixed $key
     * @param mixed $value
     * @param bool  $variadic
     */
    protected function set($key, $value, bool $variadic = false)
    {
        if (null === $key) {
            $this->_values[] = $value;
        } elseif ($variadic) {
            $this->_values[$key][] = $value;
        } else {
            $this->_values[$key] = $value;
        }
    }

    /**
     * Validate if all required arguments/options have proper values.
     *
     * @throw \RuntimeException If value missing for required ones.
     */
    protected function validate()
    {
        foreach ($this->_options + $this->_arguments as $item) {
            if (!$item->required()) {
                continue;
            }

            list($name, $label) = [$item->name(), 'Argument'];
            if ($item instanceof Option) {
                list($name, $label) = [$item->long(), 'Option'];
            }

            if (\in_array($this->_values[$item->attributeName()], [null, []])) {
                throw new \RuntimeException(
                    \sprintf('%s "%s" is required', $label, $name)
                );
            }
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
     * @throws \InvalidArgumentException If given param name is already registered.
     */
    protected function ifAlreadyRegistered(Parameter $param)
    {
        if ($this->registered($param->attributeName())) {
            throw new \InvalidArgumentException(\sprintf(
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
        $values['version'] = $this->_version;

        if (!$withDefaults) {
            unset($values['help'], $values['version']);
        }

        return $values;
    }
}
