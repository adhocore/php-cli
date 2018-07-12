<?php

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
}
