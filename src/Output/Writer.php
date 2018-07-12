<?php

namespace Ahc\Cli\Output;

/**
 * Cli Writer.
 *
 * @author  Jitendra Adhikari <jiten.adhikary@gmail.com>
 * @license MIT
 *
 * @link    https://github.com/adhocore/cli
 */
class Writer
{
    /** @var resource Output file handle */
    protected $stream;

    /** @var resource Error output file handle */
    protected $eStream;

    /** @var string Write method to be relayed to Colorizer */
    protected $method;

    /** @var Color */
    protected $colorizer;

    /** @var Cursor */
    protected $cursor;

    public function __construct(string $path = null, Color $colorizer = null)
    {
        if ($path) {
            $path = \fopen($path, 'w');
        }

        $this->stream  = $path ?: \STDOUT;
        $this->eStream = $path ?: \STDERR;

        $this->cursor    = new Cursor;
        $this->colorizer = $colorizer ?? new Color;
    }

    /**
     * Get Colorizer.
     *
     * @return Color
     */
    public function colorizer(): Color
    {
        return $this->colorizer;
    }

    /**
     * Magically set methods.
     *
     * @param string $name Like `red`, `bgRed`, 'bold', `error` etc
     *
     * @return self
     */
    public function __get(string $name): self
    {
        if (\strpos($this->method, $name) === false) {
            $this->method .= $this->method ? \ucfirst($name) : $name;
        }

        return $this;
    }

    /**
     * Write the formatted text to stdout or stderr.
     *
     * @param string $text
     * @param bool   $eol
     *
     * @return self
     */
    public function write(string $text, bool $eol = false): self
    {
        list($method, $this->method) = [$this->method ?: 'line', ''];

        $text  = $this->colorizer->{$method}($text, []);
        $error = \stripos($method, 'error') !== false;

        if ($eol) {
            $text .= \PHP_EOL;
        }

        return $this->doWrite($text, $error);
    }

    /**
     * Really write to the stream.
     *
     * @param  string $text
     * @param  bool   $error
     *
     * @return self
     */
    protected function doWrite(string $text, bool $error = false): self
    {
        $stream = $error ? $this->eStream : $this->stream;

        \fwrite($stream, $text);

        return $this;
    }

    /**
     * Write EOL n times.
     *
     * @param int $n
     *
     * @return self
     */
    public function eol(int $n = 1): self
    {
        return $this->doWrite(\str_repeat(PHP_EOL, \max($n, 1)));
    }

    /**
     * Write raw text (as it is).
     *
     * @param string $text
     * @param bool   $error
     *
     * @return self
     */
    public function raw($text, bool $error = false): self
    {
        return $this->doWrite((string) $text, $error);
    }

    /**
     * Write to stdout or stderr magically.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return self
     */
    public function __call(string $method, array $arguments): self
    {
        if (\method_exists($this->cursor, $method)) {
            return $this->doWrite($this->cursor->{$method}(...$arguments));
        }

        $this->method = $method;

        return $this->write(...$arguments);
    }
}
