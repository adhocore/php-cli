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

    public function __construct(string $input = null, string $output = null)
    {
        $this->reader = new Reader($input);
        $this->writer = new Writer($output);
    }

    public function confirm(string $text, string $default = 'y'): bool
    {
        $choice = $this->choice($text, ['y', 'n'], $default);

        return \strtolower($choice[0] ?? $default) === 'y';
    }

    public function choice(string $text, array $choices, $default = null, bool $case = false)
    {
        $this->writer->yellow($text);

        $this->listOptions($choices, $default, false);

        $choice = $this->reader->read($default);

        return $this->isValidChoice($choice, $choices, $case) ? $choice : $default;
    }

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

    protected function promptOptions(array $choices, $default): self
    {
        $options = \implode('/', $choices);

        foreach ((array) $default as $value) {
            $options = \str_replace($value, \strtoupper($value), $options);
        }

        $this->writer->cyan(' (' . $options . '): ');

        return $this;
    }

    protected function isValidChoice($choice, array $choices, bool $case)
    {
        if ($this->isAssocChoice($choices)) {
            $choices = \array_keys($choices);
        }

        $fn = ['\strcmp', '\strcasecmp'][(int) $case];

        foreach ($choices as $option) {
            if ($fn($choice, $option) == 0) {
                return true;
            }
        }

        return false;
    }

    protected function isAssocChoice(array $array)
    {
        if (empty($array)) {
            return false;
        }

        return \array_keys($array) != \range(0, \count($array) - 1);
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
