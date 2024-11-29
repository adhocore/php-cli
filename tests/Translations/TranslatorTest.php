<?php

use Ahc\Cli\Translations\Translator;
use PHPUnit\Framework\TestCase;

class TranslatorTest extends TestCase
{
    protected Translator $translator;

    protected static string $baseTranslationsDir;

    protected function setUp(): void
    {
        $this->translator = new Translator();
    }

    public static function setUpBeforeClass(): void
    {
        self::$baseTranslationsDir = Translator::$translations_dir;
    }

    protected function tearDown(): void
    {
        Translator::$translations_dir = self::$baseTranslationsDir;
    }

    public function test_get_message_returns_empty_string_for_empty_key(): void
    {
        $result = $this->translator->getMessage('');
        $this->assertSame('', $result);
    }

    public function test_default_translations_loaded_on_instantiation(): void
    {
        $reflector = new ReflectionClass(Translator::class);
        $translationsProperty = $reflector->getProperty('translations');
        $translationsProperty->setAccessible(true);

        $translations = $translationsProperty->getValue();

        $this->assertArrayHasKey('__default__', $translations);
        $this->assertNotEmpty($translations['__default__']);
        $this->assertSame(require self::$baseTranslationsDir . '/en.php', $translations['__default__']);
    }

    public function test_get_message_returns_correct_translation_for_default_locale(): void
    {
        $key = 'badProgram';
        $expectedTranslation = 'Bad program could not be started.';

        $result = $this->translator->getMessage($key);

        $this->assertSame($expectedTranslation, $result);
    }

    public function test_get_message_replaces_placeholders_with_arguments(): void
    {
        $key = 'test_placeholder';
        $message = 'Hello, %s! You are %d years old.';
        $args = ['John', 30];
        $expectedResult = 'Hello, John! You are 30 years old.';

        // Set up a mock translation
        $reflector = new ReflectionClass(Translator::class);
        $translationsProperty = $reflector->getProperty('translations');
        $translationsProperty->setAccessible(true);
        $translationsProperty->setValue([
            'en' => [$key => $message]
        ]);

        $result = $this->translator->getMessage($key, $args);

        $this->assertSame($expectedResult, $result);
    }

    public function test_get_message_returns_empty_string_for_non_existent_key(): void
    {
        $nonExistentKey = 'non_existent_key';
        $result = $this->translator->getMessage($nonExistentKey);
        $this->assertSame('', $result);
    }

    public function test_load_translations_for_non_default_locale(): void
    {
        $customLocale = 'french'; // dont use `fr` because it's a true locale and file exist
        Translator::$locale = $customLocale;

        // Create a mock translation file for the custom locale
        $mockTranslations = [
            'test_key' => 'Test en français',
        ];
        $mockFilePath = Translator::$translations_dir . '/' . $customLocale . '.php';
        file_put_contents($mockFilePath, '<?php return ' . var_export($mockTranslations, true) . ';');

        $translator = new Translator();
        $result = $translator->getMessage('test_key');

        $this->assertSame('Test en français', $result);

        // Clean up
        unlink($mockFilePath);
        Translator::$locale = 'en';
    }

    public function test_merge_custom_translations_with_default_translations(): void
    {
        $customLocale = 'fr';
        Translator::$locale = $customLocale;
        Translator::$translations_dir = __DIR__;

       // Create mock custom translations
        $customTranslations = [
            'usage' => 'Utilisation',
        ];

        $mockCustomPath = Translator::$translations_dir . '/' . $customLocale . '.php';
        file_put_contents($mockCustomPath, '<?php return ' . var_export($customTranslations, true) . ';');

        $translator = new Translator();

        // Test merged translations
        $this->assertSame('Utilisation', $translator->getMessage('usage'));
        $this->assertSame('Usage Examples', $translator->getMessage('usageExamples'));

        unlink($mockCustomPath);
        Translator::$locale = 'en';
    }

    public function test_use_cached_translations_when_requesting_same_locale_multiple_times(): void
    {
        $customLocale = 'fr';
        Translator::$locale = $customLocale;
        Translator::$translations_dir = __DIR__;

        // Create a mock translation file for the custom locale
        $mockTranslations = [
            'test_key' => 'Test en français',
        ];
        $mockFilePath = Translator::$translations_dir . '/' . $customLocale . '.php';
        file_put_contents($mockFilePath, '<?php return ' . var_export($mockTranslations, true) . ';');

        $translator = new Translator();

        // First call to load translations
        $result1 = $translator->getMessage('test_key');
        $this->assertSame('Test en français', $result1);

        // Modify the mock file to ensure we're using cached translations
        file_put_contents($mockFilePath, '<?php return ' . var_export(['test_key' => 'Modified test'], true) . ';');

        // Second call should use cached translations
        $result2 = $translator->getMessage('test_key');
        $this->assertSame('Test en français', $result2);

        // Clean up
        unlink($mockFilePath);
        Translator::$locale = 'en';
    }

    public function test_handle_non_existent_translation_file(): void
    {
        $nonExistentLocale = 'xx';
        Translator::$locale = $nonExistentLocale;

        // Ensure the translation file doesn't exist
        $nonExistentPath = Translator::$translations_dir . '/' . $nonExistentLocale . '.php';
        $this->assertFileDoesNotExist($nonExistentPath);

        $translator = new Translator();

        // Test with a key that exists in the default translations
        $defaultKey = 'badProgram';
        $expectedDefaultTranslation = 'Bad program could not be started.';
        $result = $translator->getMessage($defaultKey);
        $this->assertSame($expectedDefaultTranslation, $result);

        // Test with a key that doesn't exist in the default translations
        $nonExistentKey = 'non_existent_key';
        $result = $translator->getMessage($nonExistentKey);
        $this->assertSame('', $result);

        // Reset the locale
        Translator::$locale = 'en';
    }

    public function test_change_locale_after_initial_translations_loaded(): void
    {
        Translator::$translations_dir = __DIR__;

        // Set up initial locale and translations
        Translator::$locale = 'en';
        $translator = new Translator();
        $initialMessage = $translator->getMessage('badProgram');

        // Change locale to a new one
        $newLocale = 'fr';
        Translator::$locale = $newLocale;

        // Create mock translations for the new locale
        $mockTranslations = [
            'badProgram' => 'Mauvais programme',
        ];
        $mockFilePath = Translator::$translations_dir . '/' . $newLocale . '.php';
        file_put_contents($mockFilePath, '<?php return ' . var_export($mockTranslations, true) . ';');

        // Get message with new locale
        $newMessage = $translator->getMessage('badProgram');

        // Assert that the messages are different
        $this->assertNotSame($initialMessage, $newMessage);
        $this->assertSame('Mauvais programme', $newMessage);

        // Clean up
        unlink($mockFilePath);
        Translator::$locale = 'en';
    }
}
