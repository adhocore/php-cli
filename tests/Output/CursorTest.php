<?php

/*
 * This file is part of the PHP-CLI package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

namespace Ahc\Cli\Test\Output;

use Ahc\Cli\Output\Cursor;
use PHPUnit\Framework\TestCase;

class CursorTest extends TestCase
{
    public function test()
    {
        ob_start();

        $c = new Cursor();

        echo $c->up(1) . $c->down(2) . $c->right(3) . $c->left(4) . $c->next(0) . $c->prev(2);

        $this->assertSame("\e[1A\e[2B\e[3C\e[4D\e[E\e[F\e[F", ob_get_clean());
    }

    public function test_clean()
    {
        ob_start();

        $c = new Cursor();

        echo $c->eraseLine() . $c->clear() . $c->clearUp() . $c->clearDown();

        $this->assertSame("\e[2K\e[2J\e[1J\e[J", ob_get_clean());
    }

    public function test_move()
    {
        ob_start();

        $c = new Cursor();

        echo $c->moveTo(1, 2) . $c->moveTo(5, 8);

        $this->assertSame("\e[2;1H\e[8;5H", ob_get_clean());
    }
}
