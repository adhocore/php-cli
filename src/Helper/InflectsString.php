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
    public function toCamelCase(string $string): string
    {
        $words = \str_replace(['-', '_'], ' ', $string);

        $words = \str_replace(' ', '', \ucwords($words));

        return \lcfirst($words);
    }
}
