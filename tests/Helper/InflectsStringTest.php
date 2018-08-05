<?php

/*
 * This file is part of the PHP-CLI package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     https:github.comadhocore
 *
 * Licensed under MIT license.
 *
 */

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

    public function test_to_words()
    {
        $this->assertSame('A B', $this->toWords('a-b'));
        $this->assertSame('The Long Name', $this->toWords('--the_long-name'));
        $this->assertSame('A BC', $this->toWords('a_bC'));
    }
}
