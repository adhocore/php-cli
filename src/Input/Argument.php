<?php

/*
 * This file is part of the PHP-CLI package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

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
    /**
     * {@inheritdoc}
     */
    protected function parse(string $arg)
    {
        $this->name = $name = \str_replace(['<', '>', '[', ']', '.'], '', $arg);

        // Format is "name:default+value1,default+value2" ('+' => ' ')!
        if (\strpos($name, ':') !== false) {
            $name                         = \str_replace('+', ' ', $name);
            [$this->name, $this->default] = \explode(':', $name, 2);
        }

        $this->prepDefault();
    }

    protected function prepDefault()
    {
        if ($this->variadic && $this->default && !\is_array($this->default)) {
            $this->default = \explode(',', $this->default, 2);
        }
    }
}
