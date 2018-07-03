<?php

namespace Ahc\Cli\Test;

use Ahc\Cli\Output\Color;
use PHPUnit\Framework\TestCase;

class ColorTest extends TestCase
{
    /** @dataProvider methods */
    public function test_methods($method, $color)
    {
        $this->assertSame("\033[0;{$color}m{$method}\033[0m", (new Color)->{$method}($method));
    }

    public function test_comment()
    {
        $this->assertSame("\033[1;30mcomment\033[0m", (new Color)->comment('comment'));
    }

    public function test_custom_style()
    {
        Color::style('alert', ['bg' => Color::YELLOW, 'fg' => Color::RED, 'bold' => 1]);

        $this->assertSame("\033[1;31;43malert\033[0m", (new Color)->alert('alert'));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Trying to define existing style');

        Color::style('alert', ['bg' => Color::BLACK]);
    }

    public function test_invalid_custom_style()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Trying to set empty or invalid style');

        Color::style('alert', ['invalid' => true]);
    }

    public function test_magic_call()
    {
        $this->assertSame("\033[1;37mline\033[0m", (new Color)->bold('line'));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Text required');

        (new Color)->bgRed();
    }

    public function test_magic_call_color()
    {
        $this->assertSame("\033[0;35mpurple\033[0m", (new Color)->purple('purple'));
    }

    public function test_magic_call_invalid()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Style "random" not defined');

        (new Color)->random('Rand');
    }

    public function methods()
    {
        return [
            ['error', 31],
            ['ok', 32],
            ['warn', 33],
            ['info', 34],
        ];
    }
}
