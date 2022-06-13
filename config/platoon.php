<?php

return [

    'default' => 'staging',

    'repo' => 'repo.git',

    'targets' => [

        'staging' => [
            'host' => 'inferno.thepublicgood.dev',
            'port' => 5252,
            'username' => 'ubuntu',
            'path' => '/opt/platoon',
            'php' => '/usr/bin/php',
            'composer' => '/opt/platoon/composer.phar',
            'branch' => 'master',
            'migrate' => false,
        ],
    ],
];

