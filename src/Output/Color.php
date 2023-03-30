<?php

/*
 * This file is part of the PHP-CLI package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

namespace Ahc\Cli\Output;

use Ahc\Cli\Exception\InvalidArgumentException;

use function array_intersect_key;
use function constant;
use function defined;
use function lcfirst;
use function method_exists;
use function preg_match_all;
use function sprintf;
use function str_ireplace;
use function str_replace;
use function stripos;
use function strtolower;
use function strtoupper;
use function strtr;

use const PHP_EOL;

/**
 * Cli Colorizer.
 *
 * @author  Jitendra Adhikari <jiten.adhikary@gmail.com>
 * @license MIT
 *
 * @link   static  https://github.com/adhocore/cli
 */
class Color
{
    const BLACK    = 30;
    const RED      = 31;
    const GREEN    = 32;
    const YELLOW   = 33;
    const BLUE     = 34;
    const PURPLE   = 35;
    const CYAN     = 36;
    const WHITE    = 37;
    const GRAY     = 47;
    const DARKGRAY = 100;

    protected string $format = "\033[:mod:;:fg:;:bg:m:txt:\033[0m";

    /** @var array Custom styles */
    protected static array $styles = [];

    /**
     * Returns a line formatted as comment.
     */
    public function comment(string $text, array $style = []): string
    {
        return $this->line($text, ['mod' => 2] + $style);
    }

    /**
     * Returns a line formatted as comment.
     */
    public function error(string $text, array $style = []): string
    {
        return $this->line($text, ['fg' => static::RED] + $style);
    }

    /**
     * Returns a line formatted as ok msg.
     */
    public function ok(string $text, array $style = []): string
    {
        return $this->line($text, ['fg' => static::GREEN] + $style);
    }

    /**
     * Returns a line formatted as warning.
     */
    public function warn(string $text, array $style = []): string
    {
        return $this->line($text, ['fg' => static::YELLOW] + $style);
    }

    /**
     * Returns a line formatted as info.
     */
    public function info(string $text, array $style = []): string
    {
        return $this->line($text, ['fg' => static::BLUE] + $style);
    }

    /**
     * Returns a formatted/colored line.
     */
    public function line(string $text, array $style = []): string
    {
        $style += ['bg' => null, 'fg' => static::WHITE, 'bold' => 0, 'mod' => null];

        $format = $style['bg'] === null
            ? str_replace(';:bg:', '', $this->format)
            : $this->format;

        $line = strtr($format, [
            ':mod:' => (int) ($style['mod'] ?? $style['bold']),
            ':fg:'  => (int) $style['fg'],
            ':bg:'  => (int) $style['bg'] + 10,
            ':txt:' => $text,
        ]);

        return $line;
    }

    /**
     * Prepare a multi colored string with html like tags.
     *
     * Example: "<errorBold>Text</end><eol/><bgGreenBold>Text</end><eol>"
     */
    public function colors(string $text): string
    {
        $text = str_replace(['<eol>', '<eol/>', '</eol>', "\r\n", "\n"], '__PHP_EOL__', $text);

        if (!preg_match_all('/<(\w+)>(.*?)<\/end>/', $text, $matches)) {
            return str_replace('__PHP_EOL__', PHP_EOL, $text);
        }

        $end  = "\033[0m";
        $text = str_replace(['<end>', '</end>'], $end, $text);

        foreach ($matches[1] as $i => $method) {
            $part = str_replace($end, '', $this->{$method}(''));
            $text = str_replace("<$method>", $part, $text);
        }

        return str_replace('__PHP_EOL__', PHP_EOL, $text);
    }

    /**
     * Register a custom style.
     *
     * @param string $name  Example: 'alert'
     * @param array  $style Example: ['fg' => Color::RED, 'bg' => Color::YELLOW, 'bold' => 1]
     *
     * @return void
     */
    public static function style(string $name, array $style): void
    {
        $allow = ['fg' => true, 'bg' => true, 'bold' => true];
        $style = array_intersect_key($style, $allow);

        if (empty($style)) {
            throw new InvalidArgumentException('Trying to set empty or invalid style');
        }

        if (isset(static::$styles[$name]) || method_exists(static::class, $name)) {
            throw new InvalidArgumentException('Trying to define existing style');
        }

        static::$styles[$name] = $style;
    }

    /**
     * Magically build styles.
     *
     * @param string $name      Example: 'boldError', 'bgGreenBold' etc
     * @param array  $arguments
     *
     * @return string
     */
    public function __call(string $name, array $arguments): string
    {
        if (!isset($arguments[0])) {
            throw new InvalidArgumentException('Text required');
        }

        [$name, $text, $style] = $this->parseCall($name, $arguments);

        if (isset(static::$styles[$name])) {
            return $this->line($text, $style + static::$styles[$name]);
        }

        if (defined($color = static::class . '::' . strtoupper($name))) {
            $name   = 'line';
            $style += ['fg' => constant($color)];
        }

        if (!method_exists($this, $name)) {
            throw new InvalidArgumentException(sprintf('Style "%s" not defined', $name));
        }

        return $this->{$name}($text, $style);
    }

    /**
     * Parse the name argument pairs to determine callable method and style params.
     */
    protected function parseCall(string $name, array $arguments): array
    {
        [$text, $style] = $arguments + ['', []];

        $mods = ['bold' => 1, 'dim' => 2, 'italic' => 3, 'underline' => 4, 'flash' => 5];

        foreach ($mods as $mod => $value) {
            if (stripos($name, $mod) !== false) {
                $name   = str_ireplace($mod, '', $name);
                $style += ['bold' => $value];
            }
        }

        if (!preg_match_all('/([b|B|f|F]g)?([A-Z][a-z]+)([^A-Z])?/', $name, $matches)) {
            return [lcfirst($name) ?: 'line', $text, $style];
        }

        [$name, $style] = $this->buildStyle($name, $style, $matches);

        return [$name, $text, $style];
    }

    /**
     * Build style parameter from matching combination.
     */
    protected function buildStyle(string $name, array $style, array $matches): array
    {
        foreach ($matches[0] as $i => $match) {
            $name  = str_replace($match, '', $name);
            $type  = strtolower($matches[1][$i]) ?: 'fg';

            if (defined($color = static::class . '::' . strtoupper($matches[2][$i]))) {
                $style += [$type => constant($color)];
            }
        }

        return [lcfirst($name) ?: 'line', $style];
    }
}
