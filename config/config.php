<?php

/**
 * config.php
 *
 * Central configuration bootstrap.
 * Loads variables from a ".env" file (if present) into getenv()/$_ENV
 * and exposes them as PHP constants used across the application.
 *
 * No external dependency (no composer/vlucas) is used on purpose,
 * to keep the project "pure PHP" as required.
 */

declare(strict_types=1);

/**
 * Very small .env parser.
 * Supports lines like KEY=VALUE, ignores blank lines and comments (#).
 */
function loadEnvFile(string $path): void
{
    if (!is_file($path)) {
        return; // .env is optional; fallback defaults below will be used
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        if (!str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        // Strip optional surrounding quotes
        $value = trim($value, "\"'");

        if ($key !== '' && getenv($key) === false) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }
}

loadEnvFile(dirname(__DIR__) . '/.env');

/**
 * Helper to read an env var with a default fallback.
 */
function env(string $key, mixed $default = null): mixed
{
    $value = getenv($key);

    return $value === false ? $default : $value;
}

// --- Database ---
define('DB_HOST', env('DB_HOST', '127.0.0.1'));
define('DB_PORT', env('DB_PORT', '3306'));
define('DB_NAME', env('DB_NAME', 'gamevault'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));

// --- External API ---
define('API_BASE_URL', env('API_BASE_URL', 'https://www.freetogame.com/api'));

// --- App ---
define('APP_ENV', env('APP_ENV', 'local'));
date_default_timezone_set(env('APP_TIMEZONE', 'America/Sao_Paulo'));

// Never expose PHP errors directly to the browser (per project rules).
ini_set('display_errors', APP_ENV === 'local' ? '1' : '0');
error_reporting(E_ALL);
