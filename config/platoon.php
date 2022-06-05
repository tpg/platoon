<?php

return [

    'targets' => [
        'staging' => [
            'host' => 'staging.test',
            'port' => 22,
            'path' => '/path/to/application',
            'php' => '/usr/bin/php8.1',
            'branch' => 'main',
        ],
    ],

];

