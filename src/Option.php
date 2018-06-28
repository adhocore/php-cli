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
    protected $short;

    protected $long;

    protected $desc;

    protected $default;

    protected $required = true;

    protected $optional = false;

    protected $variadic = false;

    protected $filter;

    protected $collect = [];

    public function __construct($cmd, $desc = '', $default = null, callable $filter = null)
    {
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

    protected function parse($cmd)
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

    public function long()
    {
        return $this->long;
    }

    public function short()
    {
        return $this->short;
    }

    public function name()
    {
        return \str_replace(['--', 'no-', 'with-'], '', $this->long);
    }

    public function attributeName()
    {
        $words = \str_replace('-', ' ', $this->name());

        $words = \str_replace(' ', '', \ucwords($words));

        return \lcfirst($words);
    }

    public function is($arg)
    {
        return $this->short === $arg || $this->long === $arg;
    }

    public function required()
    {
        return $this->required;
    }

    public function variadic()
    {
        return $this->variadic;
    }

    public function default()
    {
        return $this->default;
    }

    public function bool()
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
