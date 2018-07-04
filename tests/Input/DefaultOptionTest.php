<?php

namespace Ahc\Cli\Test\Input;

use Ahc\Cli\Input\ArgvParser;
use Ahc\Cli\Test\CliTestCase;

class DefaultOptionTest extends CliTestCase
{
    public function test_version()
    {
        $p = $this->newParser('v1.0.1')->parse(['php', '--version']);
        $this->assertContains('v1.0.1', $this->buffer(), 'Long');
    }

    public function test_V()
    {
        $p = $this->newParser('v2.0.1')->parse(['php', '-V']);
        $this->assertContains('v2.0.1', $this->buffer(), 'Short');
    }

    public function test_help()
    {
        $p = $this->newParser()
            ->arguments('[arg]')
            ->option('-o --option')
            ->parse(['php', '--help']);

        $this->assertContains('ArgvParserTest', $buffer = $this->buffer());
        $this->assertContains('[arg]', $buffer);
        $this->assertContains('[-o|--option]', $buffer);
    }

    public function test_help_unknown()
    {
        $p = $this->newParser()->arguments('[apple]')->parse(['php', '--unknown', '1']);
        $this->assertContains('[apple]', $this->buffer(), 'Show help');
    }

    public function test_verbosity()
    {
        $p = $this->newParser()->parse(['php', '-vv', '-vvv']);

        $this->assertSame(5, $p->verbosity);
    }

    protected function newParser(string $version = '0.0.1', string $desc = '', bool $allowUnknown = false)
    {
        $p = new ArgvParser('ArgvParserTest', $desc, $allowUnknown);

        return $p->version($version)->onExit(function () {
            return false;
        });
    }
}
