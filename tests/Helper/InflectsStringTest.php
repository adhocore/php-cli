<?php

namespace Ahc\Cli\Test\Helper;

use Ahc\Cli\Helper\InflectsString;

use PHPUnit\Framework\TestCase;

class InflectsStringTest extends TestCase
{
    use InflectsString;

    public function test_to_camel_case()
    {
        $this->assertSame('aB', $this->toCamelCase('a-b'));
        $this->assertSame('theLongName', $this->toCamelCase('--the_long-name'));
        $this->assertSame('aBC', $this->toCamelCase('a_bC'));
    }
}
