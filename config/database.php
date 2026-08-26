<?php

declare(strict_types=1);

return [
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'port' => getenv('DB_PORT') ?: '3306',
    'database' => getenv('DB_NAME') ?: 'jashei',
    'username' => getenv('DB_USER') ?: 'jashei_user',
    'password' => getenv('DB_PASS') ?: 'ChangeThisPassword',
    'charset' => 'utf8mb4',
];
