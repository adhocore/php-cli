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
    /**
     * Get the width of the terminal
     */
    public function width(): ?int
    {
        return $this->getDimension('width');
    }

    /**
     * Get the height of the terminal
     */
    public function height(): ?int
    {
        return $this->getDimension('height');
    }

    /**
     * Get specified terminal dimension
     */
    protected function getDimension(string $key): ?int
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $index      = array_search($key, ['height', 'width']);
            $dimensions = $this->getDimensions();

            return $dimensions[$index] ?? null;
        }

        $commands = [
            'width'  => 'cols',
            'height' => 'lines',
        ];
        $type = $commands[$key];

        $result = exec("tput {$type} 2>/dev/null");

        if ($result !== false) {
            return (int) $result;
        }

        return null;
    }

    /**
     * Get information about the dimensions of the Windows terminal
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
