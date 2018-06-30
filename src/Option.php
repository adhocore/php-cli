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
class Option extends Parameter
{
    protected $short;

    protected $long;

    protected $filter;

    public function __construct(string $raw, string $desc = null, $default = null, callable $filter = null)
    {
        $this->filter = $filter;

        parent::__construct($raw, $desc, $default);
    }

    protected function parse(string $raw)
    {
        if (\strpos($raw, '-with-') !== false) {
            $this->default = false;
        } elseif (\strpos($raw, '-no-') !== false) {
            $this->default = true;
        }

        $parts = \preg_split('/[\s,\|]+/', $raw);

        $this->short = $this->long = $parts[0];
        if (isset($parts[1])) {
            $this->long = $parts[1];
        }

        $this->name = \str_replace(['--', 'no-', 'with-'], '', $this->long);
    }

    public function long(): string
    {
        return $this->long;
    }

    public function short(): string
    {
        return $this->short;
    }

    public function is($arg): bool
    {
        return $this->short === $arg || $this->long === $arg;
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
