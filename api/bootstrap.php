<?php

/**
 * bootstrap.php
 *
 * Shared setup for every internal endpoint (api/*.php):
 * - loads configuration
 * - registers a tiny PSR-4-like autoloader (no Composer required)
 * - forces JSON output headers
 * - wires up GameService with its dependencies
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

spl_autoload_register(function (string $class): void {
    // Map "Config\Xxx" -> config/Xxx.php and "Src\Xxx" -> src/Xxx.php
    $map = [
        'Config\\' => __DIR__ . '/../config/',
        'Src\\' => __DIR__ . '/../src/',
    ];

    foreach ($map as $prefix => $baseDir) {
        if (str_starts_with($class, $prefix)) {
            $relative = substr($class, strlen($prefix));
            $file = $baseDir . str_replace('\\', '/', $relative) . '.php';

            if (is_file($file)) {
                require_once $file;
            }

            return;
        }
    }
});

header('Content-Type: application/json; charset=utf-8');

use Config\Database;
use Src\FreeToGameClient;
use Src\GameRepository;
use Src\GameService;

/**
 * Build a ready-to-use GameService instance.
 */
function makeGameService(): GameService
{
    $pdo = Database::getConnection();

    return new GameService(
        new FreeToGameClient(),
        new GameRepository($pdo)
    );
}

/**
 * Send a JSON response and stop execution.
 */
function jsonResponse(mixed $data, int $statusCode = 200): never
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Send a standardized error response. The real exception message is never
 * leaked to the client in production; it is only echoed when APP_ENV=local.
 */
function jsonError(string $publicMessage, int $statusCode, ?\Throwable $e = null): never
{
    $payload = ['error' => $publicMessage];

    if ($e !== null && APP_ENV === 'local') {
        $payload['debug'] = $e->getMessage();
    }

    jsonResponse($payload, $statusCode);
}
