<?php

/*
 * This file is part of the PHP-CLI package.
 * <https://github.com/adhocore/php-cli>
 *
 * (c) Sushil Gupta <desushil@gmail.com>
 * <https://github.com/sushilgupta>
 *
 * Licensed under MIT license.
 */

namespace Ahc\Cli\Helper;

use Ahc\Cli\Exception\RuntimeException;

/*
 * A thin proc_open wrapper to execute shell commands.
 * @author Sushil Gupta <desushil@gmail.com>
 * @license MIT
 */
class Shell
{
    const STDIN_DESCRIPTOR_KEY  = 0;
    const STDOUT_DESCRIPTOR_KEY = 1;
    const STDERR_DESCRIPTOR_KEY = 2;

    /** @var string Command to be executed */
    protected $command;

    /** @var array Descriptor to be passed for proc_open */
    protected $descriptors;

    /** @var int Exit code of the process once it has been terminated */
    protected $exitCode;

    /** @var string Input for stdin */
    protected $input;

    /** @var array Pointers to stdin, stdout & stderr */
    protected $pipes;

    /** @var resource The actual process resource returned from proc_open */
    protected $process;

    /** @var string Status of the process as returned from proc_get_status */
    protected $status;

    public function __construct(string $command, string $input = null)
    {
        if (!\function_exists('proc_open')) {
            throw new RuntimeException('Required proc_open could not be found in your PHP setup');
        }

        $this->command  = $command;
        $this->input    = $input;
        $this->status   = null;
        $this->exitCode = null;
    }

    protected function getDescriptors()
    {
        $out = '\\' === \DIRECTORY_SEPARATOR ? ['file', 'NUL', 'w'] : ['pipe', 'w'];

        return [
            self::STDIN_DESCRIPTOR_KEY  => ['pipe', 'r'],
            self::STDOUT_DESCRIPTOR_KEY => $out,
            self::STDERR_DESCRIPTOR_KEY => $out,
        ];
    }

    protected function setInput()
    {
        \fwrite($this->pipes[self::STDIN_DESCRIPTOR_KEY], $this->input);
    }

    protected function updateStatus()
    {
        $this->status = \proc_get_status($this->process);

        return $this->status;
    }

    protected function closePipes()
    {
        \fclose($this->pipes[self::STDIN_DESCRIPTOR_KEY]);
        \fclose($this->pipes[self::STDOUT_DESCRIPTOR_KEY]);
        \fclose($this->pipes[self::STDERR_DESCRIPTOR_KEY]);
    }

    public function execute()
    {
        if ($this->isRunning()) {
            throw new RuntimeException('Process is already running');
        }

        $this->descriptors = $this->getDescriptors();

        $this->process = proc_open($this->command, $this->descriptors, $this->pipes);

        if (!\is_resource($this->process)) {
            throw new RuntimeException('Bad program could not be started.');
        }

        $this->setInput();
        $this->updateStatus();
    }

    public function getStatus()
    {
        $this->updateStatus();

        return $this->status;
    }

    public function getOutput()
    {
        return \stream_get_contents($this->pipes[self::STDOUT_DESCRIPTOR_KEY]);
    }

    public function getErrorOutput()
    {
        return \stream_get_contents($this->pipes[self::STDERR_DESCRIPTOR_KEY]);
    }

    public function getExitCode()
    {
        return $this->exitCode;
    }

    public function isRunning()
    {
        return $this->status['running'];
    }

    public function getProcessId()
    {
        return $this->isRunning() ? $this->status['pid'] : null;
    }

    public function stop()
    {
        $this->closePipes();

        if (\is_resource($this->process)) {
            \proc_close($this->process);
        }

        $this->exitCode = $this->status['exitcode'];

        return $this->exitCode;
    }

    public function kill()
    {
        return \proc_terminate($this->process);
    }

    public function __destruct()
    {
        $this->stop();
    }
}