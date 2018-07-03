<?php

namespace Ahc\Cli\Test;

use Ahc\Cli\Input\Option;
use PHPUnit\Framework\TestCase;

class OptionTest extends TestCase
{
    /** @dataProvider data */
    public function test_new($cmd, $expect)
    {
        $o = new Option($cmd);

        $more = [];
        if (isset($expect['default'])) {
            $more += ['default' => $o->default()];
        }
        if (isset($expect['bool'])) {
            $more += ['bool' => $o->bool()];
        }

        $this->assertEquals($cmd, $o->raw());

        $this->assertEquals($expect, [
            'long'     => $o->long(),
            'short'    => $o->short(),
            'required' => $o->required(),
            'variadic' => $o->variadic(),
            'name'     => $o->name(),
            'aname'    => $o->attributeName(),
        ] + $more);
    }

    public function test_is()
    {
        $o = new Option('-a --age');

        $this->assertTrue($o->is('-a'));
        $this->assertTrue($o->is('--age'));

        $this->assertFalse($o->is('--rage'));
        $this->assertFalse($o->is('--k'));
        $this->assertFalse($o->is('a'));
        $this->assertFalse($o->is('age'));
    }

    public function test_filter()
    {
        $o = new Option('-a --age', 'Age', 18, 'intval');

        $this->assertSame(18, $o->default());
        $this->assertSame(10, $o->filter('10'));

        $in = 'apple';
        $o  = new Option('-f, --fruit', 'Age', 'orange', 'strtoupper');

        $this->assertNotSame($o->filter($in), $in);
        $this->assertSame('APPLE', $o->filter($in));

        $this->assertSame('orange', $o->default(), 'default shouldnt be filtered');

        $o = new Option('--long-only');
        $this->assertSame($r = rand(), $o->filter($r));
    }

    public function data()
    {
        $f = require __DIR__ . '/fixture.php';

        return $f['options'];
    }
}
