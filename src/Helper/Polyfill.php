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

/**
 * Polyfill class is for using newer php syntax 
 * and still maintaining backword compatibility
 * 
 * @author  Shlomo Hassid <shlomohassid@gmail.com>
 * @license MIT
 *
 * @link    https://github.com/adhocore/cli
 */
class Polyfill
{
    public static function str_contains($haystack, $needle)
    {
        if (function_exists('str_contains')) {
            return str_contains($haystack, $needle);
        }
        return $needle !== '' && strpos($haystack, $needle) !== false;
    }

    public static function str_starts_with($haystack, $needle)
    {
        if (function_exists('str_starts_with')) {
            return str_starts_with($haystack, $needle);
        }
        return (string) $needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
    }

    public static function str_ends_with($haystack, $needle)
    {
        if (function_exists('str_ends_with')) {
            return str_ends_with($haystack, $needle);
        }
        return $needle !== '' && substr($haystack, -strlen($needle)) === (string) $needle;
    }
}
