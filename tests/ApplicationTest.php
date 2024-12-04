<?php

/*
 * This file is part of the PHP-CLI package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

namespace Ahc\Cli\Test;

use Ahc\Cli\Application;
use Ahc\Cli\Input\Command;
use Ahc\Cli\IO\Interactor;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Throwable;

use function Ahc\Cli\t;

class ApplicationTest extends TestCase
{
    protected static $in = __DIR__ . '/input.test';
    protected static $ou = __DIR__ . '/output.test';
    private bool $actionCalled;

    public function setUp(): void
    {
        file_put_contents(static::$in, '', LOCK_EX);
        file_put_contents(static::$ou, '', LOCK_EX);
    }

    public function tearDown(): void
    {
        // Make sure we clean up after ourselves:
        if (file_exists(static::$in)) {
            unlink(static::$in);
        }
        if (file_exists(static::$ou)) {
            unlink(static::$ou);
        }
    }

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

    public function test_groups()
    {
        $a = $this->newApp('project', '1.0.0');

        $a->group('Configuration', function ($a) {
            $a->command('config:set');
            $a->command('config:get');
            $a->command('config:del');
        });

        $ct = 0;
        foreach ($a->commands() as $cmd) {
            if (in_array($cmd->name(), ['config:set', 'config:get', 'config:del'], true)) {
                $ct++;
                $this->assertSame('Configuration', $cmd->group());
            }
        }

        $this->assertSame(3, $ct);
    }

    public function test_command_dup_name()
    {
        $a = $this->newApp('project', '1.0.1');

        $a->command('clean', 'Cleanup project status');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Command "clean" already added');
        $a->command('clean', 'Cleanup project status', 'c');
    }

    public function test_command_dup_alias()
    {
        $a = $this->newApp('project', '1.0.1');

        $a->command('clean', 'Cleanup project status', 'c');

        $this->expectException(InvalidArgumentException::class);
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
        $logo = '
               _ _
          __ _(_) |_
         / _` | | __|
        | (_| | | |_
         \__, |_|\__|
         |___/
        ';

        $this->newApp('git', '0.0.2')
            ->logo($logo)
            ->command('add', 'stage change', 'a')
                ->arguments('<files...>')
                ->tap()
            ->parse(['git', '--help']);

        $out = file_get_contents(static::$ou);

        $this->assertStringContainsString('git, version 0.0.2', $out);
        $this->assertStringContainsString($logo, $out);
        $this->assertStringContainsString('add', $out);
        $this->assertStringContainsString('stage change', $out);
    }

    public function testCustomHelp()
    {
        $this->newApp('git', '0.0.2')
          ->help('This should be my custom help screen')
          ->parse(['git', '--help']);

        $out = file_get_contents(static::$ou);

        $this->assertStringContainsString('This should be my custom help screen', $out);
    }

    public function test_action()
    {
        ($a = $this->newApp('git', '0.0.2'))
            ->command('add', 'stage change', 'a')
                ->arguments('<files...>')
                ->action(function ($files) {
                    echo 'Add ' . implode(' and ', $files);
                })
                ->tap($a)
            ->command('config', 'list config', 'c')
                ->option('-l --list <scope>', 'list config')
                ->action(function ($list) {
                    echo "Config $list: user.email=user+100@gmail.com";
                });

        ob_start();
        $a->handle(['git', 'add', 'a.php', 'b.php']);
        $buffer = ob_get_clean();
        $this->assertSame('Add a.php and b.php', $buffer);

        ob_start();
        $a->handle(['git', 'c', '--list', 'global']);
        $buffer = ob_get_clean();
        $this->assertSame('Config global: user.email=user+100@gmail.com', $buffer);
    }

    public function test_no_action()
    {
        $a = $this->newApp('git', '0.0.2');

        $a->command('add', 'stage change', 'a')->arguments('<files...>');

        $this->assertFalse($a->handle(['git', 'add', 'a.php', 'b.php']));
    }

    public function test_action_exception()
    {
        $a = $this->newApp('git', '0.0.2');

        $a->command('add', 'stage change', 'a')->arguments('<files...>')->action(function () {
            throw new InvalidArgumentException('Dummy InvalidArgumentException');
        });

        $a->handle(['git', 'add', 'a.php', 'b.php']);

        $this->assertStringContainsString('Dummy InvalidArgumentException', file_get_contents(static::$ou));
    }

    public function test_array_action()
    {
        $a = $this->newApp('git', '0.0.2');

        $this->actionCalled = false;

        $a->command('add', 'stage change', 'a')->arguments('<files...>')->action([$this, 'action']);
        $a->handle(['git', 'add', 'a.php', 'b.php']);

        $this->assertTrue($this->actionCalled);
    }

    public function action(array $files)
    {
        $this->actionCalled = true;
    }

    public function test_logo()
    {
        $a = $this->newApp('test', '0.0.2');

        $this->assertSame($a, $a->logo($logo = '
            | |_ ___  ___| |_
            | __/ _ \/ __| __|
            | ||  __/\__ \ |_
             \__\___||___/\__|
        '));

        $this->assertSame($logo, $a->logo());
    }

    public function test_logo_command()
    {
        $a = $this->newApp('test', '0.0.2');
        $c = $a->command('cmd');

        $this->assertSame($c, $c->logo($logo = '
            | |_ ___  ___| |_
            | __/ _ \/ __| __|
            | ||  __/\__ \ |_
             \__\___||___/\__|
        '));

        $this->assertSame($logo, $c->logo());
    }

    public function test_add()
    {
        $a = $this->newApp('test', '0.0.1-test');

        $this->assertSame($a, $a->add(new Command('cmd'), 'c', true));
        $this->assertSame('cmd', $a->commandFor(['test', 'cmd'])->name());
    }

    public function test_add_dup()
    {
        $a = $this->newApp('test', '0.0.1-test');

        $this->expectException(InvalidArgumentException::class);

        $a->add(new Command('cmd'), 'cm');
        $a->add(new Command('cm'));
    }

    public function test_io()
    {
        $a = $this->newApp('test', '0.0.1-test');

        $this->assertInstanceOf(Interactor::class, $oio = $a->io());

        $a->io(new Interactor);

        $this->assertInstanceOf(Interactor::class, $a->io());
        $this->assertNotSame($oio, $a->io());
    }

    public function test_handle_empty()
    {
        $a = $this->newApp('test', '0.0.1-test');

        $a->command('make', 'Make tests');
        $a->handle(['test']);

        $o = file_get_contents(static::$ou);

        $this->assertStringContainsString('test, version 0.0.1-test', $o);
        $this->assertStringContainsString('Commands:', $o);
        $this->assertStringContainsString('make', $o);
        $this->assertStringContainsString('Make tests', $o);
    }

    public function test_cmd_not_found()
    {
        $a = $this->newApp('test')->add(new Command('cmd'))->handle(['test', 'cm']);
        $o = file_get_contents(static::$ou);

        $this->assertStringContainsString('Command cm not found', $o);
        $this->assertStringContainsString('Did you mean cmd?', $o);
    }

    public function test_io_returns_new_instance_if_not_provided(): void
    {
        $app = new Application('some-name', '0.0.1', fn () => false);

        $this->assertInstanceOf(
            Interactor::class,
            $app->io()
        );
    }

    public function test_on_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($msg = 'this will be rethrown and propagated');

        $cmd = (new Command('cmd'))->action(fn () => throw new InvalidArgumentException($msg));
        $app = $this->newApp('test')->add($cmd)->onException(fn (Throwable $e) => throw $e);
        $app->handle(['test', 'cmd']);
    }

    public function test_default_translations()
    {
        $this->assertSame('Show version', t('Show version'));
        $this->assertSame('Verbosity level [default: 0]', t('%1$s [default: %2$s]', ['Verbosity level', 0]));
        $this->assertSame('Command "rmdir" already added', t('Command "%s" already added', ['rmdir']));
    }

    public function test_custom_translations(): void
    {
        Application::addLocale('fr', [
            'Show version'               => 'Afficher la version',
            '%1$s [default: %2$s]'       => '%1$s [par défaut: %2$s]',
            'Command "%s" already added' => 'La commande "%s" a déjà été ajoutée',
        ], true);

        $this->assertSame('Afficher la version', t('Show version'));
        $this->assertSame('Niveau de verbosite [par défaut: 0]', t('%1$s [default: %2$s]', ['Niveau de verbosite', 0]));
        $this->assertSame('La commande "rmdir" a déjà été ajoutée', t('Command "%s" already added', ['rmdir']));

        // untranslated key
        $this->assertSame('Show help', t('Show help'));
    }

    public function test_app_translated()
    {
        $app = $this->newApp('test');
        $app->addLocale('fr', [
            'Show version'         => 'Afficher la version',
            'Verbosity level'      => 'Niveau de verbocité',
            '%1$s [default: %2$s]' => '%s [par défaut: %s]',
        ], true);
        $app->command('rmdir');

        $app->handle(['test', 'rmdir', '--help']);
        $o = file_get_contents(static::$ou);

        $this->assertStringContainsString('Afficher la version', $o);
        $this->assertStringContainsString('Niveau de verbocité [par défaut: 0]', $o);
    }

    protected function newApp(string $name, string $version = '')
    {
        $app = new Application($name, $version ?: '0.0.1', fn () => false);

        return $app->io(new Interactor(static::$in, static::$ou));
    }

    public function testDefaultCommand()
    {
        $app = $this->newApp('test');

        // Add some sample commands to the application
        $app->command('command1')->action(function () {
            echo 'This should be the default command';
        });
        $app->command('command2');

        // Test setting a valid default command
        $app->defaultCommand('command1');
        $this->assertEquals('command1', $app->getDefaultCommand());

        // Test executing a default command
        ob_start();
        $app->handle(['test']);
        $buffer = ob_get_clean();
        $this->assertSame('This should be the default command', $buffer);

        // Test setting an invalid default command
        $this->expectException(InvalidArgumentException::class);
        $app->defaultCommand('invalid_command');
    }
}
