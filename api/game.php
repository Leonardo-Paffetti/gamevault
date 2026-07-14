<?php

/**
 * GET api/game.php?id=123
 *
 * Returns the full detail of a single game (internal database id).
 * Best-effort enrichment with GET /game?id= from FreeToGame when available.
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonError('Method not allowed.', 405);
}

$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : false;

if ($id === false || $id === null) {
    jsonError('Parâmetro "id" inválido.', 400);
}

try {
    $service = makeGameService();
    $game = $service->getGameDetail((int) $id);

    if ($game === null) {
        jsonError('Jogo não encontrado.', 404);
    }

    jsonResponse(['game' => $game]);
} catch (\Throwable $e) {
    jsonError('Não foi possível carregar os detalhes do jogo.', 500, $e);
}
