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
        $this->assertSame(0, $shell->getExitCode());
    }

    public function test_get_process_id()
    {
        $shell = new Shell('echo hello');

        $shell->execute(true);

        $this->assertInternalType('int', $pid = $shell->getProcessId());
        $this->assertGreaterThan(getmypid(), $pid);
    }

    public function test_async_stop()
    {
        $shell = new Shell('sleep 1 && echo hello');

        $this->assertFalse($shell->isRunning());

        $shell->execute(true);

        $this->assertTrue($shell->isRunning());

        $shell->stop();

        $this->assertSame('closed', $shell->getState());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Process timeout occurred
     */
    public function test_timeout()
    {
        $shell = new Shell('sleep 1');

        try {
            $shell->setOptions(null, null, 0.01)->execute();
        } catch (\Throwable $e) {
            $this->assertSame('terminated', $shell->getState());

            throw $e;
        }
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Process is already running
     */
    public function test_rerun()
    {
        $shell = new Shell('sleep 1');

        $shell->execute(true)->execute();
    }

    public function test_error_output()
    {
        $shell = new Shell('php -r "fwrite(STDERR, \'error occurred\');"');

        $this->assertSame($shell->execute()->getErrorOutput(), 'error occurred');
    }

    public function test_exitcode()
    {
        $shell = new Shell('php -v');

        $this->assertNull($shell->getExitCode());
    }
}
