<?php

/*
 * This file is part of the PHP-CLI package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

namespace Ahc\Cli\Test;

use PHPUnit\Framework\TestCase;

/**
 * To test console output.
 */
class CliTestCase extends TestCase
{
    protected static $ou = __DIR__ . '/output';

    public static function setUpBeforeClass()
    {
        // Thanks: https://stackoverflow.com/a/39785995
        stream_filter_register('intercept', StreamInterceptor::class);
        stream_filter_append(\STDOUT, 'intercept');
        stream_filter_append(\STDERR, 'intercept');
    }

    public function setUp()
    {
        ob_start();
        StreamInterceptor::$buffer = '';
        file_put_contents(static::$ou, '');
    }

    public function tearDown()
    {
        ob_end_clean();
    }

    public static function tearDownAfterClass()
    {
        unlink(static::$ou);
    }

    public function buffer()
    {
        return StreamInterceptor::$buffer ?: file_get_contents(static::$ou);
    }

    public function assertBufferContains($expect)
    {
        $this->assertContains($expect, $this->buffer());
    }
}

class StreamInterceptor extends \php_user_filter
{
    public static $buffer = '';

    public function filter($in, $out, &$consumed, $closing)
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            static::$buffer .= $bucket->data;
        }

        return PSFS_PASS_ON;
    }
}
