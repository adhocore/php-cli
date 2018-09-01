<?php
    /*
     * Shell wrapper for package adhocore/php-cli.
     * @author Sushil Gupta <desushil@gmail.com>
     * @license MIT
     */

    namespace Ahc\Cli\Shell;

    use Ahc\Cli\Exception\RuntimeException;

    class Shell {

        const STDIN_DESCRIPTOR_KEY = 0;
        const STDOUT_DESCRIPTOR_KEY = 1;
        const STDERR_DESCRIPTOR_KEY = 2;

        protected $command;
        protected $cwd;
        protected $descriptors;
        protected $env;
        protected $error;
        protected $input;
        protected $output;
        protected $pipes;
        protected $process;
        protected $startTime;
        protected $status;
        protected $timeout;

        public function __construct(string $command, string $cwd = null, $input = null, $env = null, $timeout = 60)
        {
            if (!\function_exists('proc_open')) {
                throw new RuntimeException('Required proc_open could not be found in your PHP setup');
            }

            $this->command = $command;
            $this->cwd = $cwd;
            $this->env = $env;
            $this->input = $input;
            $this->timeout = $timeout;
        }

        public function execute()
        {
            $this->start();
            $this->wait();
        }

        public function start()
        {
            if ($this->isRunning()) {
                throw new RuntimeException('Process is already running');
            }

            $this->descriptors = $this->getDescriptors();

            $this->process = proc_open($this->command, $this->descriptors, $this->pipes, $this->cwd, $this->env);

            if (!\is_resource($this->process)) {
                throw new RuntimeException('Bad program could not be started.');
            }

            $this->setInput();

            $this->startTime = microtime(true);
            $this->status = $this->updateStatus();
        }

        public function getDescriptors()
        {
            return array(
                self::STDIN_DESCRIPTOR_KEY => array("pipe", "r"),
                self::STDOUT_DESCRIPTOR_KEY => array("pipe", "w"),
                self::STDERR_DESCRIPTOR_KEY => array("pipe", "r")
            );
        }

        public function setInput()
        {
            fwrite($this->pipes[0], $this->input);
        }

        public function getOutput()
        {
            $this->output = stream_get_contents($this->pipes[1]);

            return $this->output;
        }

        public function getErrorOutput()
        {
            $this->error = stream_get_contents($this->pipes[2]);

            return $this->error;
        }

        public function checkTimeout()
        {
            if ($this->timeout && $this->timeout < microtime(true) - $this->startTime) {
                $this->stop();
            }

            return $this->status;
        }

        public function updateStatus()
        {
            $this->status = proc_get_status($this->process);

            if (!$this->isRunning()) {
                $this->stop();
            }

            return $this->status;
        }

        public function wait()
        {
            while ($this->isRunning()) {
                usleep(1000);
                $this->checkTimeout();
                $this->updateStatus();
            }

            return $this->status;
        }

        public function isRunning()
        {
            return $this->status['running'];
        }

        public function stop()
        {
            fclose($this->pipes[0]);
            fclose($this->pipes[1]);
            fclose($this->pipes[2]);
            proc_close($this->process);

            return $this->status;
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