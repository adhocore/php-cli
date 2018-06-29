<?php

namespace Ahc\Cli\Test;

use Ahc\Cli\Writer;
use PHPUnit\Framework\TestCase;

class WriterTest extends TestCase
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
        StreamInterceptor::$buffer = '';
    }

    public function test_simple_write()
    {
        (new Writer)->write('Hey');

        $this->assertContains('Hey', StreamInterceptor::$buffer);
        $this->assertSame("\033[0;37mHey\033[0m", StreamInterceptor::$buffer);
    }

    public function test_write_error()
    {
        (new Writer)->error->write('Something wrong');

        $this->assertContains('Something wrong', StreamInterceptor::$buffer);
        $this->assertSame("\033[0;31mSomething wrong\033[0m", StreamInterceptor::$buffer);
    }

    public function test_write_with_newline()
    {
        (new Writer)->write('Hello', true);

        $this->assertContains('Hello', StreamInterceptor::$buffer);
        $this->assertSame("\033[0;37mHello\033[0m" . PHP_EOL, StreamInterceptor::$buffer);
    }

    public function test_write_bold_red_bggreen()
    {
        (new Writer)->bold->red->bgGreen->write('bold->red->bgGreen');

        $this->assertContains('bold->red->bgGreen', StreamInterceptor::$buffer);
        $this->assertSame("\033[1;31;42mbold->red->bgGreen\033[0m", StreamInterceptor::$buffer);
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
