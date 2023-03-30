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

use function array_map;
use function array_search;
use function exec;
use function implode;
use function is_array;
use function preg_match_all;

/**
 * A thin to find some information about the current terminal (width, height, ect...).
 *
 * @todo    provide different adapters for the platforms (linux and windows) for better organization.
 *
 * @author  Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @license MIT
 *
 * @link    https://github.com/adhocore/cli
 */
class Terminal
{
    public static function isWindows(): bool
    {
        // If PHP_OS is defined, use it - More reliable:
        if (defined('PHP_OS')) {
            return str_starts_with(strtoupper(PHP_OS), 'WIN'); // May be 'WINNT' or 'WIN32' or 'Windows'
        }

        // @codeCoverageIgnoreStart
        return '\\' === DIRECTORY_SEPARATOR; // Fallback - Less reliable (Windows 7...)
        // @codeCoverageIgnoreEnd
    }

    /**
     * Get the width of the terminal.
     */
    public function width(): ?int
    {
        return $this->getDimension('width');
    }

    /**
     * Get the height of the terminal.
     */
    public function height(): ?int
    {
        return $this->getDimension('height');
    }

    /**
     * Get specified terminal dimension.
     */
    protected function getDimension(string $key): ?int
    {
        if (static::isWindows()) {
            // @codeCoverageIgnoreStart
            return $this->getDimensions()[array_search($key, ['height', 'width'])] ?? null;
            // @codeCoverageIgnoreEnd
        }

        $type   = ['width'  => 'cols', 'height' => 'lines'][$key];
        $result = exec("tput {$type} 2>/dev/null");

        return $result === false ? null : (int) $result;
    }

    /**
     * Get information about the dimensions of the Windows terminal.
     *
     * @codeCoverageIgnore
     *
     * @return int[]
     */
    protected function getDimensions(): array
    {
        exec('mode CON', $output);

        if (!is_array($output)) {
            return [];
        }

        $output = implode("\n", $output);

        preg_match_all('/.*:\s*(\d+)/', $output, $matches);

        return array_map('intval', $matches[1] ?? []);
    }
}
