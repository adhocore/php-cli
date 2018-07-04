<?php

namespace Ahc\Cli\Input;

/**
 * Cli Reader.
 *
 * @author  Jitendra Adhikari <jiten.adhikary@gmail.com>
 * @license MIT
 *
 * @link    https://github.com/adhocore/cli
 */
class Reader
{
    protected $stream;

    public function __construct(string $path = null)
    {
        $this->stream = $path ? \fopen($path, 'w') : \STDIN;
    }

    /**
     * Read a line from configured stream (or terminal).
     *
     * @param mixed         $default The default value.
     * @param callable|null $fn      The validator/sanitizer callback.
     *
     * @return mixed
     *
     * @throws \Exception When value is not valid.
     */
    public function read($default = null, callable $fn = null)
    {
        $in = \trim(\fgets(\STDIN));

        if ('' === $in && null !== $default) {
            return $default;
        }

        return $fn ? $fn($in) : $in;
    }
}
