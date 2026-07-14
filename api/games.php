<?php

/**
 * GET api/games.php
 *
 * Query params (all optional):
 *   - search:   string, matches title (LIKE)
 *   - genre:    exact match
 *   - platform: exact match
 *
 * Response:
 *   {
 *     "meta": { "total": int, "last_synced_at": string|null, "genres": [...], "platforms": [...] },
 *     "games": [ {...}, ... ]
 *   }
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonError('Method not allowed.', 405);
}

try {
    $service = makeGameService();

    $search = isset($_GET['search']) ? trim((string) $_GET['search']) : null;
    $genre = isset($_GET['genre']) ? trim((string) $_GET['genre']) : null;
    $platform = isset($_GET['platform']) ? trim((string) $_GET['platform']) : null;

    $games = $service->listGames($search, $genre, $platform);
    $meta = $service->getMeta();

    jsonResponse([
        'meta' => $meta,
        'games' => $games,
    ]);
} catch (\Throwable $e) {
    jsonError('Não foi possível carregar o catálogo. Tente novamente em instantes.', 500, $e);
}
