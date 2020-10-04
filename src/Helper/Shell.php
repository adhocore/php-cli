<?php

/*
 * This file is part of the PHP-CLI package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

namespace Ahc\Cli\Helper;

use Ahc\Cli\Exception\RuntimeException;

/**
 * A thin proc_open wrapper to execute shell commands.
 *
 * With some inspirations from symfony/process.
 *
 * @author  Sushil Gupta <desushil@gmail.com>
 * @license MIT
 *
 * @link    https://github.com/adhocore/cli
 */
class Shell
{
    const STDIN_DESCRIPTOR_KEY  = 0;
    const STDOUT_DESCRIPTOR_KEY = 1;
    const STDERR_DESCRIPTOR_KEY = 2;

    const STATE_READY      = 'ready';
    const STATE_STARTED    = 'started';
    const STATE_CLOSED     = 'closed';
    const STATE_TERMINATED = 'terminated';

    /** @var bool Whether to wait for the process to finish or return instantly */
    protected $async = false;

    /** @var string Command to be executed */
    protected $command;

    /** @var string Current working directory */
    protected $cwd = null;

    /** @var array Descriptor to be passed for proc_open */
    protected $descriptors;

    /** @var array An array of environment variables */
    protected $env = null;

    /** @var int Exit code of the process once it has been terminated */
    protected $exitCode = null;

    /** @var string Input for stdin */
    protected $input;

    /** @var array Other options to be passed for proc_open */
    protected $otherOptions = [];

    /** @var array Pointers to stdin, stdout & stderr */
    protected $pipes = null;

    /** @var resource The actual process resource returned from proc_open */
    protected $process = null;

    /** @var int Process starting time in unix timestamp */
    protected $processStartTime;

    /** @var array Status of the process as returned from proc_get_status */
    protected $processStatus = null;

    /** @var float Default timeout for the process in seconds with microseconds */
    protected $processTimeout = null;

    /** @var string Current state of the shell execution, set from this class, NOT for proc_get_status */
    protected $state = self::STATE_READY;

    public function __construct(string $command, string $input = null)
    {
        // @codeCoverageIgnoreStart
        if (!\function_exists('proc_open')) {
            throw new RuntimeException('Required proc_open could not be found in your PHP setup.');
        }
        // @codeCoverageIgnoreEnd

        $this->command = $command;
        $this->input   = $input;
    }

    protected function getDescriptors(): array
    {
        $out = $this->isWindows() ? ['file', 'NUL', 'w'] : ['pipe', 'w'];

        return [
            self::STDIN_DESCRIPTOR_KEY  => ['pipe', 'r'],
            self::STDOUT_DESCRIPTOR_KEY => $out,
            self::STDERR_DESCRIPTOR_KEY => $out,
        ];
    }

    protected function isWindows(): bool
    {
        return '\\' === \DIRECTORY_SEPARATOR;
    }

    protected function setInput()
    {
        \fwrite($this->pipes[self::STDIN_DESCRIPTOR_KEY], $this->input);
    }

    protected function updateProcessStatus()
    {
        if ($this->state !== self::STATE_STARTED) {
            return;
        }

        $this->processStatus = \proc_get_status($this->process);

        if ($this->processStatus['running'] === false && $this->exitCode === null) {
            $this->exitCode = $this->processStatus['exitcode'];
        }
    }

    protected function closePipes()
    {
        \fclose($this->pipes[self::STDIN_DESCRIPTOR_KEY]);
        \fclose($this->pipes[self::STDOUT_DESCRIPTOR_KEY]);
        \fclose($this->pipes[self::STDERR_DESCRIPTOR_KEY]);
    }

    protected function wait()
    {
        while ($this->isRunning()) {
            usleep(5000);
            $this->checkTimeout();
        }

        return $this->exitCode;
    }

    protected function checkTimeout()
    {
        if ($this->processTimeout === null) {
            return;
        }

        $executionDuration = \microtime(true) - $this->processStartTime;

        if ($executionDuration > $this->processTimeout) {
            $this->kill();

            throw new RuntimeException('Timeout occurred, process terminated.');
        }
        // @codeCoverageIgnoreStart
    }

    // @codeCoverageIgnoreEnd

    public function setOptions(
        string $cwd = null,
        array $env = null,
        float $timeout = null,
        array $otherOptions = []
    ): self {
        $this->cwd            = $cwd;
        $this->env            = $env;
        $this->processTimeout = $timeout;
        $this->otherOptions   = $otherOptions;

        return $this;
    }

    public function execute(bool $async = false): self
    {
        if ($this->isRunning()) {
            throw new RuntimeException('Process is already running.');
        }

        $this->descriptors      = $this->getDescriptors();
        $this->processStartTime = \microtime(true);

        $this->process = \proc_open(
            $this->command,
            $this->descriptors,
            $this->pipes,
            $this->cwd,
            $this->env,
            $this->otherOptions
        );
        $this->setInput();

        // @codeCoverageIgnoreStart
        if (!\is_resource($this->process)) {
            throw new RuntimeException('Bad program could not be started.');
        }
        // @codeCoverageIgnoreEnd

        $this->state = self::STATE_STARTED;

        $this->updateProcessStatus();

        if ($this->async = $async) {
            $this->setOutputStreamNonBlocking();
        } else {
            $this->wait();
        }

        return $this;
    }

    private function setOutputStreamNonBlocking(): bool
    {
        return \stream_set_blocking($this->pipes[self::STDOUT_DESCRIPTOR_KEY], false);
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getOutput(): string
    {
        return \stream_get_contents($this->pipes[self::STDOUT_DESCRIPTOR_KEY]);
    }

    public function getErrorOutput(): string
    {
        return \stream_get_contents($this->pipes[self::STDERR_DESCRIPTOR_KEY]);
    }

    public function getExitCode()
    {
        $this->updateProcessStatus();

        return $this->exitCode;
    }

    public function isRunning(): bool
    {
        if (self::STATE_STARTED !== $this->state) {
            return false;
        }

        $this->updateProcessStatus();

        return $this->processStatus['running'];
    }

    public function getProcessId()
    {
        return $this->isRunning() ? $this->processStatus['pid'] : null;
    }

    public function stop()
    {
        $this->closePipes();

        if (\is_resource($this->process)) {
            \proc_close($this->process);
        }

        $this->state = self::STATE_CLOSED;

        $this->exitCode = $this->processStatus['exitcode'];

        return $this->exitCode;
    }

    public function kill()
    {
        if (\is_resource($this->process)) {
            \proc_terminate($this->process);
        }

        $this->state = self::STATE_TERMINATED;
    }

    public function __destruct()
    {
        // If async (run in background) => we don't care if it ever closes
        // Otherwise, waited already till it ends itself or timeout occurs, in which case kill it
    }
}
