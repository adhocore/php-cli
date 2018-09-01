<?php

/*
 * Shell wrapper for package adhocore/php-cli.
 * @author Sushil Gupta <desushil@gmail.com>
 * @license MIT
 */

namespace Ahc\Cli\Helper;

use Ahc\Cli\Exception\RuntimeException;

class Shell
{
    const STDIN_DESCRIPTOR_KEY  = 0;
    const STDOUT_DESCRIPTOR_KEY = 1;
    const STDERR_DESCRIPTOR_KEY = 2;

    protected $command;
    protected $descriptors;
    protected $input;
    protected $pipes;
    protected $process;
    protected $status;

    public function __construct(string $command, string $input = null)
    {
        if (!\function_exists('proc_open')) {
            throw new RuntimeException('Required proc_open could not be found in your PHP setup');
        }

        $this->command = $command;
        $this->input   = $input;
        $this->status  = null;
    }

    private function getDescriptors()
    {
        if ('\\' === \DIRECTORY_SEPARATOR) {
            return [
                self::STDIN_DESCRIPTOR_KEY  => ['pipe', 'r'],
                self::STDOUT_DESCRIPTOR_KEY => ['file', 'NUL', 'w'],
                self::STDERR_DESCRIPTOR_KEY => ['file', 'NUL', 'w'],
            ];
        } else {
            return [
                self::STDIN_DESCRIPTOR_KEY  => ['pipe', 'r'],
                self::STDOUT_DESCRIPTOR_KEY => ['pipe', 'w'],
                self::STDERR_DESCRIPTOR_KEY => ['pipe', 'w'],
            ];
        }
    }

    private function setInput()
    {
        fwrite($this->pipes[self::STDIN_DESCRIPTOR_KEY], $this->input);
    }

    private function updateStatus()
    {
        $this->status = proc_get_status($this->process);

        return $this->status;
    }

    private function closePipes()
    {
        fclose($this->pipes[self::STDIN_DESCRIPTOR_KEY]);
        fclose($this->pipes[self::STDOUT_DESCRIPTOR_KEY]);
        fclose($this->pipes[self::STDERR_DESCRIPTOR_KEY]);
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

    public function getOutput()
    {
        return stream_get_contents($this->pipes[self::STDOUT_DESCRIPTOR_KEY]);
    }

    public function getErrorOutput()
    {
        return stream_get_contents($this->pipes[self::STDERR_DESCRIPTOR_KEY]);
    }

    public function getExitCode()
    {
        return $this->status ? $this->status['exitcode'] : -1;
    }

    public function isRunning()
    {
        return $this->status ? $this->status['running'] : false;
    }

    public function getProcessId()
    {
        return $this->isRunning() ? $this->status['pid'] : null;
    }

    public function stop()
    {
        if (!$this->isRunning()) {
            return $this->getExitCode();
        }

        $this->closePipes();

        if (\is_resource($this->process)) {
            proc_close($this->process);
        }

        return $this->getExitCode();
    }

    public function kill()
    {
        return proc_terminate($this->process);
    }

    public function __destruct()
    {
        $this->stop();
    }
}