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
use Ahc\Cli\Helper\InflectsString;

use function array_column;
use function array_fill_keys;
use function array_keys;
use function array_map;
use function array_merge;
use function gettype;
use function implode;
use function is_array;
use function max;
use function reset;
use function sprintf;
use function str_pad;
use function str_repeat;
use function strlen;
use function trim;

use const PHP_EOL;

class Table
{
    use InflectsString;

    public function render(array $rows, array $styles = []): string
    {
        if ([] === $table = $this->normalize($rows)) {
            return '';
        }

        [$head, $rows] = $table;

        $styles = $this->normalizeStyles($styles);
        $title  = $body = $dash = $positions = [];

        [$start, $end] = $styles['head'];
        $pos           = 0;
        foreach ($head as $col => $size) {
            $dash[]          = str_repeat('-', $size + 2);
            $title[]         = str_pad($this->toWords($col), $size, ' ');
            $positions[$col] = ++$pos;
        }

        $title = "|$start " . implode(" $end|$start ", $title) . " $end|" . PHP_EOL;

        $odd = true;
        foreach ($rows as $line => $row) {
            $parts = [];
            $line++;

            [$start, $end] = $styles[['even', 'odd'][(int) $odd]];
            foreach ($head as $col => $size) {
                $colNumber = $positions[$col];

                if (isset($styles[$line . ':' . $colNumber])) { // cell, 1:1
                    $style = $styles[$line . ':' . $colNumber];
                } elseif (isset($styles[$col]) || isset($styles['*:' . $colNumber])) { // col, *:2 or b
                    $style = $styles['*:' . $colNumber] ?? $styles[$col];
                } elseif (isset($styles[$line . ':*'])) { // row, 2:*
                    $style = $styles[$line . ':*'];
                } elseif (isset($styles['*:*'])) { // any cell, *:*
                    $style = $styles['*:*'];
                } else {
                    $style = $styles[['even', 'odd'][(int) $odd]];
                }

                $text          = $row[$col] ?? '';
                [$start, $end] = $this->parseStyle($style, $text, $row, $rows);

                if (preg_match('/(\\x1b(?:.+)m)/U', $text, $matches)) {
                    $word = str_replace($matches[1], '', $text);
                    $word = preg_replace('/\\x1b\[0m/', '', $word);

                    $size += strlen($text) - strlen($word);
                }

                $parts[] = "$start " . str_pad($text, $size, ' ') . " $end";
            }

            $odd    = !$odd;
            $body[] = '|' . implode('|', $parts) . '|';
        }

        $dash  = '+' . implode('+', $dash) . '+' . PHP_EOL;
        $body  = implode(PHP_EOL, $body) . PHP_EOL;

        return "$dash$title$dash$body$dash";
    }

    protected function normalize(array $rows): array
    {
        $head = reset($rows);
        if (empty($head)) {
            return [];
        }

        if (!is_array($head)) {
            throw new InvalidArgumentException(
                sprintf('Rows must be array of assoc arrays, %s given', gettype($head))
            );
        }

        $head = array_fill_keys(array_keys($head), null);
        foreach ($rows as $i => &$row) {
            $row = array_merge($head, $row);
        }

        foreach ($head as $col => &$value) {
            $cols = array_column($rows, $col);
            $cols = array_map(function ($col) {
                $col ??= '';

                if (preg_match('/(\\x1b(?:.+)m)/U', $col, $matches)) {
                    $col = str_replace($matches[1], '', $col);
                    $col = preg_replace('/\\x1b\[0m/', '', $col);
                }

                return $col;
            }, $cols);

            $span   = array_map('strlen', $cols);
            $span[] = strlen($col);
            $value  = max($span);
        }

        return [$head, $rows];
    }

    protected function normalizeStyles(array $styles): array
    {
        $default = [
            // styleFor => ['styleStartFn', 'end']
            'head' => ['', ''],
            'odd'  => ['', ''],
            'even' => ['', ''],
        ];

        foreach ($styles as $for => $style) {
            if (is_string($style) && $style !== '') {
                $default[$for] = ['<' . trim($style, '<> ') . '>', '</end>'];
            } elseif (str_contains($for, ':') && is_callable($style)) {
                $default[$for] = $style;
            }
        }

        return $default;
    }

    protected function parseStyle(array|callable $style, $val, array $row, array $table): array
    {
        if (is_array($style)) {
            return $style;
        }

        $style = call_user_func($style, $val, $row, $table);

        if (is_string($style) && $style !== '') {
            return ['<' . trim($style, '<> ') . '>', '</end>'];
        }
        if (is_array($style) && count($style) === 2) {
            return $style;
        }

        return ['', ''];
    }
}
