<?php

namespace Ahc\Cli;

/**
 * Cli Option.
 *
 * @author  Jitendra Adhikari <jiten.adhikary@gmail.com>
 * @license MIT
 *
 * @link    https://github.com/adhocore/cli
 */
class Argument
{
    use InflectsString;

    protected $name;

    protected $rawArg;

    protected $default;

    protected $required = false;

    protected $variadic = false;

    public function __construct(string $arg)
    {
        $this->rawArg = $arg;

        $this->parse($arg);
    }

    protected function parse(string $arg)
    {
        $this->required = $arg[0] === '<';
        $this->variadic = \strpos($arg, '...') !== false;
        $this->name     = $name = \str_replace(['<', '>', '[', ']', '.'], '', $arg);

        // Format is "name:default+value1,default+value2" ('+'' => ' ')!
        if (\strpos($name, ':') !== false) {
            $name = \str_replace('+', ' ', $name);
            list($this->name, $this->default) = \explode(':', $name, 2);
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    public function attributeName(): string
    {
        return $this->toCamelCase($this->name);
    }

    public function required(): bool
    {
        return $this->required;
    }

    public function variadic(): bool
    {
        return $this->variadic;
    }

    public function default()
    {
        if (!$this->variadic) {
            return $this->default;
        }

        return null === $this->default ? [] : \explode(',', $this->default);
    }
}
