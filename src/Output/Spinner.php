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

use BadMethodCallException;
use InvalidArgumentException;

use function call_user_func_array;
use function count;
use function in_array;
use function sprintf;

/**
 * The Spinner provides helpers to display spinner progress to output.
 *
 * @author Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *
 * @method $this each(iterable $items, ?callable $callback) Update a progress bar using an iterable.
 * @method void  finish() Force end of progress
 * @method $this option(string|array $key, ?string $value = null) Set the spinner option
 * @method $this total(int $total) Set the total property.
 */
class Spinner
{
    /**
     * @var ProgressBar
     */
    private $progressBar;

    /**
     * The current step of spinner.
     */
    private int $step = 0;

    /**
     * List of methods of the progress bar directly accessible in the spinner.
     * The user should not have too much freedom
     */
    private array $availableMethods = ['each',  'finish', 'option', 'total'];

    /**
     * Animated indicator characters
     */
    protected array $indicators = ['⠏', '⠛', '⠹', '⢸', '⣰', '⣤', '⣆', '⡇'];

    /**
     * Spinner message.
     */
    protected string $message = '';

    /**
     * Constructor
     */
    public function __construct(protected int $max = 0, ?ProgressBar $progressBar = null)
    {
        $this->progressBar = $progressBar ?: new ProgressBar();
        $this->progressBar->total($max);
        $this->progressBar->barWidth(1);
        $this->progressBar->option('labelPosition', 'right');
    }

    /**
     * Dynamic call of progress bar methods like `options` for example
     */
    public function __call(string $name, array $arguments)
    {
        // The user should not have too much freedom
        if (in_array($name, $this->availableMethods, true)) {
            $result = call_user_func_array([$this->progressBar, $name], $arguments);

            if ($result instanceof ProgressBar) {
                return $this;
            }

            return $result;
        }

        throw new BadMethodCallException(sprintf("Undefined method %s", $name));
    }

    /**
     * Increments the current position we are at and re-writes the spinner.
     */
    public function advance(int $step = 1, string $label = ''): void
    {
        $this->step += $step;

        if ($this->step === $this->max) {
            $loader = '✔';
        } else {
            $loader = $this->indicators[$this->step % count($this->indicators)];
        }

        $this->progressBar->option([
            'loader'  => $loader,
            'pointer' => '' // We delete the pointer, the spinner does not need it
        ]);
        $this->progressBar->advance($step, $label ?: $this->message);
    }

    /**
     * Sets the animated indicator characters
     */
    public function indicators(array $indicators): self
    {
        if (2 > count($indicators)) {
            throw new InvalidArgumentException('Must have at least 2 indicator value characters.');
        }

        $this->indicators = $indicators;

        return $this;
    }

    /**
     * Set a global spinner message.
     * If the label is not passed when calling the `advance` method, this message will be used
     */
    public function message(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get the original progress bar
     */
    public function getProgressBar(): ProgressBar
    {
        return $this->progressBar;
    }
}
