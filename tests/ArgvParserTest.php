<?php

namespace Ahc\Cli\Test;

use Ahc\Cli\ArgvParser;
use PHPUnit\Framework\TestCase;

class ArgvParserTest extends TestCase
{
    public function test_new()
    {
        $p = new ArgvParser('ArgvParser');

        $p->version('0.0.'.rand(1, 10));

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
        return require __DIR__.'/fixture.php';
    }
}
