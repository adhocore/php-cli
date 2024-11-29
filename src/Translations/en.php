<?php

/*
 * This file is part of the PHP-CLI package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

return [
    // main
    'arguments'          => 'Arguments',
    'choice'             => 'Choice',
    'choices'            => 'Choices (comma separated)',
    'command'            => 'Command',
    'commandNotFound'    => 'Command %s not found',
    'commandSuggestion'  => 'Did you mean %s?',
    'commands'           => 'Commands',
    'descWithDefault'    => '%s [default: %s]',
    'helpExample'        => '[OPTIONS...] [ARGUMENTS...]',
    'optionHelp'         => 'Legend: <required> [optional] variadic...',
    'options'            => 'Options',
    'promptInvalidValue' => 'Invalid value. Please try again!',
    'showHelp'           => 'Show help',
    'showHelpFooter'     => 'Run `<command> --help` for specific help',
    'showVersion'        => 'Show version',
    'stackTrace'         => 'Stack Trace',
    'thrownIn'           => 'thrown in',
    'thrownAt'           => 'at',
    'usage'              => 'Usage',
    'usageExamples'      => 'Usage Examples',
    'version'            => 'version',
    'verbosityLevel'     => 'Verbosity level',

    // exceptions
    'argumentVariadic'           => 'Only last argument can be variadic',
    'badProgram'                 => 'Bad program could not be started.',
    'commandAlreadyAdded'        => 'Command "%s" already added',
    'commandDoesNotExist'        => 'Command "%s" does not exist',
    'configOptionMissing'        => 'Configuration option value is required',
    'invalidTableRowsType'       => 'Rows must be array of assoc arrays, %s given',
    'optionNotRegistered'        => 'Option "%s" not registered',
    'parameterAlreadyRegistered' => 'The parameter "%s" is already registered',
    'processAlreadyRun'          => 'Process is already running.',
    'procOpenMissing'            => 'Required proc_open could not be found in your PHP setup.',
    'progressbarCurrentMax'      => 'The current (%d) is greater than the total (%d).',
    'progressbarTotalMin'        => 'The progress total must be greater than zero.',
    'styleInvisible'             => 'Built-in styles cannot be invisible',
    'textRequired'               => 'Text required',
    'timeoutOccured'             => 'Timeout occurred, process terminated.',
    'undefinedStyle'             => 'Style "%s" not defined',
    'usingInvalidStyle'          => 'Trying to set empty or invalid style',
];
