<?php

namespace Ahc\Cli;

use Ahc\Cli\Helper\OutputHelper;
use Ahc\Cli\Input\Command;

/**
 * A command application.
 *
 * @codeCoverageIgnore
 */
class Application
{
    /** @var Command[] */
    protected $commands = [];

    /** @var array Raw argv sent to parse() */
    protected $argv = [];

    protected $aliases = [];

    protected $name;

    /** @var string App version */
    protected $version = '0.0.1';

    public function __construct(string $name, string $version, callable $onExit = null)
    {
        $this->name    = $name;
        $this->version = $version;

        $this->command = (new Command('__default__', 'Default command', true))->version($this->version);
        $this->onExit  = $onExit ?? function () {
            exit(0);
        };
    }

    public function name(): string
    {
        return $this->name;
    }

    public function version(): string
    {
        return $this->version;
    }

    public function commands(): array
    {
        return $this->commands;
    }

    public function argv(): array
    {
        return $this->argv;
    }

    public function command(string $name, string $desc = '', bool $allowUnknown = false, string $alias = ''): Command
    {
        if (isset($this->commands[$name])) {
            throw new \InvalidArgumentException(\sprintf('Command "%s" already added', $name));
        }

        if ($alias) {
            $this->aliases[$alias] = $name;
        }

        return $this->commands[$name] = (new Command($name, $desc, $allowUnknown))->version($this->version)->onExit($this->onExit);
    }

    public function commandFor(array $argv): Command
    {
        $argv += [null, null, null];

        return
             // cmd
            $this->commands[$argv[1]]
            // cmd:subcmd
            ?? $this->commands[$argv[1] . ':' . $argv[2]]
            // cmd alias
            ?? $this->commands[$this->aliases[$argv[1]] ?? null]
            // default.
            ?? $this->command;
    }

    public function parse(array $argv)
    {
        $this->command = $this->commandFor($argv)->parse($argv);

        $this->doAction($this->command);

        return $this->command;
    }

    public function showHelp()
    {
        $header = "{$this->name}, version {$this->version}";
        $footer = 'Run `<command> --help` for specific help';

        (new OutputHelper)->showCommandsHelp($this->commands, $header, $footer);

        $exit = $this->onExit;

        $exit();
    }

    protected function doAction(Command $command)
    {
        if (null === $action = $command->action()) {
            return;
        }

        $params = [];
        $values = $command->values();
        foreach ((new \ReflectionFunction($action))->getParameters() as $param) {
            $params[] = $values[$param->getName()] ?? null;
        }

        return $action(...$params);
    }
}
