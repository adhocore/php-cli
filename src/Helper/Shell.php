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

use function fclose;
use function function_exists;
use function fwrite;
use function is_resource;
use function microtime;
use function proc_close;
use function proc_get_status;
use function proc_open;
use function proc_terminate;
use function stream_get_contents;
use function stream_set_blocking;

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

    const DEFAULT_STDIN_WIN = ['pipe', 'r'];
    const DEFAULT_STDIN_NIX = ['pipe', 'r'];

    const DEFAULT_STDOUT_WIN = ['pipe', 'w'];
    const DEFAULT_STDOUT_NIX = ['pipe', 'w'];

    const DEFAULT_STDERR_WIN = ['pipe', 'w'];
    const DEFAULT_STDERR_NIX = ['pipe', 'w'];

    /** @var bool Whether to wait for the process to finish or return instantly */
    protected bool $async = false;

    /** @var string Current working directory */
    protected ?string $cwd = null;

    /** @var array Descriptor to be passed for proc_open */
    protected array $descriptors;

    /** @var array An array of environment variables */
    protected ?array $env = null;

    /** @var int Exit code of the process once it has been terminated */
    protected ?int $exitCode = null;

    /** @var array Other options to be passed for proc_open */
    protected array $otherOptions = [];

    /** @var array Pointers to stdin, stdout & stderr */
    protected array $pipes = [];

    /** @var resource The actual process resource returned from proc_open */
    protected $process = null;

    /** @var float Process starting time in unix timestamp */
    protected float $processStartTime = 0;

    /** @var array Status of the process as returned from proc_get_status */
    protected ?array $processStatus = null;

    /** @var float Default timeout for the process in seconds with microseconds */
    protected ?float $processTimeout = null;

    /** @var string Current state of the shell execution, set from this class, NOT for proc_get_status */
    protected string $state = self::STATE_READY;

    /**
     * @param string $command Command to be executed
     * @param string $input   Input for stdin
     */
    public function __construct(protected string $command, protected ?string $input = null)
    {
        // @codeCoverageIgnoreStart
        if (!function_exists('proc_open')) {
            throw new RuntimeException('Required proc_open could not be found in your PHP setup.');
        }
        // @codeCoverageIgnoreEnd

        $this->command = $command;
        $this->input   = $input;
    }

    protected function prepareDescriptors(?array $stdin = null, ?array $stdout = null, ?array $stderr = null): array
    {
        $win = Terminal::isWindows();
        if (!$stdin) {
            $stdin = $win ? self::DEFAULT_STDIN_WIN : self::DEFAULT_STDIN_NIX;
        }
        if (!$stdout) {
            $stdout = $win ? self::DEFAULT_STDOUT_WIN : self::DEFAULT_STDOUT_NIX;
        }
        if (!$stderr) {
            $stderr = $win ? self::DEFAULT_STDERR_WIN : self::DEFAULT_STDERR_NIX;
        }

        return [
            self::STDIN_DESCRIPTOR_KEY  => $stdin,
            self::STDOUT_DESCRIPTOR_KEY => $stdout,
            self::STDERR_DESCRIPTOR_KEY => $stderr,
        ];
    }

    protected function setInput(): void
    {
        //Make sure the pipe is a stream resource before writing to it to avoid a warning
        if (is_resource($this->pipes[self::STDIN_DESCRIPTOR_KEY])) {
            fwrite($this->pipes[self::STDIN_DESCRIPTOR_KEY], $this->input ?? '');
        }
    }

    protected function updateProcessStatus(): void
    {
        if ($this->state === self::STATE_STARTED) {
            $this->processStatus = proc_get_status($this->process);

            if ($this->processStatus['running'] === false && $this->exitCode === null) {
                $this->exitCode = $this->processStatus['exitcode'];
            }
        }
    }

    protected function closePipes(): void
    {
        //Make sure the pipe are a stream resource before closing them to avoid a warning
        if (is_resource($this->pipes[self::STDIN_DESCRIPTOR_KEY])) {
            fclose($this->pipes[self::STDIN_DESCRIPTOR_KEY]);
        }
        if (is_resource($this->pipes[self::STDOUT_DESCRIPTOR_KEY])) {
            fclose($this->pipes[self::STDOUT_DESCRIPTOR_KEY]);
        }
        if (is_resource($this->pipes[self::STDERR_DESCRIPTOR_KEY])) {
            fclose($this->pipes[self::STDERR_DESCRIPTOR_KEY]);
        }
    }

    protected function wait(): ?int
    {
        while ($this->isRunning()) {
            usleep(5000);
            $this->checkTimeout();
        }

        return $this->exitCode;
    }

    protected function checkTimeout(): void
    {
        if ($this->processTimeout === null) {
            return;
        }

        $executionDuration = microtime(true) - $this->processStartTime;

        if ($executionDuration > $this->processTimeout) {
            $this->kill();

            throw new RuntimeException('Timeout occurred, process terminated.');
        }
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

    public function setOptions(
        ?string $cwd = null,
        ?array $env = null,
        ?float $timeout = null,
        array $otherOptions = []
    ): self {
        $this->cwd            = $cwd;
        $this->env            = $env;
        $this->processTimeout = $timeout;
        $this->otherOptions   = $otherOptions;

        return $this;
    }

    /**
     * execute
     * Execute the command with optional stdin, stdout and stderr which override the defaults
     * If async is set to true, the process will be executed in the background.
     *
     * @param bool   $async  - default false
     * @param ?array $stdin  - default null (loads default descriptor)
     * @param ?array $stdout - default null (loads default descriptor)
     * @param ?array $stderr - default null (loads default descriptor)
     *
     * @return self
     */
    public function execute(bool $async = false, ?array $stdin = null, ?array $stdout = null, ?array $stderr = null): self
    {
        if ($this->isRunning()) {
            throw new RuntimeException('Process is already running.');
        }

        $this->descriptors      = $this->prepareDescriptors($stdin, $stdout, $stderr);
        $this->processStartTime = microtime(true);

        $this->process = proc_open(
            $this->command,
            $this->descriptors,
            $this->pipes,
            $this->cwd,
            $this->env,
            $this->otherOptions
        );
        $this->setInput();

        // @codeCoverageIgnoreStart
        if (!is_resource($this->process)) {
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
        $isRes = is_resource($this->pipes[self::STDOUT_DESCRIPTOR_KEY]);

        return $isRes ? stream_set_blocking($this->pipes[self::STDOUT_DESCRIPTOR_KEY], false) : false;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getOutput(): string
    {
        $isRes = is_resource($this->pipes[self::STDOUT_DESCRIPTOR_KEY]);

        return $isRes ? stream_get_contents($this->pipes[self::STDOUT_DESCRIPTOR_KEY]) : '';
    }

    public function getErrorOutput(): string
    {
        $isRes = is_resource($this->pipes[self::STDERR_DESCRIPTOR_KEY]);

        return $isRes ? stream_get_contents($this->pipes[self::STDERR_DESCRIPTOR_KEY]) : '';
    }

    public function getExitCode(): ?int
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

    public function getProcessId(): ?int
    {
        return $this->isRunning() ? $this->processStatus['pid'] : null;
    }

    public function stop(): ?int
    {
        $this->closePipes();

        if (is_resource($this->process)) {
            proc_close($this->process);
        }

        $this->state = self::STATE_CLOSED;

        $this->exitCode = $this->processStatus['exitcode'];

        return $this->exitCode;
    }

    public function kill(): void
    {
        if (is_resource($this->process)) {
            proc_terminate($this->process);
        }

        $this->state = self::STATE_TERMINATED;
    }

    public function __destruct()
    {
        // If async (run in background) => we don't care if it ever closes
        // Otherwise, waited already till it ends itself or timeout occurs, in which case kill it
    }
}
