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

use Ahc\Cli\Input\Argument;
use PHPUnit\Framework\TestCase;

class ArgumentTest extends TestCase
{
    public function test()
    {
        $a = new Argument('<a>');
        $this->assertTrue($a->required());
        $this->assertFalse($a->optional());

        $a = new Argument('[b:ball]');
        $this->assertFalse($a->required());
        $this->assertSame('ball', $a->default());

        $a = new Argument('[thing:a+b,c+d...]');
        $this->assertSame('thing', $a->name());
        $this->assertTrue($a->variadic());
        $this->assertSame(['a b', 'c d'], $a->default());
    }
}
