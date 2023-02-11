<?php

/*
 * This file is part of the PHP-CLI package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

require_once __DIR__ . '/../vendor/autoload.php';

defined('RUNNING_TEST') || define('RUNNING_TEST', 1);
defined('STDIN') || define('STDIN', fopen('php://stdin', 'r'));
