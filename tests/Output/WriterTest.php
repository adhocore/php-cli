<?php

namespace Ahc\Cli\Test\Output;

use Ahc\Cli\Output\Color;
use Ahc\Cli\Output\Writer;
use Ahc\Cli\Test\CliTestCase;

class WriterTest extends CliTestCase
{
    public function test_simple_write()
    {
        (new Writer)->write('Hey');

        $this->assertContains('Hey', $this->buffer());
        $this->assertSame("\033[0;37mHey\033[0m", $this->buffer());
    }

    public function test_write_error()
    {
        (new Writer)->error->write('Something wrong');

        $this->assertContains('Something wrong', $this->buffer());
        $this->assertSame("\033[0;31mSomething wrong\033[0m", $this->buffer());
    }

    public function test_write_with_newline()
    {
        (new Writer)->write('Hello', true);

        $this->assertContains('Hello', $this->buffer());
        $this->assertSame("\033[0;37mHello\033[0m" . PHP_EOL, $this->buffer());
    }

    public function test_write_bold_red_bggreen()
    {
        (new Writer)->bold->red->bgGreen->write('bold->red->bgGreen');

        $this->assertContains('bold->red->bgGreen', $this->buffer());
        $this->assertSame("\033[1;31;42mbold->red->bgGreen\033[0m", $this->buffer());
    }

    public function test_raw()
    {
        $w = new Writer($ou = __DIR__ . '/output');

        $w->raw(new class {
            public function __toString()
            {
                return __FUNCTION__;
            }
        })->clear();

        $out = file_get_contents($ou);
        $this->assertSame("__toString\e[2J", $out);

        unlink($ou);
    }

    public function test_colorizer()
    {
        $this->assertInstanceOf(Color::class, (new Writer)->colorizer());
    }
}
