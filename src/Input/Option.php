<?php

namespace Ahc\Cli\Input;

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
    /** @var string Short name */
    protected $short = '';

    /** @var string Long name */
    protected $long = '';

    /**
     * {@inheritdoc}
     */
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

    /**
     * Get long name.
     *
     * @return string
     */
    public function long(): string
    {
        return $this->long;
    }

    /**
     * Get short name.
     *
     * @return string
     */
    public function short(): string
    {
        return $this->short;
    }

    /**
     * Test if this option matches given arg.
     *
     * @return bool
     */
    public function is(string $arg): bool
    {
        return $this->short === $arg || $this->long === $arg;
    }

    /**
     * Check if the option is boolean type.
     *
     * @return bool
     */
    public function bool(): bool
    {
        return \preg_match('/\-no|\-with/', $this->long) > 0;
    }
}
