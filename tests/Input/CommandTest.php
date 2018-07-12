<?php

namespace Ahc\Cli\Test\Input;

use Ahc\Cli\Application;
use Ahc\Cli\Input\Command;
use PHPUnit\Framework\TestCase;

class CommandTest extends TestCase
{
    public function test_new()
    {
        $p = $this->newCommand('0.0.' . rand(1, 10));

        $data = $this->data();
        foreach ($data['options'] as $option) {
            $p->option($option['cmd']);
        }

        foreach ($data['argvs'] as $argv) {
            if (isset($argv['throws'])) {
                $this->expectException($argv['throws'][0]);
                $this->expectExceptionMessage($argv['throws'][1]);
            }

            $values = $p->parse($argv['argv']);

            $argv += ['expect' => []];

            foreach ($argv['expect'] as $key => $expect) {
                $this->assertSame($expect, $values[$key]);
            }
        }
    }

    public function data()
    {
        return require __DIR__ . '/fixture.php';
    }

    public function test_arguments()
    {
        $p = $this->newCommand()->arguments('<cmd> [env]')->parse(['php', 'mycmd']);

        $this->assertSame('mycmd', $p->cmd);
        $this->assertNull($p->env, 'No default');

        $p = $this->newCommand()->arguments('<id:adhocore> [hobbies...]')->parse(['php']);

        $this->assertSame('adhocore', $p->id, 'Default');
        $this->assertEmpty($p->hobbies, 'No default');
        $this->assertSame([], $p->hobbies, 'Variadic');

        $p = $this->newCommand()->arguments('<dir> [dirs...]')->parse(['php', 'dir1', 'dir2', 'dir3']);
        $this->assertSame('dir1', $p->dir);
        $this->assertTrue(is_array($p->dirs));
        $this->assertSame(['dir2', 'dir3'], $p->dirs);
    }

    public function test_arguments_variadic_not_last()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Only last argument can be variadic');

        $p = $this->newCommand()->arguments('[paths...]')->argument('[env]', 'Env');
    }

    public function test_arguments_with_options()
    {
        $p = $this->newCommand()->arguments('<cmd> [env]')
            ->option('-c --config', 'Config')
            ->option('-d --dir', 'Dir')
            ->parse(['php', 'thecmd', '-d', 'dir1', 'dev', '-c', 'conf.yml', 'any', 'thing']);

        $this->assertArrayHasKey('help', $p->values());
        $this->assertArrayNotHasKey('help', $p->values(false));

        $this->assertSame('dir1', $p->dir);
        $this->assertSame('conf.yml', $p->config);
        $this->assertSame('thecmd', $p->cmd);
        $this->assertSame('dev', $p->env);
        $this->assertSame('any', $p->{0});
        $this->assertSame('thing', $p->{1});
    }

    public function test_options_repeat()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The parameter "--apple" is already registered');

        $p = $this->newCommand()->option('-a --apple', 'Apple')->option('-a --apple', 'Apple');
    }

    public function test_options_unknown()
    {
        $p = $this->newCommand('', '', true)->parse(['php', '--hot-path', '/path']);
        $this->assertSame('/path', $p->hotPath, 'Allow unknown');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Option "--random" not registered');

        // Dont allow unknown
        $p = $this->newCommand()->option('-k known [opt]')->parse(['php', '-k', '--random', 'rr']);
    }

    public function test_literals()
    {
        $p = $this->newCommand()->option('-a --apple', 'Apple')->option('-b --ball', 'Ball');

        $p->parse(['php', '-a', 'the apple', '--', '--ball', 'the ball']);

        $this->assertSame('the apple', $p->apple);
        $this->assertNotSame('the ball', $p->ball);
        $this->assertSame('--ball', $p->{0}, 'Should be arg');
        $this->assertSame('the ball', $p->{1}, 'Should be arg');
    }

    public function test_options()
    {
        $p = $this->newCommand()->option('-u --user-id [id]', 'User id')->parse(['php']);
        $this->assertNull($p->userId, 'Optional no default');

        $p = $this->newCommand()->option('-c --cheese [type]', 'User id')->parse(['php', '-c']);
        $this->assertSame(true, $p->cheese, 'Optional given');

        $p = $this->newCommand()->option('-u --user-id [id]', 'User id', null, 1)->parse(['php']);
        $this->assertSame(1, $p->userId, 'Optional default');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Option "--user-id" is required');

        $p = $this->newCommand()->option('-u --user-id <id>', 'User id')->parse(['php']);
    }

    public function test_special_options()
    {
        $p = $this->newCommand()->option('-n --no-more', '')->option('-w --with-that', '');

        $p->parse(['php', '-nw']);

        $this->assertTrue($p->that, '--with becomes true when given');
        $this->assertFalse($p->more, '--no becomes false when given');

        $p = $this->newCommand()->option('--any')->parse(['php', '--any=thing']);
        $this->assertSame('thing', $p->any);

        $p = $this->newCommand()->option('-m --many [item...]')->parse(['php', '--many=1', '2']);
        $this->assertSame(['1', '2'], $p->many);
    }

    public function test_bool_options()
    {
        $p = $this->newCommand()->option('-n --no-more', '')->option('-w --with-that', '')
            ->parse(['php']);

        $this->assertTrue($p->more);
        $this->assertFalse($p->that);

        $p = $this->newCommand()->option('-n --no-more', '')->option('-w --with-that', '')
            ->parse(['php', '--no-more', '-w']);

        $this->assertFalse($p->more);
        $this->assertTrue($p->that);
    }

    public function test_user_options()
    {
        $p = $this->newCommand();

        $this->assertEmpty($p->userOptions());

        $p = $this->newCommand()->option('-u --user', 'User');

        $this->assertNotEmpty($o = $p->userOptions());
        $this->assertCount(1, $o);
        $this->assertSame('user', reset($o)->name());
    }

    public function test_usage()
    {
        $p = $this->newCommand()->usage('Usage: $ cmd [...]');

        $this->assertSame('Usage: $ cmd [...]', $p->usage());
    }

    public function test_event()
    {
        $p = $this->newCommand()->option('--hello')->on(function () {
            echo 'hello event';
        });

        ob_start();
        $p->parse(['php', '--hello']);

        $this->assertSame('hello event', ob_get_clean());
    }

    public function test_no_value()
    {
        $p = $this->newCommand()->option('-x --xyz')->parse(['php', '-x']);

        $this->assertTrue($p->xyz, 'not required becomes true');
    }

    public function test_args()
    {
        $p = $this->newCommand()->arguments('<a> [b]')->option('-x --xyz')
            ->parse(['php', 'A', '-x', 'X', 'B', 'C', 'D']);

        $this->assertSame(['a' => 'A', 'b' => 'B', 'C', 'D'], $p->args());
    }

    public function test_tap()
    {
        $this->assertInstanceOf(static::class, $this->newCommand()->tap($this));
        $this->assertSame('asdf', $this->newCommand()->tap('asdf'));
        $this->assertSame(234, $this->newCommand()->tap(234));
    }

    public function test_app_tap()
    {
        $c = $this->newCommand();
        $this->assertNull($c->tap());
        $this->assertNull($c->app());

        $c = $this->newCommand('', '', false, new Application('app'));
        $this->assertInstanceOf(Application::class, $c->app());
        $this->assertInstanceOf(Application::class, $c->app());
    }

    public function test_bind()
    {
        $c = $this->newCommand()->bind(new Application('app'));
        $this->assertInstanceOf(Application::class, $c->app());

        $c = $this->newCommand()->bind(null);
        $this->assertNull($c->app());
    }

    protected function newCommand(string $version = '0.0.1', string $desc = '', bool $allowUnknown = false, $app = null)
    {
        $p = new Command('cmd', $desc, $allowUnknown, $app);

        return $p->version($version);
    }
}
