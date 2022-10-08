<?php

/*
 * This file is part of the PHP-CLI package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

namespace Ahc\Cli\Helper;

use function lcfirst;
use function str_replace;
use function trim;
use function ucwords;

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
     */
    public function toCamelCase(string $string): string
    {
        $words = str_replace(['-', '_'], ' ', $string);

        $words = str_replace(' ', '', ucwords($words));

        return lcfirst($words);
    }

    /**
     * Convert a string to capitalized words.
     */
    public function toWords(string $string): string
    {
        $words = trim(str_replace(['-', '_'], ' ', $string));

        return ucwords($words);
    }
}
