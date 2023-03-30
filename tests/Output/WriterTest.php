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

use Ahc\Cli\Output\Color;
use Ahc\Cli\Output\Writer;
use Ahc\Cli\Test\CliTestCase;
use InvalidArgumentException;

use function substr_count;

class WriterTest extends CliTestCase
{
    public function test_simple_write()
    {
        (new Writer)->write('Hey');

        $this->assertStringContainsString('Hey', $this->buffer());
        $this->assertSame("\033[0;37mHey\033[0m", $this->buffer());
    }

    public function test_write_error()
    {
        (new Writer)->error->write('Something wrong');

        $this->assertStringContainsString('Something wrong', $this->buffer());
        $this->assertSame("\033[0;31mSomething wrong\033[0m", $this->buffer());
    }

    public function test_write_with_newline()
    {
        (new Writer)->write('Hello', true);

        $this->assertStringContainsString('Hello', $this->buffer());
        $this->assertSame("\033[0;37mHello\033[0m" . PHP_EOL, $this->buffer());
    }

    public function test_write_bold_red_bggreen()
    {
        (new Writer)->bold->red->bgGreen->write('bold->red->bgGreen');

        $this->assertStringContainsString('bold->red->bgGreen', $this->buffer());
        $this->assertSame("\033[1;31;42mbold->red->bgGreen\033[0m", $this->buffer());
    }

    public function test_raw()
    {
        $w = new Writer(static::$ou);

        $w->raw(new class {
            public function __toString()
            {
                return __FUNCTION__;
            }
        })->clear();

        $this->assertSame("__toString\e[2J", $this->buffer());
    }

    public function test_empty_table()
    {
        $w = new Writer(static::$ou);

        $w->table([]);

        $this->assertSame('', $this->buffer(), 'empty');
    }

    public function test_table()
    {
        $w = new Writer(static::$ou);

        $w->table([
            ['a' => 'apple', 'b-c' => 'ball', 'c_d' => 'cat'],
            ['a' => 'applet', 'b-c' => 'bee', 'c_d' => 'cute'],
        ], [
            'head' => 'boldBgGreen',
            'odd'  => 'purple',
            'even' => 'cyan',
        ]);

        $this->assertSame(3, substr_count($this->buffer(), '+--------+------+------+'), '3 dashes');
        $this->assertBufferContains(
            "|\33[1;37;42m A      \33[0m|\33[1;37;42m B C  \33[0m|\33[1;37;42m C D  \33[0m|",
            'Head'
        );
        $this->assertBufferContains("|\33[0;35m apple  \33[0m|\33[0;35m ball \33[0m|\33[0;35m cat  \33[0m|", 'Odd');
        $this->assertBufferContains("|\33[0;36m applet \33[0m|\33[0;36m bee  \33[0m|\33[0;36m cute \33[0m|", 'Even');
    }

    public function test_table_throws()
    {
        $w = new Writer(static::$ou);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Rows must be array of assoc arrays');

        $w->table([1, 2]);
    }

    public function test_colorizer()
    {
        $this->assertInstanceOf(Color::class, (new Writer)->colorizer());
    }
}
