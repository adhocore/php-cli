<?php

/*
 * This file is part of the PHP-CLI package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     https:github.comadhocore
 *
 * Licensed under MIT license.
 *
 */

namespace Ahc\Cli\Input;

use Ahc\Cli\Helper\InflectsString;

/**
 * Cli Parameter.
 *
 * @author  Jitendra Adhikari <jiten.adhikary@gmail.com>
 * @license MIT
 *
 * @link    https://github.com/adhocore/cli
 */
abstract class Parameter
{
    use InflectsString;

    /** @var string */
    protected $name;

    /** @var string */
    protected $raw;

    /** @var string */
    protected $desc;

    /** @var mixed */
    protected $default;

    /** @var callable The sanitizer/filter callback */
    protected $filter;

    /** @var bool */
    protected $required = false;

    /** @var bool */
    protected $optional = false;

    /** @var bool */
    protected $variadic = false;

    public function __construct(string $raw, string $desc = '', $default = null, callable $filter = null)
    {
        $this->raw      = $raw;
        $this->desc     = $desc;
        $this->default  = $default;
        $this->filter   = $filter;
        $this->required = \strpos($raw, '<') !== false;
        $this->optional = \strpos($raw, '[') !== false;
        $this->variadic = \strpos($raw, '...') !== false;

        $this->parse($raw);
    }

    /**
     * Parse raw string representation of parameter.
     *
     * @param string $raw
     *
     * @return void
     */
    abstract protected function parse(string $raw);

    /**
     * Get raw definition.
     *
     * @return string
     */
    public function raw(): string
    {
        return $this->raw;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function desc(): string
    {
        return $this->desc;
    }

    /**
     * Get normalized name.
     *
     * @return string
     */
    public function attributeName(): string
    {
        return $this->toCamelCase($this->name);
    }

    /**
     * Check this param is required.
     *
     * @return bool
     */
    public function required(): bool
    {
        return $this->required;
    }

    /**
     * Check this param is optional.
     *
     * @return bool
     */
    public function optional(): bool
    {
        return $this->optional;
    }

    /**
     * Check this param is variadic.
     *
     * @return bool
     */
    public function variadic(): bool
    {
        return $this->variadic;
    }

    /**
     * Gets default value.
     *
     * @return mixed
     */
    public function default()
    {
        if ($this->variadic()) {
            return (array) $this->default;
        }

        return $this->default;
    }

    /**
     * Run the filter/sanitizer/validato callback for this prop.
     *
     * @param mixed $raw
     *
     * @return mixed
     */
    public function filter($raw)
    {
        if ($this->filter) {
            $callback = $this->filter;

            return $callback($raw);
        }

        return $raw;
    }
}
