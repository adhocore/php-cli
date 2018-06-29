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

    /**
     * Magically set methods.
     *
     * @param string $name Like `red`, `bgRed`, 'bold', `error` etc
     *
     * @return self
     */
    public function __get($name)
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
     * @return void
     */
    public function write($text, $eol = false)
    {
        list($method, $this->method) = [$this->method ?: 'line', ''];

        $stream = \stripos($method, 'error') !== false ? \STDERR : \STDOUT;

        \fwrite($stream, Color::{$method}($text, [], $eol));
    }
}
