<?php

/*
 * This file is part of the PHP-CLI package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

namespace Ahc\Cli\Test\Helper;

use Ahc\Cli\Helper\OutputHelper;
use Ahc\Cli\Input\Argument;
use Ahc\Cli\Input\Command;
use Ahc\Cli\Input\Option;
use Ahc\Cli\Output\Color;
use Ahc\Cli\Output\Writer;
use PHPUnit\Framework\TestCase;
use function file;
use function implode;
use function str_replace;
use const FILE_IGNORE_NEW_LINES;

class OutputHelperTest extends TestCase
{
    protected static $ou = __DIR__ . '/output';

    public function setUp(): void
    {
        file_put_contents(static::$ou, '', LOCK_EX);
    }

    public static function tearDownAfterClass(): void
    {
        unlink(static::$ou);
    }

    public function test_show_arguments()
    {
        $this->newHelper()->showArgumentsHelp([
            new Argument('<path>', 'The path'),
            new Argument('[config:defaultConfig]'),
        ], 'Arg Header', 'Arg Footer');

        $this->assertSame([
            'Arg Header',
            '',
            'Arguments:',
            '  [config]    ',
            '  <path>      The path',
            '',
            'Arg Footer',
        ], $this->output());
    }

    public function test_show_options()
    {
        $this->newHelper()->showOptionsHelp([
            new Option('-h --help', 'Show help'),
            new Option('-n|--full-name <name>', 'Full name'),
        ], 'Opt Header', 'Opt Footer');

        $this->assertSame([
            'Opt Header',
            '',
            'Options:',
            '  <-n|--full-name>    Full name',
            '  [-h|--help]         Show help',
            '',
            'Opt Footer',
        ], $this->output());
    }

    public function test_show_commands()
    {
        $this->newHelper()->showCommandsHelp([
            new Command('rm', 'Remove file or folder'),
            new Command('mkdir', 'Make a folder'),
            new Command('group:rm', 'Remove file or folder'),
            new Command('group:mkdir', 'Make a folder'),
        ], 'Cmd Header', 'Cmd Footer');

        $this->assertSame([
            'Cmd Header',
            '',
            'Commands:',
            'group',
            '  group:mkdir    Make a folder',
            '  group:rm       Remove file or folder',
            '*',
            '  mkdir          Make a folder',
            '  rm             Remove file or folder',
            '',
            'Cmd Footer',
        ], $this->output());
    }

    public function test_empty()
    {
        $this->newHelper()->showCommandsHelp([], 'Header');

        $this->assertSame([
            'Header',
            '',
            'Commands:',
            '  (n/a)',
        ], $this->output());
    }

    public function test_show_usage()
    {
        $argv0 = $_SERVER['argv'][0];

        $_SERVER['argv'][0] = 'test';

        $this->newHelper()->showUsage(implode('', [
            '<bold>  $0</end> <comment>-a apple</end> ## apple only<eol>',
            '<bold>  $0</end> <comment>-a apple -b ball</end> ## apple ball<eol>',
            'loooooooooooong text ## something<eol>',
            'no shell comments<eol>',
            'short text ## something else<eol>',
        ]));

        $this->assertEquals([
            '',
            'Usage Examples:',
            '  test -a apple          # apple only',
            '  test -a apple -b ball  # apple ball',
            'loooooooooooong text     # something',
            'no shell comments',
            'short text               # something else',
            '',
        ], $this->output());

        $_SERVER['argv'][0] = $argv0;
    }

    public function newHelper()
    {
        return new OutputHelper(new Writer(static::$ou, new class extends Color {
            protected string $format = ':txt:';
        }));
    }

    protected function output(): array
    {
        return str_replace("\033[0m", '', file(static::$ou, FILE_IGNORE_NEW_LINES));
    }
}
