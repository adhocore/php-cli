<?php

namespace Ahc\Cli\Test\Helper;

use Ahc\Cli\Helper\OutputHelper;
use Ahc\Cli\Input\Argument;
use Ahc\Cli\Input\ArgvParser as Command;
use Ahc\Cli\Input\Option;
use Ahc\Cli\Output\Color;
use Ahc\Cli\Output\Writer;
use PHPUnit\Framework\TestCase;

class OutputHelperTest extends TestCase
{
    protected static $ou = __DIR__ . '/output';

    public function setUp()
    {
        file_put_contents(static::$ou, '');
    }

    public static function tearDownAfterClass()
    {
        unlink(static::$ou);
    }

    public function test_show_arguments()
    {
        $this->newHelper()->showArgumentsHelp([
            new Argument('<path>'),
            new Argument('[config:defaultConfig]'),
        ], 'Arg Header', 'Arg Footer');

        $this->assertSame([
            'Arg Header',
            '',
            'Arguments:',
            '  [config]  ',
            '  <path>    ',
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
            '  <-n|--full-name>  Full name',
            '  [-h|--help]       Show help',
            '',
            'Opt Footer',
        ], $this->output());
    }

    public function test_show_commands()
    {
        $this->newHelper()->showCommandsHelp([
            new Command('rm', 'Remove file or folder'),
            new Command('mkdir', 'Make a folder'),
        ], 'Cmd Header', 'Cmd Footer');

        $this->assertSame([
            'Cmd Header',
            '',
            'Commands:',
            '  mkdir  Make a folder',
            '  rm     Remove file or folder',
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

    public function newHelper()
    {
        return new OutputHelper(new Writer(static::$ou, new class extends Color {
            protected $format = ':text:';
        }));
    }

    protected function output(): array
    {
        return file(static::$ou, FILE_IGNORE_NEW_LINES);
    }
}
