<?php

namespace Ahc\Cli\Output;

/**
 * Cli Curser.
 *
 * @author  Jitendra Adhikari <jiten.adhikary@gmail.com>
 * @license MIT
 *
 * @link   static  https://github.com/adhocore/cli
 */
class Cursor
{
    public function up(int $n = 1): string
    {
        return \sprintf("\e[%dA", \max($n, 1));
    }

    public function down(int $n = 1): string
    {
        return \sprintf("\e[%dB", \max($n, 1));
    }

    public function right(int $n = 1): string
    {
        return \sprintf("\e[%dC", \max($n, 1));
    }

    public function left(int $n = 1): string
    {
        return \sprintf("\e[%dD", \max($n, 1));
    }

    public function next(int $n = 1)
    {
        return \str_repeat("\e[E", \max($n, 1));
    }

    public function prev(int $n = 1)
    {
        return \str_repeat("\e[F", \max($n, 1));
    }

    public function eraseLine()
    {
        return "\e[2K";
    }

    public function clear()
    {
        return "\e[2J";
    }

    public function clearUp()
    {
        return "\e[1J";
    }

    public function clearDown()
    {
        return "\e[J";
    }

    public function moveTo(int $x, int $y)
    {
        return \sprintf("\e[%d;%dH", $y, $x);
    }
}
