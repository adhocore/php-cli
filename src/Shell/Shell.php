<?php
    /*
     * Shell wrapper for package adhocore/php-cli.
     * @author Sushil Gupta <desushil@gmail.com>
     * @license MIT
     */

    namespace Ahc\Cli\Shell;

    use Ahc\Cli\Exception\RuntimeException;

    class Shell {

        protected $command;
        protected $cwd;
        protected $descriptors;
        protected $env;
        protected $pipes;
        protected $process;
        protected $status;
        protected $stdin;
        protected $stdout;
        protected $stderr;
        protected $timeout;

        public function __construct(string $command, string $cwd = null, $stdin = null, $env = null, $timeout = 60)
        {
            if (!\function_exists('proc_open')) {
                throw new RuntimeException('Required proc_open could not be found in your PHP setup');
            }

            $this->command = $command;
            $this->cwd = $cwd;
            $this->descriptors = $this->getDescriptors();
            $this->env = $env;
            $this->stdin = $stdin;
            $this->timeout = $timeout;
        }

        public function execute()
        {
            $descriptors = $this->getDescriptors();

            $this->process = proc_open($this->command, $descriptors, $this->pipes, $this->cwd, $this->env);

            if (!\is_resource($this->process)) {
                throw new RuntimeException('Bad program could not be started.');
            }
        }

        public function getDescriptors()
        {
            return array(
                0 => array("pipe", "r"),
                1 => array("pipe", "w"),
                2 => array("file", "/tmp/error-output.txt", "a")
            );
        }

        public function setInput()
        {
            fwrite($this->pipes[0], $this->stdin);
        }

        public function getOutput()
        {
            $this->stdout = stream_get_contents($this->pipes[1]);


            return $this->stdout;
        }

        public function getStatus()
        {
            $this->status = proc_get_status($this->process);
            return $this->status;
        }

        public function getErrorOutput()
        {
            $this->stderr = stream_get_contents($this->pipes[2]);
            return $this->stderr;
        }

        public function stop()
        {
            fclose($this->pipes[0]);
            fclose($this->pipes[1]);
            fclose($this->pipes[2]);
            return proc_close($this->process);
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