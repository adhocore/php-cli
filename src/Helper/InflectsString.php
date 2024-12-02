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

use Ahc\Cli\Application;

use function lcfirst;
use function mb_strwidth;
use function mb_substr;
use function str_replace;
use function strlen;
use function substr;
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

    /**
     * Return width of string.
     */
    public function strwidth(string $string): int
    {
        if (function_exists('mb_strwidth')) {
            return mb_strwidth($string);
        }

        return strlen($string);
    }

    /**
     * Get part of string.
     */
    public function substr(string $string, int $start, ?int $length = null): string
    {
        if (function_exists('mb_substr')) {
            return mb_substr($string, $start, $length);
        }

        return substr($string, $start, $length);
    }

    /**
     * Translates a message using the translator.
     */
    public static function translate(string $text, array $args = []): string
    {
        $translations = Application::$locales[Application::$locale] ?? [];
        $text         = $translations[$text] ?? $text;

        return sprintf($text, ...$args);
    }
}
