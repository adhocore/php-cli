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
 *
 * @author  Jitendra Adhikari <jiten.adhikary@gmail.com>
 * @license MIT
 *
 * @link    https://github.com/adhocore/cli
 */
class Normalizer
{
    /**
     * Normalize argv args. Like splitting `-abc` and `--xyz=...`.
     *
     * @param array $args
     *
     * @return array
     */
    public function normalizeArgs(array $args): array
    {
        $normalized = [];

        foreach ($args as $arg) {
            if (\preg_match('/^\-\w=/', $arg)) {
                $normalized = \array_merge($normalized, explode('=', $arg));
            } elseif (\preg_match('/^\-\w{2,}/', $arg)) {
                $splitArg   = \implode(' -', \str_split(\ltrim($arg, '-')));
                $normalized = \array_merge($normalized, \explode(' ', '-' . $splitArg));
            } elseif (\preg_match('/^\-\-([^\s\=]+)\=/', $arg)) {
                $normalized = \array_merge($normalized, explode('=', $arg));
            } else {
                $normalized[] = $arg;
            }
        }

        return $normalized;
    }

    /**
     * Normalizes value as per context and runs thorugh filter if possible.
     *
     * @param Parameter   $parameter
     * @param string|null $value
     *
     * @return mixed
     */
    public function normalizeValue(Parameter $parameter, string $value = null)
    {
        if ($parameter instanceof Option && $parameter->bool()) {
            return !$parameter->default();
        }

        if ($parameter->variadic()) {
            return (array) $value;
        }

        if (null === $value) {
            return $parameter->required() ? null : true;
        }

        return $parameter->filter($value);
    }
}
