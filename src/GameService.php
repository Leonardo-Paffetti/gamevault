<?php

declare(strict_types=1);

namespace Src;

/**
 * GameService
 *
 * Single responsibility: business rules that coordinate the external
 * API client and the local repository. Controllers (api/*.php) should
 * only ever talk to this class, never to FreeToGameClient or
 * GameRepository directly.
 */
final class GameService
{
    public function __construct(
        private readonly FreeToGameClient $client,
        private readonly GameRepository $repository
    ) {
    }

    /**
     * Pull every game from FreeToGame and upsert them into the database.
     *
     * @return array{synced: int, total: int}
     */
    public function syncCatalog(): array
    {
        $games = $this->client->fetchAllGames();

        $synced = 0;

        foreach ($games as $rawGame) {
            $normalized = $this->normalizeListItem($rawGame);

            if ($normalized === null) {
                continue; // Skip malformed entries instead of failing the whole sync.
            }

            $this->repository->upsert($normalized);
            $synced++;
        }

        return [
            'synced' => $synced,
            'total' => $this->repository->count(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listGames(?string $search, ?string $genre, ?string $platform): array
    {
        return $this->repository->findAll($search, $genre, $platform);
    }

    /**
     * Get full detail for a single game. Tries the local database first;
     * if the local record lacks a description, enriches it from the API.
     *
     * @return array<string, mixed>|null
     */
    public function getGameDetail(int $id): ?array
    {
        $game = $this->repository->findById($id);

        if ($game === null) {
            return null;
        }

        // Enrich with a longer description straight from the API when possible.
        try {
            $fresh = $this->client->fetchGameById((int) $game['external_id']);

            if (!empty($fresh['description'])) {
                $game['description'] = $fresh['description'];
            }

            if (!empty($fresh['minimum_system_requirements'])) {
                $game['minimum_system_requirements'] = $fresh['minimum_system_requirements'];
            }
        } catch (\Throwable) {
            // API enrichment is best-effort; local data is still returned.
        }

        return $game;
    }

    public function getMeta(): array
    {
        return [
            'total' => $this->repository->count(),
            'last_synced_at' => $this->repository->lastSyncedAt(),
            'genres' => $this->repository->distinctGenres(),
            'platforms' => $this->repository->distinctPlatforms(),
        ];
    }

    /**
     * Normalize one raw item from GET /games into our table shape.
     *
     * @param array<string, mixed> $raw
     * @return array<string, mixed>|null
     */
    private function normalizeListItem(array $raw): ?array
    {
        if (empty($raw['id']) || empty($raw['title'])) {
            return null;
        }

        return [
            'external_id' => (int) $raw['id'],
            'title' => (string) $raw['title'],
            'thumbnail' => $raw['thumbnail'] ?? null,
            'short_description' => $raw['short_description'] ?? null,
            'game_url' => $raw['game_url'] ?? null,
            'genre' => $raw['genre'] ?? null,
            'platform' => $raw['platform'] ?? null,
            'publisher' => $raw['publisher'] ?? null,
            'developer' => $raw['developer'] ?? null,
            'release_date' => $this->normalizeDate($raw['release_date'] ?? null),
            'freetogame_profile_url' => $raw['freetogame_profile_url'] ?? null,
        ];
    }

    private function normalizeDate(?string $date): ?string
    {
        if ($date === null || $date === '') {
            return null;
        }

        $timestamp = strtotime($date);

        return $timestamp === false ? null : date('Y-m-d', $timestamp);
    }
}
