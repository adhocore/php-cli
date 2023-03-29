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

use Ahc\Cli\Helper\Terminal;
use Ahc\Cli\Output\ProgressBar;
use Ahc\Cli\Output\Writer;
use Ahc\Cli\Test\CliTestCase;
use ArrayIterator;
use IteratorAggregate;
use UnexpectedValueException;

class ProgressBarTest extends CliTestCase
{
    public function test_progress_bar()
    {
        $progress = new ProgressBar(10, new Writer(static::$ou));
        for ($i = 1; $i <= 10; $i++) {
            $progress->advance(1, "$i x label");
            if ($i === 2) {
                $progress->forceRedraw(true);
                $progress->current(2, '2 x label');
                $progress->forceRedraw(false);
            }
        }

        $this->assertBufferContains('===========================================> 100%');
        $this->assertBufferContains('10 x label');

        $this->expectException(UnexpectedValueException::class);
        $progress->current(11);
    }

    public function test_progress_bar_option()
    {
        $progress = new ProgressBar(10, new Writer(static::$ou));
        $progress->option('loader', '#');
        $progress->option(['pointer' => '~']);
        for ($i = 1; $i <= 10; $i++) {
            $label = $i === 5 ? '' : '#' . ($i % 3) . ' label';
            $progress->advance(1, $label);
        }

        $this->assertBufferContains('###########################################~ 100%');
        $this->assertBufferContains('#1 label');

        $this->expectException(UnexpectedValueException::class);
        $progress->option('color', '');
    }

    public function test_progress_bar_each()
    {
        $progress = new ProgressBar(3, new Writer(static::$ou));
        $progress->each([]);
        $progress->each(new class() implements IteratorAggregate {
            public $a = 1;
            public $b = 2;
            public $c = 3;

            public function getIterator()
            {
                return new ArrayIterator($this);
            }
        }, fn ($v, $k) => "$k: $v");

        $this->assertBufferContains('===========================================> 100%');
        $this->assertBufferContains('c: 3');
        $this->assertNotNull((new Terminal)->height());

        $this->expectException(UnexpectedValueException::class);
        (new ProgressBar(1))->current(2);
    }
}
