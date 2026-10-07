<?php

/**
 * config/database.php — PDO Connection Configuration
 * Al Amin's Math Care | alaminmathcare.com
 *
 * ⚠️  Change credentials before deploying to production cPanel.
 *     Set file permission to 640 on Linux server:
 *     chmod 640 config/database.php
 */

declare(strict_types=1);

if (getenv('DB_PASS') === false && is_file(dirname(__DIR__) . '/.env')) {
    $lines = file(dirname(__DIR__) . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            putenv(trim($parts[0]) . '=' . trim($parts[1], " \t\n\r\0\x0B\"'"));
        }
    }
}

// Smart Port Detection: Default to 3307 for local Windows dev, 3306 for Linux/cPanel production
$defaultPort = (PHP_OS_FAMILY === 'Windows') ? '3307' : '3306';

return [
    'host'     => getenv('DB_HOST')     ?: '127.0.0.1',
    'port'     => getenv('DB_PORT')     ?: $defaultPort,
    'dbname'   => getenv('DB_NAME')     ?: 'alaminmathcare',
    'username' => getenv('DB_USER')     ?: 'root',
    'password' => getenv('DB_PASS')     ?: '',
    'charset'  => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',

    // PDO Options — Security & Performance
    'options' => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,   // ✅ Real prepared statements (prevents SQL injection)
        PDO::ATTR_PERSISTENT         => false,   // No persistent connections (prevent stale connections)
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
    ],
];
