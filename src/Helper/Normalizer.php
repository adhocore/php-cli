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

use function array_merge;
use function explode;
use function implode;
use function ltrim;
use function preg_match;
use function str_split;

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
     */
    public function normalizeArgs(array $args): array
    {
        $normalized = [];

        foreach ($args as $arg) {
            if (preg_match('/^\-\w=/', $arg)) {
                $normalized = array_merge($normalized, explode('=', $arg));
            } elseif (preg_match('/^\-\w{2,}/', $arg)) {
                $splitArg   = implode(' -', str_split(ltrim($arg, '-')));
                $normalized = array_merge($normalized, explode(' ', '-' . $splitArg));
            } elseif (preg_match('/^\-\-([^\s\=]+)\=/', $arg)) {
                $normalized = array_merge($normalized, explode('=', $arg));
            } else {
                $normalized[] = $arg;
            }
        }

        return $normalized;
    }

    /**
     * Normalizes value as per context and runs thorugh filter if possible.
     */
    public function normalizeValue(Parameter $parameter, ?string $value = null): mixed
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
