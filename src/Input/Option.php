<?php

/*
 * This file is part of the PHP-CLI package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

namespace Ahc\Cli\Input;

use function preg_match;
use function preg_split;
use function str_replace;
use function strpos;

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
    protected string $short = '';

    protected string $long = '';

    /**
     * {@inheritdoc}
     */
    protected function parse(string $raw): void
    {
        if (strpos($raw, '-with-') !== false) {
            $this->default = false;
        } elseif (strpos($raw, '-no-') !== false) {
            $this->default = true;
        }

        $parts = preg_split('/[\s,\|]+/', $raw);

        $this->short = $this->long = $parts[0];
        if (isset($parts[1])) {
            $this->long = $parts[1];
        }

        $this->name = str_replace(['--', 'no-', 'with-'], '', $this->long);
    }

    /**
     * Get long name.
     */
    public function long(): string
    {
        return $this->long;
    }

    /**
     * Get short name.
     */
    public function short(): string
    {
        return $this->short;
    }

    /**
     * Test if this option matches given arg.
     */
    public function is(string $arg): bool
    {
        return $this->short === $arg || $this->long === $arg;
    }

    /**
     * Check if the option is boolean type.
     */
    public function bool(): bool
    {
        return preg_match('/\-no-|\-with-/', $this->long) > 0;
    }
}
