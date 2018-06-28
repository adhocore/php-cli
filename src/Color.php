<?php

namespace Ahc\Cli;

/**
 * Cli Colorizer.
 *
 * @author  Jitendra Adhikari <jiten.adhikary@gmail.com>
 * @license MIT
 *
 * @link    https://github.com/adhocore/cli
 */
class Color
{
    const FG_RED    = 31;
    const FG_GREEN  = 32;
    const FG_YELLOW = 33;
    const FG_BLUE   = 36;
    // @todo

    protected static $format = "\033[:bold:;:fg:;:bg:m:text:\033[0m";

    protected static $styles = [];

    protected static $muted  = false;

    public static function error($text, array $style = [], $eol = false)
    {
        static::line($text, ['fg' => static::FG_RED] + $style, $eol);
    }

    public static function info($text, array $style = [], $eol = false)
    {
        static::line($text, ['fg' => static::FG_BLUE] + $style, $eol);
    }

    public static function warn($text, array $style = [], $eol = false)
    {
        static::line($text, ['fg' => static::FG_YELLOW] + $style, $eol);
    }

    public static function comment($text, array $style = [], $eol = false)
    {
        static::line($text, ['fg' => static::FG_RED] + $style, $eol);
    }

    public static function line($text, array $style = [], $eol = false)
    {
        if (static::$muted) {
            return;
        }

        $style += ['bg' => null, 'fg' => 37, 'bold' => 0];

        $format = $style['bg'] === null
            ? \str_replace(';:bg:', '', static::$format)
            : static::$format;

        echo \strtr($format, [
            ':bold:' => (int) $style['bold'],
            ':fg:'   => (int) $style['fg'],
            ':bg:'   => (int) $style['bg'],
            ':text:' => (string) $text,
        ]);

        if ($eol) {
            static::eol();
        }
    }

    public static function eol()
    {
        if (!static::$muted) {
            echo PHP_EOL;
        }
    }

    public function style($name, array $style)
    {
        $allow = ['fg' => true, 'bg' => true, 'bold' => true];
        $style = \array_intersect_key($style, $allow);

        if (empty($style)) {
            throw new \InvalidArgumentException('Trying to set empty or invalid style');
        }

        if (isset(static::$styles[$name]) || \method_exists(static::class, $name)) {
            throw new \InvalidArgumentException('Trying to define existing style');
        }

        static::$styles[$name] = $style;
    }

    public static function __callStatic($name, $arguments)
    {
        if (empty($arguments[0])) {
            throw new \InvalidArgumentException('Text required');
        }

        list($text, $style, $eol) = $arguments + ['', [], false];

        if (\substr($name, 0, 4) === 'bold') {
            $name = \lcfirst(\substr($name, 4));
            static::{$name}($text, ['bold' => true] + $style, $eol);

            return;
        }

        if (!isset(static::$styles[$name])) {
            throw new \InvalidArgumentException(\sprintf('Style %s not defined', $name));
        }

        $style = static::$styles[$name];

        static::line($text, $style, $eol);
    }

    public static function mute($muted = true)
    {
        static::$muted = $muted;
    }
}
