<?php

namespace Ahc\Cli\Test\Input;

use Ahc\Cli\Input\ArgvParser;
use PHPUnit\Framework\TestCase;

class ArgvParserTest extends TestCase
{
    public function test_new()
    {
        $p = new ArgvParser('ArgvParser');

        $p->version('0.0.' . rand(1, 10));

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
        $p = $this->newParser()->arguments('<cmd> [env]')->parse(['php', 'mycmd']);

        $this->assertSame('mycmd', $p->cmd);
        $this->assertNull($p->env, 'No default');

        $p = $this->newParser()->arguments('<id:adhocore> [hobbies...]')->parse(['php']);

        $this->assertSame('adhocore', $p->id, 'Default');
        $this->assertEmpty($p->hobbies, 'No default');
        $this->assertSame([], $p->hobbies, 'Variadic');

        $p = $this->newParser()->arguments('<dir> [dirs...]')->parse(['php', 'dir1', 'dir2', 'dir3']);
        $this->assertSame('dir1', $p->dir);
        $this->assertTrue(is_array($p->dirs));
        $this->assertSame(['dir2', 'dir3'], $p->dirs);
    }

    public function test_arguments_variadic_not_last()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Only last argument can be variadic');

        $p = $this->newParser()->arguments('<paths...> [env]');
    }

    public function test_arguments_with_options()
    {
        $p = $this->newParser()->arguments('<cmd> [env]')
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

        $p = $this->newParser()->option('-a --apple', 'Apple')->option('-a --apple', 'Apple');
    }

    public function test_options_unknown()
    {
        $p = $this->newParser('', '', true)->parse(['php', '--hot-path', '/path']);
        $this->assertSame('/path', $p->hotPath, 'Allow unknown');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Option "--random" not registered');

        // Dont allow unknown
        $p = $this->newParser()->option('-k known [opt]')->parse(['php', '-k', '--random', 'rr']);
    }

    public function test_literals()
    {
        $p = $this->newParser()->option('-a --apple', 'Apple')->option('-b --ball', 'Ball');

        $p->parse(['php', '-a', 'the apple', '--', '--ball', 'the ball']);

        $this->assertSame('the apple', $p->apple);
        $this->assertNotSame('the ball', $p->ball);
        $this->assertSame('--ball', $p->{0}, 'Should be arg');
        $this->assertSame('the ball', $p->{1}, 'Should be arg');
    }

    public function test_options()
    {
        $p = $this->newParser()->option('-u --user-id [id]', 'User id')->parse(['php']);
        $this->assertNull($p->userId, 'Optional no default');

        $p = $this->newParser()->option('-c --cheese [type]', 'User id')->parse(['php', '-c']);
        $this->assertSame(true, $p->cheese, 'Optional given');

        $p = $this->newParser()->option('-u --user-id [id]', 'User id', null, 1)->parse(['php']);
        $this->assertSame(1, $p->userId, 'Optional default');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Option "--user-id" is required');

        $p = $this->newParser()->option('-u --user-id <id>', 'User id')->parse(['php']);
    }

    public function test_special_options()
    {
        $p = $this->newParser()->option('-n --no-more', '')->option('-w --with-that', '');

        $p->parse(['php', '-nw']);

        $this->assertTrue($p->that, '--with becomes true when given');
        $this->assertFalse($p->more, '--no becomes false when given');

        $p = $this->newParser()->option('--any')->parse(['php', '--any=thing']);
        $this->assertSame('thing', $p->any);

        $p = $this->newParser()->option('-m --many [item...]')->parse(['php', '--many=1', '2']);
        $this->assertSame(['1', '2'], $p->many);
    }

    public function test_bool_options()
    {
        $p = $this->newParser()->option('-n --no-more', '')->option('-w --with-that', '')
            ->parse(['php']);

        $this->assertTrue($p->more);
        $this->assertFalse($p->that);

        $p = $this->newParser()->option('-n --no-more', '')->option('-w --with-that', '')
            ->parse(['php', '--no-more', '-w']);

        $this->assertFalse($p->more);
        $this->assertTrue($p->that);
    }

    public function test_event()
    {
        $p = $this->newParser()->option('--hello')->on(function () {
            echo 'hello event';
        });

        ob_start();
        $p->parse(['php', '--hello']);

        $this->assertSame('hello event', ob_get_clean());
    }

    public function test_no_value()
    {
        $p = $this->newParser()->option('-x --xyz')->parse(['php', '-x']);

        $this->assertNull($p->xyz);
    }

    public function test_args()
    {
        $p = $this->newParser()->arguments('<a> [b]')->option('-x --xyz')
            ->parse(['php', 'A', '-x', 'X', 'B', 'C', 'D']);

        $this->assertSame(['a' => 'A', 'b' => 'B', 'C', 'D'], $p->args());
    }

    public function test_tap()
    {
        $this->assertInstanceOf(static::class, $this->newParser()->tap($this));
        $this->assertSame('asdf', $this->newParser()->tap('asdf'));
        $this->assertSame(234, $this->newParser()->tap(234));
    }

    protected function newParser(string $version = '0.0.1', string $desc = '', bool $allowUnknown = false)
    {
        $p = new ArgvParser('ArgvParserTest', $desc, $allowUnknown);

        return $p->version($version);
    }
}
