<?php
return [
    'default' => 'sqlite',
    'connections' => [
        'sqlite' => [
            'driver'   => 'sqlite',
            'database' => (defined('LARAVEL_ROOT') ? LARAVEL_ROOT : dirname(__DIR__)) . '/database/database.sqlite',
            'prefix'   => '',
        ],
        'mysql' => [
            'driver'    => 'mysql',
            'host'      => '127.0.0.1',
            'port'      => '',
            'database'  => '',
            'username'  => '',
            'password'  => '',
            'charset'   => 'utf8mb4',
            'prefix'    => '',
        ],
    ],
];
