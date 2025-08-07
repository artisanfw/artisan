<?php

return [
    'environment' => Artisan\Routing\Entities\ApiOptions::ENV_DEVELOPMENT,
    'db' => [
        'driver' => 'pdo_mysql',
        'host' => 'localhost',
        'dbname' => '',
        'user' => '',
        'password' => '',
        'model_paths' => [
            PROJECT_DIR . '/src/Models'
        ],
    ],
    'email' => [
        'dsn' => '',
    ],
    'jwt' => [
        'algorithm' => \Artisan\Services\JWT::ALG_HS256,
    ],
    'logs' => [
        'path' => '/var/log/artisan/',
        'extension' => 'log',
        'level' => \Monolog\Level::Debug,
        'rotation' => true,
        'max_files' => 2,
    ],
    'twig' => [
        'paths' => [
            PROJECT_DIR . '/src/Templates',
        ],
        'functions' => [
            \Artisan\Services\Language::getTwigFunction(),
        ],
    ],
    'i18n' => [
        'locale' => 'en',
        'path' => PROJECT_DIR . '/locales',
        'file_format' => \Artisan\Services\Language::YAML_FORMAT,
        'wrapper' => \Artisan\Services\Language::WRAPPER_CURLY_BRACES,
        'default_domain' => 'messages+intl-icu',
    ],
    'token-manager' => [
        'types' => [
            \Api\Controllers\AccountsController::TOKEN_TYPE_EMAIL_VALIDATION,
            \Api\Controllers\AccountsController::TOKEN_TYPE_ACCOUNT_LOGIN,
        ],
        'default_code_length' => 6,
        'charset' => [
            'letters' => '',
            'numbers' => '1234567890',
        ],
        'repository' => \Artisan\TokenManager\Repositories\DoctrineRepository::class,
    ],
    'geoip' => [
        'license_key' => '',
        'mmdb' => PROJECT_DIR. '/geodb/GeoLite2-City.mmdb',
    ],
    'accounts' => [
        'token_exp_days' => 30,
        'token_salt' => '',
        'pwd_min_length' => 4,
        'pwd_mix_upper_lower_case' => false,
        'pwd_numbers' => false,
        'pwd_symbols' => false,
        //Remember to update 'invalid_password' message in the locale yaml
    ]
];