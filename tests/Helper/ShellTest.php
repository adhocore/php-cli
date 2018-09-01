<?php

/*
 * This file is part of the PHP-CLI package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

namespace Ahc\Cli\Test\Helper;

use Ahc\Cli\Helper\Shell;
use PHPUnit\Framework\TestCase;

class ShellTest extends TestCase
{
    public function test_get_output()
    {
        $shell = new Shell('echo hello');
        $shell->execute();
        $this->assertSame("hello\n", $shell->getOutput());
    }

    public function test_get_process_id()
    {
        $shell = new Shell('echo hello');
        $shell->execute();
        $this->assertInternalType('int', $shell->getProcessId());
    }
}
