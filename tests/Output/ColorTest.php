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
use InvalidArgumentException;
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
        $this->assertSame("\033[2;37mcomment\033[0m", (new Color)->comment('comment'));
    }

    public function test_custom_style()
    {
        Color::style('alert', ['bg' => '48;5;82', 'fg' => '38;5;57']);

        $this->assertSame("\033[0;38;5;57;48;5;82malert\033[0m", (new Color)->alert('alert'));
        $this->assertSame("\033[1;38;5;57;48;5;82malert\033[0m", (new Color)->boldAlert('alert'));
        $this->assertSame("\033[1;38;5;57;48;5;82malert\033[0m", (new Color)->alertBold('alert'));
    }

    public function test_invalid_custom_style()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Trying to set empty or invalid style');

        Color::style('alert', ['invalid' => true]);
    }

    public function test_invisible_built_in_style()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Built-in styles cannot be invisible (matching background and foreground)');

        Color::style('error', ['bg' => Color::RED, 'fg' => Color::RED]);
    }

    public function test_colors()
    {
        $c = new Color;

        // We use PHP_EOL here because it is platform dependent and eol tag will be replaced by it.
        $this->assertSame(PHP_EOL . 'abc' . PHP_EOL, $c->colors('<eol>abc</eol>'));
        $this->assertSame("\033[0;31mRed\033[0m", $c->colors('<red>Red</end>'));
        $this->assertSame("\033[1;31mBoldRed" . PHP_EOL . "\033[0m", $c->colors('<boldRed>BoldRed<eol/></end>'));
        $this->assertSame("\033[0;36;42mBgGreenCyan\033[0m" . PHP_EOL, $c->colors('<bgGreenCyan>BgGreenCyan</end><eol>'));
        $this->assertSame(
            "\033[0;31mRed\033[0m" . PHP_EOL . 'Normal' . PHP_EOL . "\033[1;37mBOLD\033[0m",
            $c->colors("<red>Red</end>\r\nNormal\n<bold>BOLD</end>")
        );
    }

    public function test_magic_call()
    {
        $this->assertSame("\033[1;37mline\033[0m", (new Color)->bold('line'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Text required');

        (new Color)->bgRed();
    }

    public function test_magic_call_color()
    {
        $this->assertSame("\033[0;35mpurple\033[0m", (new Color)->purple('purple'));
    }

    public function test_magic_call_invalid()
    {
        $this->expectException(InvalidArgumentException::class);
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
