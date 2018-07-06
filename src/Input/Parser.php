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
    protected $_wasVariadic = false;

    /** @var Option[] Registered options */
    protected $_options = [];

    /** @var Option[] Registered arguments */
    protected $_arguments = [];

    /** @var array Parsed values indexed by option name */
    protected $_values = [];

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

    protected function splitShort(string $arg): array
    {
        $args = \str_split(\substr($arg, 1));

        return \array_map(function ($a) {
            return "-$a";
        }, $args);
    }

    protected function parseArgs(string $arg)
    {
        if ($arg === '--') {
            return;
        }

        if ($this->_wasVariadic) {
            $this->_values[$this->_lastOption->attributeName()][] = $arg;

            return;
        }

        if (!$argument = \reset($this->_arguments)) {
            $this->_values[] = $arg;

            return;
        }

        $name = $argument->attributeName();
        if ($argument->variadic()) {
            $this->_values[$name][] = $arg;

            return;
        }

        $this->_values[$name] = $arg;

        // Otherwise we will always collect same arguments again!
        \array_shift($this->_arguments);
    }

    protected function parseOptions(string $arg, string $nextArg = null)
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
     * @param Option      $option
     * @param string|null $value
     *
     * @return bool Indicating whether it has eaten adjoining arg to its right.
     */
    protected function setValue(Option $option, string $value = null): bool
    {
        $name  = $option->attributeName();
        $value = $this->prepareValue($option, $value);

        $this->_values[$name] = $value ?? $this->_values[$name];

        return !\in_array($value, [true, false, null], true);
    }

    /**
     * Prepares value as per context and runs thorugh filter if possible.
     *
     * @param Option      $option
     * @param string|null $value
     *
     * @return mixed
     */
    protected function prepareValue(Option $option, string $value = null)
    {
        if (\is_bool($default = $option->default())) {
            return !$default;
        }

        if ($option->variadic()) {
            return (array) $value;
        }

        if (null === $value && !$option->required()) {
            return true;
        }

        return null === $value ? null : $option->filter($value);
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
}
