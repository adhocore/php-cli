<?php

namespace Ahc\Cli;

use Ahc\Cli\Helper\OutputHelper;
use Ahc\Cli\Input\Command;
use Ahc\Cli\Output\Writer;

/**
 * A cli application.
 *
 * @author  Jitendra Adhikari <jiten.adhikary@gmail.com>
 * @license MIT
 *
 * @link    https://github.com/adhocore/cli
 */
class Application
{
    /** @var Command[] */
    protected $commands = [];

    /** @var array Raw argv sent to parse() */
    protected $argv = [];

    /** @var array Command aliases [alias => cmd] */
    protected $aliases = [];

    /** @var string */
    protected $name;

    /** @var string App version */
    protected $version = '0.0.1';

    public function __construct(string $name, string $version = '', callable $onExit = null)
    {
        $this->name    = $name;
        $this->version = $version;

        // @codeCoverageIgnoreStart
        $this->onExit = $onExit ?? function () {
             exit(0);
         };
        // @codeCoverageIgnoreEnd

        $this->command = $this->command('__default__', 'Default command', true);

        unset($this->commands['__default__']);

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
        if ($this->commands[$name] ?? $this->aliases[$name] ?? $this->commands[$alias] ?? $this->aliases[$alias] ?? null) {
            throw new \InvalidArgumentException(\sprintf('Command "%s" already added', $name));
        }

        if ($alias) {
            $this->aliases[$alias] = $name;
        }

        $command = (new Command($name, $desc, $allowUnknown, $this))->version($this->version)->onExit($this->onExit);

        return $this->commands[$name] = $command;
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
        $this->argv = $argv;

        $command = $this->commandFor($argv);
        $aliases = $this->aliasesFor($command);

        // Eat the cmd name!
        foreach ($argv as $i => $arg) {
            if (\in_array($arg, $aliases)) {
                unset($argv[$i]);
            }
        }

        $command->parse($argv);

        $this->doAction($command);

        return $command;

    }

    protected function aliasesFor(Command $command)
    {
        $aliases = [$name = $command->name()];

        foreach ($this->aliases as $alias => $command) {
            if ($command === $name) {
                $aliases[] = $alias;
            }
        }

        return $aliases;
    }

     public function showHelp(Writer $writer = null)
     {
        $header = "{$this->name}, version {$this->version}";
        $footer = 'Run `<command> --help` for specific help';

        (new OutputHelper($writer))->showCommandsHelp($this->commands, $header, $footer);

        return ($this->onExit)();
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
