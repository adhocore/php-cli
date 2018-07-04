<?php

namespace Ahc\Cli\Test\Helper;

use Ahc\Cli\Helper\OutputHelper;
use Ahc\Cli\Input\Argument;
use Ahc\Cli\Input\ArgvParser as Command;
use Ahc\Cli\Input\Option;
use Ahc\Cli\IO\Interactor;
use Ahc\Cli\Output\Color;
use Ahc\Cli\Output\Writer;
use PHPUnit\Framework\TestCase;

class InteractorTest extends TestCase
{
    protected static $in = __DIR__ . '/input';
    protected static $ou = __DIR__ . '/output';

    public function setUp()
    {
        file_put_contents(static::$in, '');
        file_put_contents(static::$ou, '');
    }

    public function tearDown()
    {
        unlink(static::$in);
        unlink(static::$ou);
    }

    public function test_confirm()
    {
        $i = $this->newInteractor('n');
        $this->assertFalse($i->confirm('OK?', 'y'));

        $i = $this->newInteractor('');
        $this->assertTrue($i->confirm('OK?', 'y'));

        $i = $this->newInteractor('Z');
        $this->assertTrue($i->confirm('OK?', 'y'));
    }

    public function test_confirm_more()
    {
        $i = $this->newInteractor('n');

        $this->assertFalse($i->confirm('OK?'));
    }

    public function test_choice()
    {
        $i = $this->newInteractor('a');

        $this->assertSame('a', $i->choice('Select one', ['a', 'b', 'c']));
    }

    public function test_choice_more()
    {
        $i = $this->newInteractor('x');

        $this->assertSame('c', $i->choice('Select one', ['a', 'b', 'c'], 'c'));
    }

    public function test_choices()
    {
        $i = $this->newInteractor('a,b');

        $this->assertSame(['a', 'b'], $i->choices('Select many', ['a', 'b', 'c']));
    }

    public function test_choices_more()
    {
        $i = $this->newInteractor('a,d');

        $this->assertSame(['a'], $i->choices('Select many', ['a' => 'apple', 'b' => 'ball']));
    }

    public function test_prompt()
    {
        $i = $this->newInteractor('whatever');

        $this->assertSame('whatever', $i->prompt('type anything', ''));
    }

    public function test_prompt_default()
    {
        $i = $this->newInteractor('');

        $this->assertSame('def', $i->prompt('type anything', 'def'));
    }

    public function test_prompt_filter()
    {
        $i = $this->newInteractor("1\n3\n5");

        $this->assertSame(5, $i->prompt('gte 5', null, function ($v) {
            if ((int) $v < 5) {
                throw new \Exception('gte 5');
            }

            return (int) $v;
        }));

        $this->assertContains('gte 5', file_get_contents(static::$ou));
    }

    public function test_call()
    {
        $i = $this->newInteractor('');

        $this->assertSame('a', $i->read('a'));

        $i->write(__METHOD__);
        $this->assertContains(__METHOD__, file_get_contents(static::$ou));
    }

    protected function newInteractor(string $in = '')
    {
        file_put_contents(static::$in, $in);

        return new Interactor(static::$in, static::$ou);
    }
}
