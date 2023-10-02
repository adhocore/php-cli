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
use PHPUnit\Framework\TestCase;
use Ahc\Cli\Exception\InvalidArgumentException;
use Ahc\Cli\Exception\InvalidParameterException;

use function debug_backtrace;
use function ob_start;
use function ob_get_clean;

class AdvancedArgsTest extends TestCase
{

    public function test_middle_variadic()
    {
        $p = $this->newCommand()
            ->arguments('<first> <second...> <third> [fourth...]')
            ->option('--ignore [keywords...]', 'Ignore')
            ->option('--city [cities...]', 'Cities')
            ->option('-c --country [countries...]', 'Countries')
            ->option('--states [states...]', 'States');

        $v = $p->parse([
                'cmd',
                '100',
                '[', 'test', 'me', ']',
                '300',
                '--ignore', '[', 'john', 'jane', 'joe', ']',
                '--city=[', 'kathmandu', 'pokhara', ']',
                '--country', '[nepal', 'india]',
                '--states=', '[NY', 'CA]',
                '[400', '401', ']'
            ])->values();

        $this->assertSame("100", $v['first'] ?? "", "first value is not 100");
        $this->assertSame(["test", "me"], $v['second'] ?? [], "second value is not [test, me]");
        $this->assertSame("300", $v['third'] ?? "", "third value is not 300");
        $this->assertSame(["john", "jane", "joe"], $v['ignore'] ?? [], "ignore value is not [john, jane, joe]");
        $this->assertSame(["kathmandu", "pokhara"], $v['city'] ?? [], "city value is not [kathmandu, pokhara]");
        $this->assertSame(["nepal", "india"], $v['country'] ?? [], "country value is not [nepal, india]");
        $this->assertSame(["NY", "CA"], $v['states'] ?? [], "states value is not [NY, CA]");
        $this->assertSame(["400", "401"], $v['fourth'] ?? [], "fourth value is not [400, 401]");
    }

    public function test_negative_values_recognition()
    {
        $p = $this->newCommand()
            ->arguments('<offset> <limits...> [percision]')
            ->option('--trim-by <required>', 'Trim by', 'intval');

        $v = $p->parse([
            'cmd',
            '-10',                   // Normal negative number
            '[', '-200', '400', ']', // inline variadic group with negative numbers
            '-0.345',                // Negative float
            '--trim-by', '-3',       // Negative option value
        ])->values();

        $this->assertArrayHasKey('offset', $v, "offset key is not set");
        $this->assertArrayHasKey('limits', $v, "limits key is not set");
        $this->assertArrayHasKey('percision', $v, "percision key is not set");
        $this->assertSame("-10", $v['offset'], "offset value is not -10");
        $this->assertSame(["-200", "400"], $v['limits'], "limit value is not -200, 400");
        $this->assertSame('-0.345', $v['percision'], "percision value is not -0.345");
        $this->assertSame(-3, $v['trimBy'], "percision value is not -3");
    }

    public function test_complex_options_and_variadic()
    {
        $p = $this->newCommand()
            ->arguments('<first> <second...> [third]')
            ->option('--ignore [keywords...]', 'Ignore keywords') // no short name defined
            ->option('-r --replace', 'Replace flag')
            ->option('-a --all', 'Replace all')
            ->option('-t --test <required>', 'Tests')
            ->option('-n --names [name...]', 'Names');

        $v = $p->parse([
            'cmd',
            'string value long',
            '[', 'word1', 'word2', ']', // user used space.
            '--ignore', '[john', 'jane', 'joe]', // user did not use space.
            '-ra', // user used short name for two flags.
            '-t=', 'match', // user used equal sign with required.
            '-n=[', 'john', 'jane', ']', // user used equal sign with variadic.
            '300'
        ])->values();

        $this->assertSame("string value long", $v['first'] ?? "", "first value is not string value long");
        $this->assertSame(["word1", "word2"], $v['second'] ?? [], "second value is not [word1, word2]");
        $this->assertSame(["john", "jane", "joe"], $v['ignore'] ?? [], "ignore value is not [john, jane, joe]");
        $this->assertSame(true, $v['replace'] ?? false, "replace value is not true");
        $this->assertSame(true, $v['all'] ?? false, "all value is not true");
        $this->assertSame("match", $v['test'] ?? "", "test value is not 'match'");
        $this->assertSame(["john", "jane"], $v['names'] ?? [], "names value is not [john, jane]");
        $this->assertSame("300", $v['third'] ?? "", "third value is not 300");
    }

    public function test_last_variadic_without_boundaries_recognition()
    {
        // This is a valid case, but not recommended.
        $p = $this->newCommand()
            ->arguments('<path> [paths...]')
            ->option('-f --force', 'Force add ignored file', 'boolval', false);

        $v = $p->parse([
            'cmd',
            'path normal',
            'path1', 
            'path2', 
            '-f' // Negative option value
        ])->values();

        $this->assertSame("path normal", $v["path"] ?? []);
        $this->assertSame(["path1", "path2"], $v["paths"] ?? []);
        $this->assertTrue($v["force"] ?? false, "");

        // Even this is valid, but not recommended.
        $p = $this->newCommand()
            ->arguments('<path> [paths...]')
            ->option('-f --force', 'Force add ignored file', 'boolval', false)
            ->option('-m --more [items...]', 'Force add ignored file');

        $v = $p->parse([
            'cmd',
            'path normal',
            'path1',
            'path2',
            '-f',
            '-m',
            'm1', 'm2', 'm3'
        ])->values();

        $this->assertSame("path normal", $v["path"] ?? []);
        $this->assertSame(["path1", "path2"], $v["paths"] ?? []);
        $this->assertTrue($v["force"] ?? false);
        $this->assertSame(["m1", "m2", "m3"], $v["more"] ?? []);
    }

    public function test_event_with_variadic()
    {

        $p = $this->newCommand()->option('--hello [names...]')->on(function ($value) {
            echo 'greeting '.$value.PHP_EOL;
        });

        $expected = "greeting john".PHP_EOL.
                    "greeting bob".PHP_EOL;

        ob_start();
        $p->parse(['php', '--hello', "john", "bob"]);

        $this->assertSame($expected, ob_get_clean());

        $p = $this->newCommand()->option('--hello [names...]')->on(function ($value) {
            echo 'greeting '.$value.PHP_EOL;
        });

        ob_start();
        $p->parse(['php', '--hello', "[", "john", "bob", "]"]);

        $this->assertSame($expected, ob_get_clean());
    }

    public function test_variadic_group_contains_non_constants()
    {
        $p = $this->newCommand()
            ->arguments('<path> [paths...]')
            ->option('--hello [bob]');

        $this->expectException(InvalidParameterException::class);

        $p->parse([
            "cmd", "path", "[", "john", "bob", "--opt", "]", "--hello", "john"
        ]);
    }

    public function test_param_is_not_variadic_constants()
    {
        $p = $this->newCommand()
            ->arguments('<action>')
            ->option('--hello');

        $this->expectException(InvalidArgumentException::class);

        $p->parse([
            "cmd", "[", "greet", "register", "]", "--hello", "john"
        ]);
    }

    public function test_variadic_is_added_as_indexed()
    {
        // This one is valid, and john is added as to --hello:
        $p = $this->newCommand()->arguments('<action>')->option('--hello');
        $p->parse([
            "cmd", "greet", "--hello", "john"
        ]);
        $this->assertSame("john", $p->values()["hello"] ?? "");
        $this->assertSame("greet", $p->values()["action"] ?? "");

        // This one is also valid, and john the group is added with extra index:
        $p = $this->newCommand()->arguments('<action>')->option('--hello');
        $p->parse([
            "cmd", "greet", "--hello", "[", "john", "bob", "]"
        ]);

        $v = $p->values();
        $this->assertTrue($v["hello"] ?? false);
        $this->assertSame(
            ["john", "bob"],
            [$v[0] ?? "", $v[1] ?? ""]
        );
        $this->assertSame("greet", $v["action"] ?? "");
    }

    public function test_variadic_with_literal_insane_cases()
    {
        // 1. normal way:
        $p = $this->newCommand()->arguments('<names...>')
                                ->option('--args [a...]');
        $p->parse([
            "cmd",
            "[", "john", "bob", "jane", "]",
            "--args", "a", "a1", "b",
        ]);
        $v = $p->values();
        $this->assertSame(["john", "bob", "jane"], $v["names"] ?? []);
        $this->assertSame(["a", "a1", "b"], $v["args"] ?? []);

        // 2. crazy way but should work:
        $p = $this->newCommand()->arguments('<names...>')
                                ->option('--args [a...]');
        $p->parse([
            "cmd",
            "john", "bob", "jane",
            "--args", "--", "-a", "--a1", "-b",
        ]);
        $v = $p->values();
        $this->assertSame(["john", "bob", "jane"], $v["names"] ?? []);
        $this->assertSame(["-a", "--a1", "-b"], $v["args"] ?? []);

        // 3. crazy way but should work:
        $p = $this->newCommand()->arguments('<names...>')
                                ->option('--args [a...]');
        $p->parse([
            "cmd",
            "--args", "[", "--", "-a", "--a1", "-b", "]",
            "[", "john", "bob", "jane","]",
        ]);
        $v = $p->values();
        $this->assertSame(["john", "bob", "jane"], $v["names"] ?? []);
        $this->assertSame(["-a", "--a1", "-b"], $v["args"] ?? []);

        // 4. Insane way but should work:
        $p = $this->newCommand()->arguments('<names...>')
                                ->option('--args [a...]');
        $p->parse([
            "cmd",
            "john", "bob",
            "--args", "[", "--", "-a", "--a1", "-b", "]",
             "jane",
        ]);
        $v = $p->values();
        $this->assertSame(["john", "bob", "jane"], $v["names"] ?? []);
        $this->assertSame(["-a", "--a1", "-b"], $v["args"] ?? []);
    }

    protected function newCommand(string $version = '0.0.1', string $desc = '', bool $allowUnknown = false, $app = null)
    {
        $p = new Command('cmd', $desc, $allowUnknown, $app);

        return $p->version($version . debug_backtrace()[1]['function'])->onExit(function () {

            return false;
        });
    }
}