<?php

namespace Ahc\Cli;

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

    protected $name;

    protected $raw;

    protected $desc;

    protected $default;

    protected $required = false;

    protected $optional = false;

    protected $variadic = false;

    public function __construct(string $raw, string $desc = '', $default = null)
    {
        $this->raw      = $raw;
        $this->desc     = $desc;
        $this->default  = $default;
        $this->required = \strpos($raw, '<') !== false;
        $this->optional = \strpos($raw, '[') !== false;
        $this->variadic = \strpos($raw, '...') !== false;

        $this->parse($raw);
    }

    abstract protected function parse(string $raw);

    public function raw(): string
    {
        return $this->raw;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function desc(): string
    {
        return $this->desc;
    }

    public function attributeName(): string
    {
        return $this->toCamelCase($this->name);
    }

    public function required(): bool
    {
        return $this->required;
    }

    public function optional(): bool
    {
        return $this->optional;
    }

    public function variadic(): bool
    {
        return $this->variadic;
    }

    public function default()
    {
        return $this->default;
    }
}
