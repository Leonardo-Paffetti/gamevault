<?php

/**
 * POST api/sync.php
 *
 * Triggers a full synchronization against the FreeToGame API.
 * Inserts new games and updates existing ones (ON DUPLICATE KEY UPDATE),
 * so it never creates duplicated records.
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed. Use POST.', 405);
}

try {
    $service = makeGameService();
    $result = $service->syncCatalog();
    $meta = $service->getMeta();

    jsonResponse([
        'message' => 'Sincronização concluída com sucesso.',
        'synced' => $result['synced'],
        'total' => $result['total'],
        'meta' => $meta,
    ]);
} catch (\Throwable $e) {
    jsonError('A API do FreeToGame está indisponível no momento. Tente novamente mais tarde.', 502, $e);
}
