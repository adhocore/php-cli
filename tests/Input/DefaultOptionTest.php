<?php

/*
 * This file is part of the PHP-CLI package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

namespace Ahc\Cli\Test\Input;

use Ahc\Cli\Input\Command;
use Ahc\Cli\Test\CliTestCase;

class DefaultOptionTest extends CliTestCase
{
    public function test_version()
    {
        $p = $this->newCommand('v1.0.1')->parse(['php', '--version']);
        $this->assertStringContainsString('v1.0.1', $this->buffer(), 'Long');
    }

    public function test_V()
    {
        $p = $this->newCommand('v2.0.1')->parse(['php', '-V']);
        $this->assertStringContainsString('v2.0.1', $this->buffer(), 'Short');
    }

    public function test_help()
    {
        $p = $this->newCommand()
            ->argument('[arg]', 'Some desc')
            ->option('-o --option')
            ->usage('cmdname --option opt <arg>')
            ->parse(['php', '--help']);

        $this->assertStringContainsString('cmdname', $buffer = $this->buffer());
        $this->assertStringContainsString('Usage Examples:', $buffer);
        $this->assertStringContainsString('--option opt <arg>', $buffer);
        $this->assertStringContainsString('[arg]', $buffer);
        $this->assertStringContainsString('Some desc', $buffer);
        $this->assertStringContainsString('[-o|--option]', $buffer);
    }

    public function test_help_unknown()
    {
        $p = $this->newCommand()->arguments('[apple]')->parse(['php', '--unknown', '1']);
        $this->assertStringContainsString('[apple]', $this->buffer(), 'Show help');
    }

    public function test_verbosity()
    {
        $p = $this->newCommand()->parse(['php', '-vv', '-vvv']);

        $this->assertSame(5, $p->verbosity);
    }

    protected function newCommand(string $version = '0.0.1', string $desc = '', bool $allowUnknown = false)
    {
        $p = new Command('cmdname', $desc, $allowUnknown);

        return $p->version($version)->onExit(function () {
            return false;
        });
    }
}
