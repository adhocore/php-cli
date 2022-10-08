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
        $title  = $body = $dash = [];

        [$start, $end] = $styles['head'];
        foreach ($head as $col => $size) {
            $dash[]  = str_repeat('-', $size + 2);
            $title[] = str_pad($this->toWords($col), $size, ' ');
        }

        $title = "|$start " . implode(" $end|$start ", $title) . " $end|" . PHP_EOL;

        $odd = true;
        foreach ($rows as $row) {
            $parts = [];

            [$start, $end] = $styles[['even', 'odd'][(int) $odd]];
            foreach ($head as $col => $size) {
                $parts[] = str_pad($row[$col] ?? '', $size, ' ');
            }

            $odd    = !$odd;
            $body[] = "|$start " . implode(" $end|$start ", $parts) . " $end|";
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
            $cols   = array_column($rows, $col);
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
            if (isset($default[$for])) {
                $default[$for] = ['<' . trim($style, '<> ') . '>', '</end>'];
            }
        }

        return $default;
    }
}
