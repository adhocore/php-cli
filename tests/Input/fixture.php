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
    'options' => [
        'space req' => [
            'cmd'    => '-v --virtual <req>',
            'expect' => [
                'long'     => '--virtual',
                'short'    => '-v',
                'required' => true,
                'variadic' => false,
                'name'     => 'virtual',
                'aname'    => 'virtual',
            ],
        ],
        'comma opt' => [
            'cmd'    => '-f,--fruit [opt]',
            'expect' => [
                'long'     => '--fruit',
                'short'    => '-f',
                'required' => false,
                'variadic' => false,
                'name'     => 'fruit',
                'aname'    => 'fruit',
            ],
        ],
        'pipe ...' => [
            'cmd'    => '-a|--apple [opt...]',
            'expect' => [
                'long'     => '--apple',
                'short'    => '-a',
                'required' => false,
                'variadic' => true,
                'name'     => 'apple',
                'aname'    => 'apple',
                'bool'     => false,
            ],
        ],
        '--no' => [
            'cmd'    => '-n|--no-shit',
            'expect' => [
                'long'     => '--no-shit',
                'short'    => '-n',
                'required' => false,
                'variadic' => false,
                'name'     => 'shit',
                'aname'    => 'shit',
                'bool'     => true,
            ],
        ],
        '--with' => [
            'cmd'    => '-w|--with-this',
            'expect' => [
                'long'     => '--with-this',
                'short'    => '-w',
                'required' => false,
                'variadic' => false,
                'name'     => 'this',
                'aname'    => 'this',
                'default'  => false,
                'bool'     => true,
            ],
        ],
        'camel case' => [
            'cmd'    => '-C|--camel-case',
            'expect' => [
                'long'     => '--camel-case',
                'short'    => '-C',
                'required' => false,
                'variadic' => false,
                'name'     => 'camel-case',
                'aname'    => 'camelCase',
                'default'  => false,
            ],
        ],
    ],
    'argvs' => [
        [
            'argv'   => [],
            'throws' => [\RuntimeException::class, 'Option "--virtual" is required'],
        ],
        [
            'argv'   => [''],
            'throws' => [\RuntimeException::class, 'Option "--virtual" is required'],
        ],
        [
            'argv'   => ['-x', 1],
            'throws' => [\RuntimeException::class, 'Option "--virtual" is required'],
        ],
        [
            'argv'   => ['-x', 1],
            'throws' => [\RuntimeException::class, 'Option "--virtual" is required'],
        ],
        [
            'argv'   => ['-v'],
            'throws' => [\RuntimeException::class, 'Option "--virtual" is required'],
        ],
        [
            'argv'   => ['--virtual'],
            'throws' => [\RuntimeException::class, 'Option "--virtual" is required'],
        ],
    ],
];
