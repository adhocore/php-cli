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
    /** @var string */
    protected $_version;

    /** @var Option|null The last seen option */
    protected $_lastOption;

    /** @var string */
    protected $_name;

    /** @var string */
    protected $_desc;

    /** @var Option[] Registered options */
    protected $_options = [];

    /** @var array Parsed values indexed by option name */
    protected $_values = [];

    /** @var array Arguments that dont belong to any specific option */
    protected $_args = [];

    /** @var callable[] Events for options */
    protected $_events = [];

    /** @var bool Whether to allow unknown (not registered) options */
    protected $_allowUnknown = false;

    /** @var bool If the last seen option was variadic */
    protected $_wasVariadic = false;

    /**
     * Constructor.
     *
     * @param string $name
     * @param string $desc
     * @param bool   $allowUnknown
     */
    public function __construct(string $name, string $desc = null, bool $allowUnknown = false)
    {
        $this->_name         = $name;
        $this->_desc         = $desc;
        $this->_allowUnknown = $allowUnknown;

        $this->addDefaultOptions();
    }

    protected function addDefaultOptions()
    {
        $this->option('-h, --help', 'Show help')->on([$this, 'showHelp']);
        $this->option('-V, --version', 'Show version')->on([$this, 'showVersion']);
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

    /**
     * Registers new option.
     *
     * @param string        $cmd     [description]
     * @param string        $desc    [description]
     * @param callable|null $filter  [description]
     * @param mixed         $default [description]
     *
     * @return self
     */
    public function option(string $cmd, string $desc = '', callable $filter = null, $default = null): self
    {
        $option = new Option($cmd, $desc, $default, $filter);

        if (isset($this->_options[$option->long()])) {
            throw new \InvalidArgumentException(
                \sprintf('The option %s is already registered', $option->long())
            );
        }

        $this->_values[$option->attributeName()] = $option->default();
        $this->_options[$option->long()]         = $option;

        return $this;
    }

    /**
     * Sets event handler for last option.
     *
     * @param callable $fn [description]
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
     * Parse the argv input.
     *
     * @param array $argv The first item is ignored.
     *
     * @return self       [description]
     *
     * @throws \RuntimeException When argument is missing or invalid.
     */
    public function parse(array $argv): self
    {
        \array_shift($argv);

        $argv  = $this->normalize($argv);
        $count = \count($argv);

        for ($i = 0; $i < $count; $i++) {
            list($arg, $nextArg) = [$argv[$i], isset($argv[$i + 1]) ? $argv[$i + 1] : null];

            if ($arg[0] !== '-' || !empty($literal) || ($literal = $arg === '--')) {
                $this->parseArgs($arg);
            } else {
                $i += (int) $this->parseOptions($arg, $nextArg);
            }
        }

        return $this->validate();
    }

    protected function parseArgs(string $arg)
    {
        if ($this->_wasVariadic) {
            $this->_values[$this->_lastOption->attributeName()][] = $arg;
        } else {
            $this->_args[] = $arg;
        }
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

        $this->emit($option->long());
        $this->setValue($option, $value);

        return $isValue;
    }

    protected function handleUnknown(string $arg, string $value = null)
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

    protected function setValue(Option $option, string $value = null)
    {
        $name = $option->attributeName();

        if (null === $value && null !== $this->_values[$name]) {
            return;
        }

        $this->_values[$name] = $this->prepareValue($option, $value);
    }

    protected function prepareValue(Option $option, string $value = null)
    {
        if ($option->bool()) {
            return !$option->default();
        }

        if ($option->variadic()) {
            return (array) $value;
        }

        return null === $value ? null : $option->filter($value);
    }

    protected function validate(): self
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

    /**
     * Get values indexed by camelized attribute name.
     *
     * @param bool $withDefaults
     *
     * @return array
     */
    public function values($withDefaults = true): array
    {
        $values = $this->_values;

        if (!$withDefaults) {
            unset($values['help'], $values['version']);
        }

        return $values;
    }

    /**
     * Get values.
     *
     * @param bool $withDefaults
     *
     * @return array
     */
    public function args()
    {
        return $this->_args;
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

    protected function optionFor(string $arg)
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

    protected function emit(string $event)
    {
        if (empty($this->_events[$event])) {
            return;
        }

        $callback = $this->_events[$event];

        $callback();
    }
}
