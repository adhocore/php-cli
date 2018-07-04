<?php

namespace Ahc\Cli\Test\Input;

use Ahc\Cli\Input\Reader;
use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{
    protected static $in = __DIR__ . '/input';

    public function setUp()
    {
        file_put_contents(static::$in, '');
    }

    public static function tearDownAfterClass()
    {
        unlink(static::$in);
    }

    public function test_default()
    {
        $r = new Reader(static::$in);

        $this->assertSame('dflt', $r->read('dflt'));
    }

    public function test_callback()
    {
        file_put_contents(static::$in, 'the value');

        $r = new Reader(static::$in);

        $this->assertSame('The Value', $r->read('dflt', function ($v) {
            return \ucwords($v);
        }));
    }
}
