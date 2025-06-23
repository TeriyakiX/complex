<?php

return [
    'paths' => ['*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'https://ekbcomplex.netlify.app',
        'http://localhost:5173',
        'http://localhost:5174',
        'http://localhost:5176',
        'https://ekbcomplex.ru'
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
