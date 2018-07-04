<?php

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

    /** @var bool */
    protected $required = false;

    /** @var bool */
    protected $optional = false;

    /** @var bool */
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

    /**
     * Parse raw string representation of parameter.
     *
     * @param string $raw
     *
     * @return void
     */
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
