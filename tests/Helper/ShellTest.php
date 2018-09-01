<?php

/*
 * Test for shell wrapper for package adhocore/php-cli.
 * @author Sushil Gupta <desushil@gmail.com>
 * @license MIT
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
