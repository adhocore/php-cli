<?php

namespace Ahc\Cli;

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
    public function toCamelCase(string $string)
    {
        $words = \str_replace('-', ' ', $string);

        $words = \str_replace(' ', '', \ucwords($words));

        return \lcfirst($words);
    }
}
