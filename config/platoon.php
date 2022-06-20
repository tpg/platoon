<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default target
    |--------------------------------------------------------------------------
    |
    | The default target if you have more than one deployment target.
    | This default will be used if no targets are specified during deployment.
    |
    */

    'default' => 'staging',

    /*
    |--------------------------------------------------------------------------
    | Git repository
    |--------------------------------------------------------------------------
    |
    | The Git repository to clone from. You'll need to make sure that you
    | can clone this repo to all targets before deploying.
    |
    */

    'repo' => 'repo.git',

    /*
    |--------------------------------------------------------------------------
    | Deployment targets
    |--------------------------------------------------------------------------
    |
    | This is the list of deployment targets. You can specify the target
    | you want to deploy to when using the "platoon:deploy" artisan command.
    |
    */
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

