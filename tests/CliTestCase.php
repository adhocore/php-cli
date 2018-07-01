<?php

namespace Ahc\Cli\Test;

use PHPUnit\Framework\TestCase;

/**
 * To test console output.
 */
class CliTestCase extends TestCase
{
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
    }

    public function tearDown()
    {
        ob_end_clean();
    }

    public function buffer()
    {
        return StreamInterceptor::$buffer;
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
