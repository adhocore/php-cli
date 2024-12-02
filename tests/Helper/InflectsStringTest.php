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

use Ahc\Cli\Application;
use Ahc\Cli\Helper\InflectsString;
use PHPUnit\Framework\TestCase;

class InflectsStringTest extends TestCase
{
    use InflectsString;

    public function test_to_camel_case()
    {
        $this->assertSame('aB', $this->toCamelCase('a-b'));
        $this->assertSame('theLongName', $this->toCamelCase('--the_long-name'));
        $this->assertSame('aBC', $this->toCamelCase('a_bC'));
    }

    public function test_to_words()
    {
        $this->assertSame('A B', $this->toWords('a-b'));
        $this->assertSame('The Long Name', $this->toWords('--the_long-name'));
        $this->assertSame('A BC', $this->toWords('a_bC'));
    }

    public function test_default_translate(): void
    {
        $this->assertSame('Show version', $this->translate('Show version'));
        $this->assertSame('Verbosity level [default: 0]', $this->translate('%s [default: %s]', ['Verbosity level', 0]));
        $this->assertSame('Command "rmdir" already added', $this->translate('Command "%s" already added', ['rmdir']));
    }

    public function test_custom_translations(): void
    {
        Application::addLocale('fr', [
            'Show version' => 'Afficher la version',
            '%s [default: %s]' => '%s [par défaut: %s]',
            'Command "%s" already added' => 'La commande "%s" a déjà été ajoutée'
        ], true);


        $this->assertSame('Afficher la version', $this->translate('Show version'));
        $this->assertSame('Verbosity level [par défaut: 0]', $this->translate('%s [default: %s]', ['Verbosity level', 0]));
        $this->assertSame('La commande "rmdir" a déjà été ajoutée', $this->translate('Command "%s" already added', ['rmdir']));

        // untranslated key
        $this->assertSame('Show help', $this->translate('Show help'));
    }
}
