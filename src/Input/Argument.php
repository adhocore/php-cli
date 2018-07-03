<?php

namespace Ahc\Cli\Input;

/**
 * Cli Option.
 *
 * @author  Jitendra Adhikari <jiten.adhikary@gmail.com>
 * @license MIT
 *
 * @link    https://github.com/adhocore/cli
 */
class Argument extends Parameter
{
    protected function parse(string $arg)
    {
        $this->required = $arg[0] === '<';
        $this->variadic = \strpos($arg, '...') !== false;
        $this->name     = $name = \str_replace(['<', '>', '[', ']', '.'], '', $arg);

        // Format is "name:default+value1,default+value2" ('+'' => ' ')!
        if (\strpos($name, ':') !== false) {
            $name                             = \str_replace('+', ' ', $name);
            list($this->name, $this->default) = \explode(':', $name, 2);
        }
    }

    public function default()
    {
        if (!$this->variadic) {
            return $this->default;
        }

        return null === $this->default ? [] : \explode(',', $this->default);
    }
}
