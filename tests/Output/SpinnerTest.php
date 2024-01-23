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

use Ahc\Cli\Output\ProgressBar;
use Ahc\Cli\Output\Spinner;
use Ahc\Cli\Output\Writer;
use Ahc\Cli\Test\CliTestCase;
use InvalidArgumentException;

class SpinnerTest extends CliTestCase
{
    public function test_spinner()
    {
        $progress = new ProgressBar(null, new Writer(static::$ou));
        $spinner = new Spinner(10, $progress);
        for ($i = 1; $i <= 10; $i++) {
            $spinner->advance(1, "$i x label");
        }

        $this->assertBufferContains('⠏');
        $this->assertBufferContains('⠛');
        $this->assertBufferContains('⠹');
        $this->assertBufferContains('⢸');
        $this->assertBufferContains('⣰');
        $this->assertBufferContains('⣤');
        $this->assertBufferContains('⣆', '⡇');
        $this->assertBufferContains('⡇');
        $this->assertBufferContains('✔ 100%');
        $this->assertBufferContains('10 x label');
    }

    public function test_spinner_indicators()
    {
        $progress = new ProgressBar(null, new Writer(static::$ou));
        $spinner = new Spinner(10, $progress);
        $spinner->indicators(['-', '\\', '|', '/']);
        for ($i = 1; $i <= 10; $i++) {
            $spinner->advance(1, "$i x label");
        }

        $this->assertBufferContains('-');
        $this->assertBufferContains('\\');
        $this->assertBufferContains('|');
        $this->assertBufferContains('/');
        $this->assertBufferContains('✔ 100%');
        $this->assertBufferContains('10 x label');

        $this->expectException(InvalidArgumentException::class);
        $spinner->indicators(['/']);
    }
}
