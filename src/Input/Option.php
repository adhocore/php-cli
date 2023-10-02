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

use function \preg_match;
use function \preg_split;
use function \str_replace;
use function \strpos;

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

    public const SIGN_SHORT = '-';
    public const SIGN_LONG  = '--';

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

        [$this->short, $this->long] = $this->namingParts($raw);

        $this->name = str_replace(
            [self::SIGN_LONG, 'no-', 'with-'], 
            '',
            $this->long
        );
    }

    /**
     * parses a raw option declaration string and return its parts
     *
     * @param string $raw
     *
     * @return array [string:short, string:long]
     */
    protected function namingParts(string $raw): array
    {
        $short = '';
        $long  = '';
        foreach (preg_split('/[\s,\|]+/', $raw) as $part) { 
            if (str_starts_with($part, self::SIGN_LONG)) {
                $long = $part;
            } elseif (str_starts_with($part, self::SIGN_SHORT)) {
                $short = $part;
            }
        }
        return [
            $short,
            $long ?: self::SIGN_LONG.ltrim($short, self::SIGN_SHORT)
        ];
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
     * @param string $arg
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
        return preg_match('/\-no-|\-with-/', $this->long) > 0;
    }
}
