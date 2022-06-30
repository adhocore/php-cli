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

        $this->assertIsInt($pid = $shell->getProcessId());
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

    public function test_timeout()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Timeout occurred, process terminated.');

        $shell = new Shell('sleep 1');

        try {
            $shell->setOptions(null, null, 0.01)->execute();
        } catch (\Throwable $e) {
            $this->assertSame('terminated', $shell->getState());

            throw $e;
        }
    }

    public function test_rerun()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Process is already running');

        $shell = new Shell('sleep 1');

        $shell->execute(true)->execute();
    }

    public function test_error_output()
    {
        $shell = new Shell('false');

        $this->assertSame(1, $shell->execute()->getExitCode());
    }

    public function test_exitcode()
    {
        $shell = new Shell('php -v');

        $this->assertSame(0, $shell->execute()->getExitCode());
    }
}
