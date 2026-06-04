<?php
declare(strict_types=1);

return [
    'env'  => getenv('APP_ENV')  ?: 'production',
    'name' => getenv('APP_NAME') ?: 'DevLog',

    'db' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => (int)(getenv('DB_PORT') ?: 3306),
        'name' => getenv('DB_NAME') ?: 'devlog',
        'user' => getenv('DB_USER') ?: 'devlog',
        'pass' => getenv('DB_PASS') ?: '',
    ],
];
