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

use function current;
use function func_get_args;
use function is_array;
use function is_int;
use function next;

/**
 * helper specializing in table manipulation
 *
 * @author  Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @license MIT
 *
 * @link    https://github.com/adhocore/cli
 */
class Arr
{
    /**
     * This function can be thought of as a hybrid between PHP's `array_merge` and `array_merge_recursive`.
     *
     * The difference between this method and the built-in ones, is that if an array key contains another array, then
     * Hash::merge() will behave in a recursive fashion (unlike `array_merge`). But it will not act recursively for
     * keys that contain scalar values (unlike `array_merge_recursive`).
     *
     * Note: This function will work with an unlimited amount of arguments and typecasts non-array parameters into arrays.
     *
     * @param array $data  Array to be merged
     * @param mixed $merge Array to merge with. The argument and all trailing arguments will be array cast when merged
     *
     * @return array Merged array
     *
     * @credit CakePHP - http://book.cakephp.org/2.0/en/core-utility-libraries/hash.html#Hash::merge
     */
    public static function merge(array $data, $merge)
    {
        $args   = func_get_args();
        $return = current($args);

        while (($arg = next($args)) !== false) {
            foreach ((array) $arg as $key => $val) {
                if (! empty($return[$key]) && is_array($return[$key]) && is_array($val)) {
                    $return[$key] = self::merge($return[$key], $val);
                } elseif (is_int($key) && isset($return[$key])) {
                    $return[] = $val;
                } else {
                    $return[$key] = $val;
                }
            }
        }

        return $return;
    }
}
