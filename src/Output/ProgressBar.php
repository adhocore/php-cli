<?php

/*
 * This file is part of the PHP-CLI package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

namespace Ahc\Cli\Output;

use Ahc\Cli\Helper\Terminal;
use UnexpectedValueException;

use function count;
use function iterator_to_array;
use function min;
use function round;
use function str_repeat;
use function strlen;
use function trim;

/**
 * The Progress provides helpers to display progress output.
 *
 * @author Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 */
class ProgressBar
{
    /**
     * The total number of items involved.
     */
    protected int $total = 0;

    /**
     * The current item that the progress bar represents.
     */
    protected int $current = 0;

    /**
     * The current percentage displayed.
     */
    protected string $currentPercentage = '';

    /**
     * The string length of the bar when at 100%.
     */
    protected int $barStrLen = 0;

    /**
     * Flag indicating whether we are writing the bar for the first time.
     */
    protected bool $firstLine = true;

    /**
     * Current status bar label.
     */
    protected string $label = '';

    /**
     * Options for progress bar.
     */
    private array $options = [
        'pointer'    => '>',
        'loader'     => '=',
        'color'      => 'white',
        'labelColor' => 'white',
    ];

    /**
     * Force a redraw every time.
     */
    protected bool $forceRedraw = false;

    /**
     * If this progress bar ever displayed a label.
     */
    protected bool $hasLabelLine = false;

    protected Writer $writer;

    protected Cursor $cursor;

    protected Terminal $terminal;

    /**
     * If they pass in a total, set the total.
     */
    public function __construct(?int $total = null, ?Writer $writer = null)
    {
        if ($total !== null) {
            $this->total($total);
        }

        $this->writer   = $writer ?: new Writer();
        $this->cursor   = $this->writer->cursor();
        $this->terminal = new Terminal();
    }

    /**
     * Set the total property.
     */
    public function total(int $total): self
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Set progress bar options.
     */
    public function option(string|array $key, ?string $value = null): self
    {
        if (is_string($key)) {
            if (empty($value)) {
                throw new UnexpectedValueException('configuration option value is required');
            }

            $key = [$key => $value];
        }

        $this->options = array_merge($this->options, $key);

        return $this;
    }

    /**
     * Determines the current percentage we are at and re-writes the progress bar.
     *
     * @throws UnexpectedValueException
     *
     * @return void
     */
    public function current(int $current, string $label = '')
    {
        if ($this->total == 0) {
            // Avoid dividing by 0
            throw new UnexpectedValueException('The progress total must be greater than zero.');
        }

        if ($current > $this->total) {
            throw new UnexpectedValueException(sprintf('The current (%d) is greater than the total (%d).', $current, $this->total));
        }

        $this->drawProgressBar($current, $label);

        $this->current = $current;
        $this->label   = $label;
    }

    /**
     * Increments the current position we are at and re-writes the progress bar.
     *
     * @param int $increment The number of items to increment by
     */
    public function advance(int $increment = 1, string $label = '')
    {
        $this->current($this->current + $increment, $label);
    }

    /**
     * Force the progress bar to redraw every time regardless of whether it has changed or not.
     */
    public function forceRedraw(bool $force = true): self
    {
        $this->forceRedraw = $force;

        return $this;
    }

    /**
     * Update a progress bar using an iterable.
     *
     * @param iterable $items    Array or any other iterable object
     * @param callable $callback A handler to run on each item
     */
    public function each($items, callable $callback = null)
    {
        if ($items instanceof \Traversable) {
            $items = iterator_to_array($items);
        }

        if (0 === $total = count($items)) {
            return;
        }

        $this->total($total);

        foreach ($items as $key => $item) {
            $label = $callback ? (string) $callback($item, $key) : '';

            $this->advance(1, $label);
        }
    }

    /**
     * Draw the progress bar, if necessary.
     */
    protected function drawProgressBar(int $current, string $label)
    {
        $percentage = $this->percentageFormatted($current / $this->total);

        if ($this->shouldRedraw($percentage, $label)) {
            $this->writer->colors($this->getProgressBar($current, $label) . '<eol>');
        }

        $this->currentPercentage = $percentage;
    }

    /**
     * Build the progress bar str and return it.
     */
    protected function getProgressBar(int $current, string $label): string
    {
        if ($this->firstLine) {
            // Drop down a line, we are about to
            // re-write this line for the progress bar
            $this->writer->write('');
            $this->firstLine = false;
        }

        // Move the cursor up and clear it to the end
        $lines = $this->hasLabelLine ? 2 : 1;
        $bar   = $this->cursor->up($lines);
        $bar .= $this->cr() . $this->cursor->eraseLine();
        $bar .= $this->getProgressBarStr($current, $label);

        // If this line has a label then set that this progress bar has a label line
        if (strlen($label) > 0) {
            $this->hasLabelLine = true;
        }

        return $bar;
    }

    /**
     * Get the progress bar string, basically:
     * =============>             50% label.
     */
    protected function getProgressBarStr(int $current, string $label): string
    {
        $percentage = $current / $this->total;
        $bar_length = round($this->getBarStrLen() * $percentage);
        $bar        = $this->getBar($bar_length);
        $number     = $this->percentageFormatted($percentage);

        if ($label) {
            $label = $this->labelFormatted($label);
        // If this line doesn't have a label, but we've had one before,
        // then ensure the label line is cleared
        } elseif ($this->hasLabelLine) {
            $label = $this->labelFormatted('');
        }

        $bar   = '<' . $this->options['color'] . '>' . $bar . ' ' . $number . '</end>';
        $label = '<' . $this->options['labelColor'] . '>' . $label . '</end>';

        return trim($bar . $label);
    }

    /**
     * Get the string for the actual bar based on the current length.
     */
    protected function getBar(int $length): string
    {
        $bar     = str_repeat($this->options['loader'], $length);
        $padding = str_repeat(' ', $this->getBarStrLen() - $length);

        return "{$bar}{$this->options['pointer']}{$padding}";
    }

    /**
     * Get the length of the bar string based on the width of the terminal window.
     */
    protected function getBarStrLen(): int
    {
        if (!$this->barStrLen) {
            // Subtract 10 because of the '> 100%' plus some padding, max 100
            $this->barStrLen = max(50, min($this->terminal->width() - 10, 100));
        }

        return $this->barStrLen;
    }

    /**
     * Format the percentage so it looks pretty.
     */
    protected function percentageFormatted(float $percentage): string
    {
        return round($percentage * 100) . '%';
    }

    /**
     * Format the label so it is positioned correctly.
     */
    protected function labelFormatted(string $label): string
    {
        return "\n" . $this->cr() . $this->cursor->eraseLine() . $label;
    }

    /**
     * Determine whether the progress bar has changed and we need to redrew.
     */
    protected function shouldRedraw(string $percentage, string $label): bool
    {
        return $this->forceRedraw || $percentage != $this->currentPercentage || $label != $this->label;
    }

    protected function cr(): string
    {
        return Terminal::isWindows() ? "\r" : '';
    }
}
