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

namespace Ahc\Cli\Helper;

/**
 * Performs inflection on strings.
 *
 * @author  Jitendra Adhikari <jiten.adhikary@gmail.com>
 * @license MIT
 *
 * @link    https://github.com/adhocore/cli
 */
trait InflectsString
{
    /**
     * Convert a string to camel case.
     *
     * @param string $string
     *
     * @return string
     */
    public function toCamelCase(string $string): string
    {
        $words = \str_replace(['-', '_'], ' ', $string);

        $words = \str_replace(' ', '', \ucwords($words));

        return \lcfirst($words);
    }

    /**
     * Convert a string to capitalized words.
     *
     * @param string $string
     *
     * @return string
     */
    public function toWords(string $string): string
    {
        $words = \trim(\str_replace(['-', '_'], ' ', $string));

        return \ucwords($words);
    }
}
