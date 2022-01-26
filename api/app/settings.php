<?php

return [
    'settings' => [
        // Slim Settings
        'determineRouteBeforeAppMiddleware' => false,
        'displayErrorDetails' => true,
        'addContentLengthHeader' => false,

        // View settings
        'view' => [
            'template_path' => __DIR__ . '/templates',
            'twig' => [
                'cache' => __DIR__ . '/../cache/twig',
                'debug' => true,
                'auto_reload' => true,
            ],
        ],

        // monolog settings
        'logger' => [
            'name' => 'restAppLegalizacionPrep',
            'path' => __DIR__ . '/../log/restAppLegalizacionPrep.log',
        ],

        // doctrine settings
        'doctrine' => [
            'dev_mode' => true,
            'cache_dir' => __DIR__ . '/../cache/doctrine',
            'cache' => null,
            'metadata_dirs' => [__DIR__ . '/src/entity/generated'],
            'entity_path' => [__DIR__ . '/src/entity'],
            'auto_generate_proxies' => true,
            'proxy_dir' =>  __DIR__.'/../cache/proxies',
            'connection' => [
                'driver' => $_ENV['DB_CONNECTION'],
                'host' => $_ENV['DB_HOST'],
                'port' => $_ENV['DB_PORT'],
                'dbname' => $_ENV['DB_DATABASE'],
                'user' => $_ENV['DB_USERNAME'],
                'password' => $_ENV['DB_PASSWORD'],
                'charset' => $_ENV['DB_CHARSET']
            ]
        ]
    ],
];
