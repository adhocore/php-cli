<?php

/*
 * This file is part of the PHP-CLI package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

namespace Ahc\Cli\Translations;

use function array_merge;
use function is_file;
use function sprintf;

/**
 * Translation support of CLI
 *
 * @author  Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @license MIT
 *
 * @link    https://github.com/adhocore/cli
 */
class Translator
{
    /**
     * Locale of CLI.
     */
    public static string $locale = 'en';

    /**
     * Folder in which the translation files are to be searched.
     */
    public static string $translations_dir = __DIR__;

    /**
     * Stores translations recovered from files for faster recovery when used a second time.
     *
     * @var array<string, array>
     *
     * @example
     * ```php
     *  ['locale' => ['key1' => 'value1', 'key2' => 'value2']]
     * ```
     */
    private static array $translations = [];

    /**
	 * Constructor.
	 */
    public function __construct()
    {
        $this->loadDefaultTranslations();
    }

    /**
     * Retrieves a translated string based on the provided key and optional arguments.
     *
     * @param string $key  The key of the translation string.
     * @param array  $args Optional arguments to replace placeholders in the translation string.
     */
    public function getMessage(string $key, array $args = []): string
    {
        if ($key === '') {
            return '';
        }

        $this->loadTranslations();

        $message = self::$translations[self::$locale][$key] ?? '';

        return $args === [] ? $message : sprintf($message, ...$args);
    }

    /**
     * Loads translations for the current locale if they haven't been loaded yet.
     *
     * This method checks if translations for the current locale exist in the static
     * $translations array. If not, it attempts to load them from a PHP file in the
     * translations directory. The file name is expected to match the locale name.
     * If the file exists, its contents are merged with the default translations.
     */
    protected function loadTranslations(): void
    {
        if (!isset(self::$translations[self::$locale])) {
            $path = self::$translations_dir . '/' . self::$locale . '.php';

            $translations = is_file($path) ? require $path : [];

            self::$translations[self::$locale] = array_merge(self::$translations['__default__'], $translations);
        }
    }

    /**
     * Loads the default translations for the application.
     */
    protected function loadDefaultTranslations(): void
    {
        if (!isset(self::$translations['__default__'])) {
            self::$translations['__default__'] = require __DIR__ . '/en.php';
        }
    }
}
