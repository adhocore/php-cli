<?php

namespace Ahc\Cli\Helper;

use Ahc\Cli\Output\Writer;

/**
 * Cli Interactor.
 *
 * @author  Jitendra Adhikari <jiten.adhikary@gmail.com>
 * @license MIT
 *
 * @link    https://github.com/adhocore/cli
 */
class Interactor extends Writer
{
    public function confirm(string $text, string $default = 'y'): bool
    {
        $choice = $this->choice($text, ['y', 'n'], $default);

        return \strtolower($choice[0] ?? $default) === 'y';
    }

    public function choice(string $text, array $choices, $default = null, bool $case = false)
    {
        $choice = $this->yellow($text)->listOptions($choices, $default, false)->read($default);

        return $this->isValidChoice($choice, $choices, $case) ? $choice : $default;
    }

    public function choices(string $text, array $choices, $default = null, bool $case = false)
    {
        $valid  = [];
        $choice = $this->yellow($text)->listOptions($choices, $default, true)->read($default);

        if (\is_string($choice)) {
            $choice = \explode(',', \str_replace(' ', '', $choice));
        }

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
        $this->yellow($text)->comment(null !== $default ? " [$default]: " : ': ');

        try {
            $input = $this->read($default, $fn);
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        if ($retry > 0 && (isset($e) || \strlen($input) === 0)) {
            return $this->bgRed($error, true)->prompt($text, $default, $fn, $retry - 1);
        }

        return $input ?? $default;
    }

    public function read($default = null, callable $fn = null)
    {
        $in = \trim(\fgets(\STDIN));

        if ('' === $in && null !== $default) {
            return $default;
        }

        return $fn ? $fn($in) : $in;
    }

    protected function listOptions(array $choices, $default = null, bool $multi = false)
    {
        if (!$this->isAssocChoice($choices)) {
            return $this->promptOptions($choices, $default);
        }

        $maxLen = \max(\array_map('strlen', \array_keys($choices)));

        foreach ($choices as $choice => $desc) {
            $this->eol()->cyan(\str_pad("  [$choice]", $maxLen + 6))->comment($desc);
        }

        $label = $multi ? 'Choices (comma separated)' : 'Choice';

        return $this->eol()->yellow($label)->promptOptions(\array_keys($choices), $default);
    }

    protected function promptOptions(array $choices, $default): self
    {
        $options = \implode('/', $choices);

        foreach ((array) $default as $value) {
            $options = \str_replace($value, \strtoupper($value), $options);
        }

        return $this->cyan(' (' . $options . '): ');
    }

    protected function isValidChoice($choice, array $choices, bool $case)
    {
        if ($this->isAssocChoice($choices)) {
            $choices = \array_keys($choices);
        }

        foreach ($choices as $option) {
            if ($case && $choice == $option) {
                return true;
            }

            if (!$case && \strcasecmp($choice, $option) == 0) {
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
}
