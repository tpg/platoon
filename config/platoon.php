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
    | If you have targets that share common settings, you can set a "common"
    | target and override the common settings per target.
    |
    | Use the "build" array to specify any local build tasks you need to run
    | before deployment.
    |
    */
    'targets' => [

        'common' => [
            'host' => 'common.test',
            'port' => 22,
        ],

        'staging' => [
            'host' => 'staging.test',
            'port' => 22,
            'username' => 'ssh-username',
            'root' => '/path/to/application/root',
            'php' => '/usr/bin/php',
            'composer' => '/path/to/composer.phar',
            'branch' => 'master',
            'migrate' => false,
            'assets' => [
                // 'local' => 'remote',
            ],
            'hooks' => [
                'build' => [
                    // place your build tasks here.
                ]
            ],
        ],
    ],
];

