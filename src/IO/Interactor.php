<?php

namespace Ahc\Cli\IO;

use Ahc\Cli\Input\Reader;
use Ahc\Cli\Output\Writer;

/**
 * Cli Interactor.
 *
 * @author  Jitendra Adhikari <jiten.adhikary@gmail.com>
 * @license MIT
 *
 * @link    https://github.com/adhocore/cli
 */
class Interactor
{
    protected $reader;
    protected $writer;

    /**
     * Constructor.
     *
     * @param string|null $input  Input stream path.
     * @param string|null $output Output steam path.
     */
    public function __construct(string $input = null, string $output = null)
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

        return \strtolower($choice[0] ?? $default) === 'y';
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
    public function choice(string $text, array $choices, $default = null, bool $case = false)
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
    public function choices(string $text, array $choices, $default = null, bool $case = false)
    {
        $this->writer->yellow($text);

        $this->listOptions($choices, $default, true);

        $choice = $this->reader->read($default);

        if (\is_string($choice)) {
            $choice = \explode(',', \str_replace(' ', '', $choice));
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
    public function prompt(string $text, $default = null, callable $fn = null, int $retry = 3)
    {
        $error = 'Invalid value. Please try again!';

        $this->writer->yellow($text)->comment(null !== $default ? " [$default]: " : ': ');

        try {
            $input = $this->reader->read($default, $fn);
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        if ($retry > 0 && (isset($e) || \strlen($input) === 0)) {
            $this->writer->bgRed($error, true);

            return $this->prompt($text, $default, $fn, $retry - 1);
        }

        return $input ?? $default;
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

        $maxLen = \max(\array_map('strlen', \array_keys($choices)));

        foreach ($choices as $choice => $desc) {
            $this->writer->eol()->cyan(\str_pad("  [$choice]", $maxLen + 6))->comment($desc);
        }

        $label = $multi ? 'Choices (comma separated)' : 'Choice';

        $this->writer->eol()->yellow($label);

        return $this->promptOptions(\array_keys($choices), $default);
    }

    /**
     * Show prompt with possible options.
     *
     * @param array $choices
     * @param mixed $default
     *
     * @return self
     */
    protected function promptOptions(array $choices, $default): self
    {
        $options = '';
        $color   = $this->writer->colorizer();

        foreach ($choices as $choice) {
            if (\in_array($choice, (array) $default)) {
                $options .= '/' . $color->boldCyan($choice);
            } else {
                $options .= '/' . $color->cyan($choice);
            }
        }

        $this->writer->raw(' (' . ltrim($options, '/') . '): ');

        return $this;
    }

    /**
     * Check if user choice is one of possible choices.
     *
     * @param string $choice  User choice.
     * @param array  $choices Possible choices.
     * @param bool   $case    If input is case sensitive.
     *
     * @return bool
     */
    protected function isValidChoice($choice, array $choices, bool $case)
    {
        if ($this->isAssocChoice($choices)) {
            $choices = \array_keys($choices);
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
     *
     * @param array $array Choices
     *
     * @return bool
     */
    protected function isAssocChoice(array $array)
    {
        return !empty($array) && \array_keys($array) != \range(0, \count($array) - 1);
    }

    /**
     * Channel method calls to reader/writer.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call(string $method, array $arguments)
    {
        if (\method_exists($this->reader, $method)) {
            return $this->reader->{$method}(...$arguments);
        }

        return $this->writer->{$method}(...$arguments);
    }
}
