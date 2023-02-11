<?php

/*
 * This file is part of the PHP-CLI package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

namespace Ahc\Cli\Test\Input;

use Ahc\Cli\Input\Reader;
use PHPUnit\Framework\TestCase;
use function ucwords;

class ReaderTest extends TestCase
{
    protected static $in = __DIR__ . '/input';

    public function setUp(): void
    {
        file_put_contents(static::$in, '', LOCK_EX);
    }

    public static function tearDownAfterClass(): void
    {
        // Make sure we clean up after ourselves:
        if (file_exists(static::$in)) {
            unlink(static::$in);
        }
    }

    public function test_default()
    {
        $r = new Reader(static::$in);

        $this->assertSame('dflt', $r->read('dflt'));
    }

    public function test_callback()
    {
        file_put_contents(static::$in, 'the value', LOCK_EX);

        $r = new Reader(static::$in);

        $this->assertSame('The Value', $r->read('dflt', function ($v) {
            return ucwords($v);
        }));
    }
}
