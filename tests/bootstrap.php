<?php

namespace Ahc\Cli;

function _exit($code = 0)
{
    echo "exit($code)";
}

require_once __DIR__ . '/../vendor/autoload.php';
