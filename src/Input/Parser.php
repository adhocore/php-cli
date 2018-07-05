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
        $value   = \substr($nextArg, 0, 1) === '-' ? null : $nextArg;
        $isValue = $value !== null;

        $this->_lastOption  = $option  = $this->optionFor($arg);
        $this->_wasVariadic = $option ? $option->variadic() : false;

        if (!$option) {
            $this->handleUnknown($arg, $value);

            return $isValue;
        }

        if (false === $this->emit($option->attributeName(), $value)) {
            return false;
        }

        $this->setValue($option, $value);

        return $isValue;
    }

    protected function optionFor(string $arg)
    {
        foreach ($this->_options as $option) {
            if ($option->is($arg)) {
                return $option;
            }
        }
    }

    abstract protected function handleUnknown(string $arg, string $value = null);

    abstract protected function emit(string $event, $value = null);

    protected function setValue(Option $option, string $value = null)
    {
        $name = $option->attributeName();

        if (null === $value = $this->prepareValue($option, $value)) {
            return;
        }

        $this->_values[$name] = $value;
    }

    protected function prepareValue(Option $option, string $value = null)
    {
        if ($option->bool()) {
            return !$option->default();
        }

        if ($option->variadic()) {
            return (array) $value;
        }

        if (null === $value && $option->optional()) {
            $value = true;
        }

        return null === $value ? null : $option->filter($value);
    }

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
