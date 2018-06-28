<?php

namespace Ahc\Cli;

/**
 * Argv parser for the cli.
 *
 * @author  Jitendra Adhikari <jiten.adhikary@gmail.com>
 * @license MIT
 *
 * @link    https://github.com/adhocore/cli
 */
class ArgvParser
{
    protected $_version;

    protected $_lastOption;

    protected $_name;

    protected $_desc;

    protected $_options = [];

    protected $_values = [];

    protected $_args = [];

    protected $_events = [];

    protected $_allowUnknown = false;

    protected $_wasVariadic = false;

    public function __construct($name, $desc = null, $allowUnknown = false)
    {
        $this->_name = $name;
        $this->_desc = $desc;
        $this->_allowUnknown = $allowUnknown;

        $this->addDefaultOptions();
    }

    protected function addDefaultOptions()
    {
        $this->option('-h, --help', 'Show help')->on([$this, 'showHelp']);
        $this->option('-V, --version', 'Show version')->on([$this, 'showVersion']);
    }

    public function version($version)
    {
        $this->_version = $version;

        return $this;
    }

    public function option($cmd, $desc = '', callable $filter = null, $default = null)
    {
        $option = new Option($cmd, $desc, $default, $filter);

        if (isset($this->_options[$option->long()])) {
            throw new \InvalidArgumentException(
                \sprintf('The option %s is already registered', $option->long())
            );
        }

        $this->_values[$option->attributeName()] = $option->default();
        $this->_options[$option->long()] = $option;

        return $this;
    }

    public function on(callable $fn)
    {
        \end($this->_options);

        $this->_events[\key($this->_options)] = $fn;

        return $this;
    }

    public function parse(array $argv)
    {
        \array_shift($argv);

        $argv = $this->normalize($argv);
        $count = \count($argv);

        for ($i = 0; $i < $count; $i++) {
            list($arg, $nextArg) = [$argv[$i], isset($argv[$i + 1]) ? $argv[$i + 1] : null];

            if ($arg[0] !== '-' || !empty($literal) || ($literal = $arg === '--')) {
                $this->parseArgs($arg);
            } else {
                $i += (int) $this->parseOptions($arg, $nextArg, $i);
            }
        }

        return $this->validate();
    }

    protected function parseArgs($arg)
    {
        if ($this->_wasVariadic) {
            $this->_values[$this->_lastOption->attributeName()][] = $arg;
        } else {
            $this->_args[] = $arg;
        }
    }

    protected function parseOptions($arg, $nextArg, &$i)
    {
        $value = \substr($nextArg, 0, 1) === '-' ? null : $nextArg;
        $isValue = $value !== null;

        $this->_lastOption = $option = $this->optionFor($arg);
        $this->_wasVariadic = $option ? $option->variadic() : false;

        if (!$option) {
            $this->handleUnknown($arg, $value);

            return $isValue;
        }

        $this->emit($option->long());
        $this->setValue($option, $value);

        return $isValue;
    }

    protected function handleUnknown($arg, $value)
    {
        if ($this->_allowUnknown) {
            $this->_values[$arg] = $value;

            return;
        }

        // Has some value, error!
        if ($this->_values) {
            throw new \RuntimeException(
                \sprintf('Option %s not registered', $arg)
            );
        }

        // Has no value, show help!
        return $this->showHelp();
    }

    protected function setValue(Option $option, $value)
    {
        $name = $option->attributeName();

        if (null === $value && null !== $this->_values[$name]) {
            return;
        }

        $this->_values[$name] = $this->prepareValue($option, $value);
    }

    protected function prepareValue(Option $option, $value)
    {
        if ($option->bool()) {
            return !$option->default();
        }

        if ($option->variadic()) {
            return (array) $value;
        }

        return null === $value ? null : $option->filter($value);
    }

    protected function validate()
    {
        foreach ($this->_options as $option) {
            if (!$option->required()) {
                continue;
            }

            $value = $this->_values[$option->attributeName()];

            if (null === $value || [] === $value) {
                throw new \RuntimeException(
                    \sprintf('Option %s|%s is required', $option->short(), $option->long())
                );
            }
        }

        return $this;
    }

    public function values($withDefaults = true)
    {
        $values = $this->_values;

        if (!$withDefaults) {
            unset($values['help'], $values['version']);
        }

        return $values;
    }

    public function args()
    {
        return $this->_args;
    }

    public function __get($key)
    {
        return isset($this->_values[$key]) ? $this->_values[$key] : null;
    }

    protected function showHelp()
    {
        echo "{$this->_name}, version {$this->_version}\n";

        exit('help');
    }

    protected function showVersion()
    {
        echo "{$this->_name}, version {$this->_version}\n";

        exit(0);
    }

    protected function normalize(array $args)
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

    protected function splitShort($arg)
    {
        $args = \str_split(\substr($arg, 1));

        return \array_map(function ($a) {
            return "-$a";
        }, $args);
    }

    protected function optionFor($arg)
    {
        if (isset($this->_options[$arg])) {
            return $this->_options[$arg];
        }

        foreach ($this->_options as $option) {
            if ($option->is($arg)) {
                return $option;
            }
        }
    }

    protected function emit($event)
    {
        if (empty($this->_events[$event])) {
            return;
        }

        $callback = $this->_events[$event];

        $callback();
    }
}
