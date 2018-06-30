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
class Option
{
    use InflectsString;

    protected $short;

    protected $long;

    protected $desc;

    protected $rawCmd;

    protected $default;

    protected $required = true;

    protected $optional = false;

    protected $variadic = false;

    protected $filter;

    public function __construct(string $cmd, string $desc = null, $default = null, callable $filter = null)
    {
        $this->rawCmd   = $cmd;
        $this->desc     = $desc;
        $this->default  = $default;
        $this->filter   = $filter;
        $this->required = \strpos($cmd, '<') !== false;
        $this->optional = \strpos($cmd, '[') !== false;

        if ($this->variadic = \strpos($cmd, '...') !== false) {
            $this->default = (array) $this->default;
        }

        $this->parse($cmd);
    }

    protected function parse(string $cmd)
    {
        if (\strpos($cmd, '-with-') !== false) {
            $this->default = false;
        } elseif (\strpos($cmd, '-no-') !== false) {
            $this->default = true;
        }

        $parts = \preg_split('/[\s,\|]+/', $cmd);

        $this->short = $this->long = $parts[0];
        if (isset($parts[1])) {
            $this->long = $parts[1];
        }
    }

    public function long(): string
    {
        return $this->long;
    }

    public function short(): string
    {
        return $this->short;
    }

    public function name(): string
    {
        return \str_replace(['--', 'no-', 'with-'], '', $this->long);
    }

    public function attributeName(): string
    {
        return $this->toCamelCase($this->name());
    }

    public function is($arg): bool
    {
        return $this->short === $arg || $this->long === $arg;
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

    public function bool(): bool
    {
        return \preg_match('/\-no|\-with/', $this->long) > 0;
    }

    public function filter($raw)
    {
        if ($this->filter) {
            $callback = $this->filter;

            return $callback($raw);
        }

        return $raw;
    }
}
