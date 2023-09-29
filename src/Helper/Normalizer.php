<?php

/*
 * This file is part of the PHP-CLI package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

namespace Ahc\Cli\Helper;

use Ahc\Cli\Input\Option;
use Ahc\Cli\Input\Parameter;

/**
 * Internal value &/or argument normalizer. Has little to no usefulness as public api.
 * Currently used by Input\Parser. To "normalize" values before setting them to parameters.
 *
 * @author  Jitendra Adhikari <jiten.adhikary@gmail.com>
 * @license MIT
 *
 * @link    https://github.com/adhocore/cli
 */
class Normalizer
{

    /**
     * Normalizes value as per context and runs thorugh filter if possible.
     * 
     * @param Parameter $parameter
     * @param string|null $value
     * 
     * @return mixed
     */
    public function normalizeValue(Parameter $parameter, ?string $value = null): mixed
    {
        if ($parameter instanceof Option && $parameter->bool()) {
            return !$parameter->default();
        }

        if (null === $value) {
            return $parameter->required() ? null : true;
        }

        return $parameter->filter($value);
    }
    
}
