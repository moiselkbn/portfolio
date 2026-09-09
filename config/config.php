<?php

declare(strict_types=1);

/**
 * Credentials live in .env (git-ignored) so they never reach the repository.
 * A tiny parser keeps the project dependency-free.
 */
function loadEnv(string $path): void
{
    if (!is_readable($path)) {
        throw new RuntimeException('Missing .env file — copy .env.example to .env.');
    }

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
        $_ENV[trim($key)] = trim($value);
    }
}

loadEnv(dirname(__DIR__) . '/.env');

define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_PORT', $_ENV['DB_PORT'] ?? '3306');
define('DB_NAME', $_ENV['DB_NAME'] ?? '');
define('DB_USER', $_ENV['DB_USER'] ?? '');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');

// Show errors locally, hide (and log) them in production.
if (APP_ENV === 'local') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}
