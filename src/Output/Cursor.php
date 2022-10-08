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

use function max;
use function sprintf;
use function str_repeat;

/**
 * Cli Cursor.
 *
 * @author  Jitendra Adhikari <jiten.adhikary@gmail.com>
 * @license MIT
 *
 * @link   static  https://github.com/adhocore/cli
 */
class Cursor
{
    /**
     * Returns signal to move cursor up `n` times.
     *
     * @param int $n Times
     *
     * @return string
     */
    public function up(int $n = 1): string
    {
        return sprintf("\e[%dA", max($n, 1));
    }

    /**
     * Returns signal to move cursor down `n` times.
     *
     * @param int $n Times
     *
     * @return string
     */
    public function down(int $n = 1): string
    {
        return sprintf("\e[%dB", max($n, 1));
    }

    /**
     * Returns signal to move cursor right `n` times.
     *
     * @param int $n Times
     *
     * @return string
     */
    public function right(int $n = 1): string
    {
        return sprintf("\e[%dC", max($n, 1));
    }

    /**
     * Returns signal to move cursor left `n` times.
     *
     * @param int $n Times
     *
     * @return string
     */
    public function left(int $n = 1): string
    {
        return sprintf("\e[%dD", max($n, 1));
    }

    /**
     * Returns signal to move cursor next line `n` times.
     *
     * @param int $n Times
     *
     * @return string
     */
    public function next(int $n = 1): string
    {
        return str_repeat("\e[E", max($n, 1));
    }

    /**
     * Returns signal to move cursor prev line `n` times.
     *
     * @param int $n Times
     *
     * @return string
     */
    public function prev(int $n = 1): string
    {
        return str_repeat("\e[F", max($n, 1));
    }

    /**
     * Returns signal to erase current line.
     */
    public function eraseLine(): string
    {
        return "\e[2K";
    }

    /**
     * Returns signal to clear string.
     */
    public function clear(): string
    {
        return "\e[2J";
    }

    /**
     * Returns signal to erase lines upward.
     */
    public function clearUp(): string
    {
        return "\e[1J";
    }

    /**
     * Returns signal to erase lines downward.
     */
    public function clearDown(): string
    {
        return "\e[J";
    }

    /**
     * Returns signal to move cursor to given x, y position.
     */
    public function moveTo(int $x, int $y): string
    {
        return sprintf("\e[%d;%dH", $y, $x);
    }
}
