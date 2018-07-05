<?php

namespace Ahc\Cli\Test;

use Ahc\Cli\Application;
use Ahc\Cli\Input\Command;
use Ahc\Cli\Output\Writer;
use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{
    public function test_new()
    {
        $a = $this->newApp('project', '1.0.1');

        $this->assertInstanceOf(Command::class, $c = $a->commandFor([]));
        $this->assertSame('__default__', $c->name());
        $this->assertSame('Default command', $c->desc());

        $this->assertSame('project', $a->name());
        $this->assertSame('1.0.1', $a->version());
    }

    public function test_commands()
    {
        $a = $this->newApp('project', '1.0.1');
        $this->assertEmpty($a->commands());

        $a->command('new', 'Create new project', 'n');
        $this->assertNotEmpty($a->commands());
        $this->assertCount(1, $a->commands());

        $this->assertSame('new', $a->commandFor(['project', 'new'])->name());
        $this->assertSame('new', $a->commandFor(['project', 'n'])->name());
        $this->assertSame('__default__', $a->commandFor(['project', 'nn'])->name());
    }

    public function test_command_dup_name()
    {
        $a = $this->newApp('project', '1.0.1');

        $a->command('clean', 'Cleanup project status');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Command "clean" already added');
        $a->command('clean', 'Cleanup project status', 'c');
    }

    public function test_command_dup_alias()
    {
        $a = $this->newApp('project', '1.0.1');

        $a->command('clean', 'Cleanup project status', 'c');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Command "c" already added');
        $a->command('c', 'Cleanup project status', 'd');
    }

    public function test_parse()
    {
        $a = $this->newApp('git');

        $a->command('add', 'stage change', 'a')->arguments('<files...>');
        $c = $a->parse(['git', 'add', 'file1', 'file2']);

        $this->assertSame(['file1', 'file2'], $c->files);
        $this->assertSame(['git', 'add', 'file1', 'file2'], $a->argv());
    }

    public function test_help()
    {
        $ou = __DIR__ . '/output';
        $a  = $this->newApp('git', '0.0.2');

        $a->command('add', 'stage change', 'a')->arguments('<files...>');
        $a->showHelp(new Writer($ou));

        $out = file_get_contents($ou);

        $this->assertContains('git, version 0.0.2', $out);
        $this->assertContains('add', $out);
        $this->assertContains('stage change', $out);

        unlink($ou);
    }

    public function test_action()
    {
        ($a = $this->newApp('git', '0.0.2'))
            ->command('add', 'stage change', 'a')
                ->arguments('<files...>')->action(function ($files) {
                    echo 'Add ' . implode(' and ', $files);
                })->tap($a)
            ->command('config', 'list config', 'c')
                ->option('-l --list <scope>', 'list config')->action(function ($list) {
                    echo "Config $list: user.email=user+100@gmail.com";
                });

        ob_start();
        $a->parse(['git', 'add', 'a.php', 'b.php']);
        $buffer = ob_get_clean();
        $this->assertSame('Add a.php and b.php', $buffer);

        ob_start();
        $a->parse(['git', 'c', '--list', 'global']);
        $buffer = ob_get_clean();
        $this->assertSame('Config global: user.email=user+100@gmail.com', $buffer);
    }

    protected function newApp(string $name, string $version = '')
    {
        return new Application($name, $version ?: '0.0.1', function () {
            return false;
        });
    }
}
