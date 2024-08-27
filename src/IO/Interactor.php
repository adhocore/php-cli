<?php

/*
 * This file is part of the PHP-CLI package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

namespace Ahc\Cli\IO;

use Ahc\Cli\Input\Reader;
use Ahc\Cli\Output\Writer;
use Throwable;

use function array_keys;
use function array_map;
use function count;
use function explode;
use function func_get_args;
use function in_array;
use function is_string;
use function ltrim;
use function max;
use function method_exists;
use function range;
use function str_pad;
use function str_replace;
use function strtolower;

/**
 * Cli Interactor.
 *
 * @author  Jitendra Adhikari <jiten.adhikary@gmail.com>
 * @license MIT
 *
 * @link    https://github.com/adhocore/cli
 *
 * @method Writer bgBlack($text, $eol = false)
 * @method Writer bgBlue($text, $eol = false)
 * @method Writer bgCyan($text, $eol = false)
 * @method Writer bgGreen($text, $eol = false)
 * @method Writer bgPurple($text, $eol = false)
 * @method Writer bgRed($text, $eol = false)
 * @method Writer bgWhite($text, $eol = false)
 * @method Writer bgYellow($text, $eol = false)
 * @method Writer black($text, $eol = false)
 * @method Writer blackBgBlue($text, $eol = false)
 * @method Writer blackBgCyan($text, $eol = false)
 * @method Writer blackBgGreen($text, $eol = false)
 * @method Writer blackBgPurple($text, $eol = false)
 * @method Writer blackBgRed($text, $eol = false)
 * @method Writer blackBgWhite($text, $eol = false)
 * @method Writer blackBgYellow($text, $eol = false)
 * @method Writer blue($text, $eol = false)
 * @method Writer blueBgBlack($text, $eol = false)
 * @method Writer blueBgCyan($text, $eol = false)
 * @method Writer blueBgGreen($text, $eol = false)
 * @method Writer blueBgPurple($text, $eol = false)
 * @method Writer blueBgRed($text, $eol = false)
 * @method Writer blueBgWhite($text, $eol = false)
 * @method Writer blueBgYellow($text, $eol = false)
 * @method Writer bold($text, $eol = false)
 * @method Writer boldBlack($text, $eol = false)
 * @method Writer boldBlackBgBlue($text, $eol = false)
 * @method Writer boldBlackBgCyan($text, $eol = false)
 * @method Writer boldBlackBgGreen($text, $eol = false)
 * @method Writer boldBlackBgPurple($text, $eol = false)
 * @method Writer boldBlackBgRed($text, $eol = false)
 * @method Writer boldBlackBgWhite($text, $eol = false)
 * @method Writer boldBlackBgYellow($text, $eol = false)
 * @method Writer boldBlue($text, $eol = false)
 * @method Writer boldBlueBgBlack($text, $eol = false)
 * @method Writer boldBlueBgCyan($text, $eol = false)
 * @method Writer boldBlueBgGreen($text, $eol = false)
 * @method Writer boldBlueBgPurple($text, $eol = false)
 * @method Writer boldBlueBgRed($text, $eol = false)
 * @method Writer boldBlueBgWhite($text, $eol = false)
 * @method Writer boldBlueBgYellow($text, $eol = false)
 * @method Writer boldCyan($text, $eol = false)
 * @method Writer boldCyanBgBlack($text, $eol = false)
 * @method Writer boldCyanBgBlue($text, $eol = false)
 * @method Writer boldCyanBgGreen($text, $eol = false)
 * @method Writer boldCyanBgPurple($text, $eol = false)
 * @method Writer boldCyanBgRed($text, $eol = false)
 * @method Writer boldCyanBgWhite($text, $eol = false)
 * @method Writer boldCyanBgYellow($text, $eol = false)
 * @method Writer boldGreen($text, $eol = false)
 * @method Writer boldGreenBgBlack($text, $eol = false)
 * @method Writer boldGreenBgBlue($text, $eol = false)
 * @method Writer boldGreenBgCyan($text, $eol = false)
 * @method Writer boldGreenBgPurple($text, $eol = false)
 * @method Writer boldGreenBgRed($text, $eol = false)
 * @method Writer boldGreenBgWhite($text, $eol = false)
 * @method Writer boldGreenBgYellow($text, $eol = false)
 * @method Writer boldPurple($text, $eol = false)
 * @method Writer boldPurpleBgBlack($text, $eol = false)
 * @method Writer boldPurpleBgBlue($text, $eol = false)
 * @method Writer boldPurpleBgCyan($text, $eol = false)
 * @method Writer boldPurpleBgGreen($text, $eol = false)
 * @method Writer boldPurpleBgRed($text, $eol = false)
 * @method Writer boldPurpleBgWhite($text, $eol = false)
 * @method Writer boldPurpleBgYellow($text, $eol = false)
 * @method Writer boldRed($text, $eol = false)
 * @method Writer boldRedBgBlack($text, $eol = false)
 * @method Writer boldRedBgBlue($text, $eol = false)
 * @method Writer boldRedBgCyan($text, $eol = false)
 * @method Writer boldRedBgGreen($text, $eol = false)
 * @method Writer boldRedBgPurple($text, $eol = false)
 * @method Writer boldRedBgWhite($text, $eol = false)
 * @method Writer boldRedBgYellow($text, $eol = false)
 * @method Writer boldWhite($text, $eol = false)
 * @method Writer boldWhiteBgBlack($text, $eol = false)
 * @method Writer boldWhiteBgBlue($text, $eol = false)
 * @method Writer boldWhiteBgCyan($text, $eol = false)
 * @method Writer boldWhiteBgGreen($text, $eol = false)
 * @method Writer boldWhiteBgPurple($text, $eol = false)
 * @method Writer boldWhiteBgRed($text, $eol = false)
 * @method Writer boldWhiteBgYellow($text, $eol = false)
 * @method Writer boldYellow($text, $eol = false)
 * @method Writer boldYellowBgBlack($text, $eol = false)
 * @method Writer boldYellowBgBlue($text, $eol = false)
 * @method Writer boldYellowBgCyan($text, $eol = false)
 * @method Writer boldYellowBgGreen($text, $eol = false)
 * @method Writer boldYellowBgPurple($text, $eol = false)
 * @method Writer boldYellowBgRed($text, $eol = false)
 * @method Writer boldYellowBgWhite($text, $eol = false)
 * @method Writer colors($text)
 * @method Writer comment($text, $eol = false)
 * @method Writer cyan($text, $eol = false)
 * @method Writer cyanBgBlack($text, $eol = false)
 * @method Writer cyanBgBlue($text, $eol = false)
 * @method Writer cyanBgGreen($text, $eol = false)
 * @method Writer cyanBgPurple($text, $eol = false)
 * @method Writer cyanBgRed($text, $eol = false)
 * @method Writer cyanBgWhite($text, $eol = false)
 * @method Writer cyanBgYellow($text, $eol = false)
 * @method Writer eol(int $n = 1)
 * @method Writer error($text, $eol = false)
 * @method Writer green($text, $eol = false)
 * @method Writer greenBgBlack($text, $eol = false)
 * @method Writer greenBgBlue($text, $eol = false)
 * @method Writer greenBgCyan($text, $eol = false)
 * @method Writer greenBgPurple($text, $eol = false)
 * @method Writer greenBgRed($text, $eol = false)
 * @method Writer greenBgWhite($text, $eol = false)
 * @method Writer greenBgYellow($text, $eol = false)
 * @method Writer info($text, $eol = false)
 * @method Writer ok($text, $eol = false)
 * @method Writer purple($text, $eol = false)
 * @method Writer purpleBgBlack($text, $eol = false)
 * @method Writer purpleBgBlue($text, $eol = false)
 * @method Writer purpleBgCyan($text, $eol = false)
 * @method Writer purpleBgGreen($text, $eol = false)
 * @method Writer purpleBgRed($text, $eol = false)
 * @method Writer purpleBgWhite($text, $eol = false)
 * @method Writer purpleBgYellow($text, $eol = false)
 * @method Writer red($text, $eol = false)
 * @method Writer redBgBlack($text, $eol = false)
 * @method Writer redBgBlue($text, $eol = false)
 * @method Writer redBgCyan($text, $eol = false)
 * @method Writer redBgGreen($text, $eol = false)
 * @method Writer redBgPurple($text, $eol = false)
 * @method Writer redBgWhite($text, $eol = false)
 * @method Writer redBgYellow($text, $eol = false)
 * @method Writer table(array $rows, array $styles = [])
 * @method Writer warn($text, $eol = false)
 * @method Writer white($text, $eol = false)
 * @method Writer yellow($text, $eol = false)
 * @method Writer yellowBgBlack($text, $eol = false)
 * @method Writer yellowBgBlue($text, $eol = false)
 * @method Writer yellowBgCyan($text, $eol = false)
 * @method Writer yellowBgGreen($text, $eol = false)
 * @method Writer yellowBgPurple($text, $eol = false)
 * @method Writer yellowBgRed($text, $eol = false)
 * @method Writer yellowBgWhite($text, $eol = false)
 */
class Interactor
{
    protected Reader $reader;
    protected Writer $writer;

    /**
     * Constructor.
     *
     * @param string|null $input  Input stream path.
     * @param string|null $output Output steam path.
     */
    public function __construct(?string $input = null, ?string $output = null)
    {
        $this->reader = new Reader($input);
        $this->writer = new Writer($output);
    }

    /**
     * Get reader.
     *
     * @return Reader
     */
    public function reader(): Reader
    {
        return $this->reader;
    }

    /**
     * Get writer.
     *
     * @return Writer
     */
    public function writer(): Writer
    {
        return $this->writer;
    }

    /**
     * Confirms if user agrees to prompt as indicated by given text.
     *
     * @param string $text    Eg: `Are you sure?`
     * @param string $default One of `y|n`
     *
     * @return bool
     */
    public function confirm(string $text, string $default = 'y'): bool
    {
        $choice = $this->choice($text, ['y', 'n'], $default, false);

        return strtolower($choice[0] ?? $default) === 'y';
    }

    /**
     * Let user make a choice out of available choices.
     *
     * @param string $text    Prompt text.
     * @param array  $choices Possible choices for user.
     * @param mixed  $default Default value- if not chosen or invalid.
     * @param bool   $case    If user input should be case sensitive.
     *
     * @return mixed User input or default.
     */
    public function choice(string $text, array $choices, $default = null, bool $case = false): mixed
    {
        $this->writer->yellow($text);

        $this->listOptions($choices, $default, false);

        $choice = $this->reader->read($default);

        return $this->isValidChoice($choice, $choices, $case) ? $choice : $default;
    }

    /**
     * Let user make multiple choices out of available choices.
     *
     * @param string $text    Prompt text.
     * @param array  $choices Possible choices for user.
     * @param mixed  $default Default value- if not chosen or invalid.
     * @param bool   $case    If user input should be case sensitive.
     *
     * @return mixed User input or default.
     */
    public function choices(string $text, array $choices, $default = null, bool $case = false): mixed
    {
        $this->writer->yellow($text);

        $this->listOptions($choices, $default, true);

        $choice = $this->reader->read($default);

        if (is_string($choice)) {
            $choice = explode(',', str_replace(' ', '', $choice));
        }

        $valid = [];

        foreach ($choice as $option) {
            if ($this->isValidChoice($option, $choices, $case)) {
                $valid[] = $option;
            }
        }

        return $valid ?: (array) $default;
    }

    /**
     * Prompt user for free input.
     *
     * @param string        $text    Prompt text.
     * @param mixed         $default
     * @param callable|null $fn      The sanitizer/validator for user input
     *                               Any exception message is printed and prompted again.
     * @param int           $retry   How many more times to retry on failure.
     *
     * @return mixed
     */
    public function prompt(string $text, $default = null, ?callable $fn = null, int $retry = 3): mixed
    {
        $error  = 'Invalid value. Please try again!';
        $hidden = func_get_args()[4] ?? false;
        $readFn = ['read', 'readHidden'][(int) $hidden];

        $this->writer->yellow($text)->comment(null !== $default ? " [$default]: " : ': ');

        try {
            $input = $this->reader->{$readFn}($default, $fn);
        } catch (Throwable $e) {
            $input = '';
            $error = $e->getMessage();
        }

        if ($retry > 0 && $input === '') {
            $this->writer->bgRed($error, true);

            return $this->prompt($text, $default, $fn, $retry - 1, $hidden);
        }

        return $input ?? $default;
    }

    /**
     * Prompt user for secret input like password. Currently for unix only.
     *
     * @param string        $text  Prompt text.
     * @param callable|null $fn    The sanitizer/validator for user input
     *                             Any exception message is printed as error.
     * @param int           $retry How many more times to retry on failure.
     *
     * @return mixed
     */
    public function promptHidden(string $text, ?callable $fn = null, int $retry = 3): mixed
    {
        return $this->prompt($text, null, $fn, $retry, true);
    }

    /**
     * Show choices list.
     *
     * @param array $choices Available choices.
     * @param mixed $default
     * @param bool  $multi   Indicates multiple choices.
     *
     * @return self
     */
    protected function listOptions(array $choices, $default = null, bool $multi = false): self
    {
        if (!$this->isAssocChoice($choices)) {
            return $this->promptOptions($choices, $default);
        }

        $maxLen = max(array_map('strlen', array_keys($choices)));

        foreach ($choices as $choice => $desc) {
            $this->writer->eol()->cyan(str_pad("  [$choice]", $maxLen + 6))->comment($desc);
        }

        $label = $multi ? 'Choices (comma separated)' : 'Choice';

        $this->writer->eol()->yellow($label);

        return $this->promptOptions(array_keys($choices), $default);
    }

    /**
     * Show prompt with possible options.
     */
    protected function promptOptions(array $choices, mixed $default): self
    {
        $options = '';

        foreach ($choices as $choice) {
            $style    = in_array($choice, (array) $default) ? 'boldCyan' : 'cyan';
            $options .= "/<$style>$choice</end>";
        }

        $options = ltrim($options, '/');

        $this->writer->colors(" ($options): ");

        return $this;
    }

    /**
     * Check if user choice is one of possible choices.
     */
    protected function isValidChoice(string $choice, array $choices, bool $case): bool
    {
        if ($this->isAssocChoice($choices)) {
            $choices = array_keys($choices);
        }

        $fn = ['\strcasecmp', '\strcmp'][(int) $case];

        foreach ($choices as $option) {
            if ($fn($choice, $option) == 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the choices array is associative.
     */
    protected function isAssocChoice(array $array): bool
    {
        return !empty($array) && array_keys($array) != range(0, count($array) - 1);
    }

    /**
     * Channel method calls to reader/writer.
     */
    public function __call(string $method, array $arguments)
    {
        if (method_exists($this->reader, $method)) {
            return $this->reader->{$method}(...$arguments);
        }

        return $this->writer->{$method}(...$arguments);
    }
}
