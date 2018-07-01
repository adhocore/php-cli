<?php

namespace Ahc\Cli;

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
    /** @var string Write method to be relayed to Colorizer */
    protected $method;

    /** @var Color */
    protected $colorizer;

    public function __construct()
    {
        $this->colorizer = new Color;
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

        $stream = \stripos($method, 'error') !== false ? \STDERR : \STDOUT;

        if ($method === 'eol') {
            \fwrite($stream, PHP_EOL);
        } else {
            \fwrite($stream, $this->colorizer->{$method}($text, [], $eol));
        }

        return $this;
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
        $this->method = $method;

        return $this->write($arguments[0] ?? '', $arguments[1] ?? false);
    }
}
