<?php

declare(strict_types=1);

namespace Src;

use PDO;

/**
 * GameRepository
 *
 * Single responsibility: persistence for the "games" table.
 * This is the ONLY class allowed to write SQL in the whole project.
 */
final class GameRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * Insert a game or update it if the external_id already exists.
     *
     * @param array<string, mixed> $game Normalized game data.
     */
    public function upsert(array $game): void
    {
        $sql = 'INSERT INTO games (
                    external_id, title, thumbnail, short_description, game_url,
                    genre, platform, publisher, developer, release_date,
                    freetogame_profile_url, created_at, updated_at
                ) VALUES (
                    :external_id, :title, :thumbnail, :short_description, :game_url,
                    :genre, :platform, :publisher, :developer, :release_date,
                    :freetogame_profile_url, NOW(), NOW()
                )
                ON DUPLICATE KEY UPDATE
                    title = VALUES(title),
                    thumbnail = VALUES(thumbnail),
                    short_description = VALUES(short_description),
                    game_url = VALUES(game_url),
                    genre = VALUES(genre),
                    platform = VALUES(platform),
                    publisher = VALUES(publisher),
                    developer = VALUES(developer),
                    release_date = VALUES(release_date),
                    freetogame_profile_url = VALUES(freetogame_profile_url),
                    updated_at = NOW()';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':external_id' => $game['external_id'],
            ':title' => $game['title'],
            ':thumbnail' => $game['thumbnail'],
            ':short_description' => $game['short_description'],
            ':game_url' => $game['game_url'],
            ':genre' => $game['genre'],
            ':platform' => $game['platform'],
            ':publisher' => $game['publisher'],
            ':developer' => $game['developer'],
            ':release_date' => $game['release_date'],
            ':freetogame_profile_url' => $game['freetogame_profile_url'],
        ]);
    }

    /**
     * Find games applying optional search + filters.
     *
     * @return array<int, array<string, mixed>>
     */
    public function findAll(?string $search, ?string $genre, ?string $platform): array
    {
        $sql = 'SELECT * FROM games WHERE 1 = 1';
        $params = [];

        if ($search !== null && $search !== '') {
            $sql .= ' AND title LIKE :search';
            $params[':search'] = '%' . $search . '%';
        }

        if ($genre !== null && $genre !== '') {
            $sql .= ' AND genre = :genre';
            $params[':genre'] = $genre;
        }

        if ($platform !== null && $platform !== '') {
            $sql .= ' AND platform = :platform';
            $params[':platform'] = $platform;
        }

        $sql .= ' ORDER BY title ASC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Find a single game by its internal id.
     *
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM games WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);

        $result = $stmt->fetch();

        return $result === false ? null : $result;
    }

    /**
     * Find a single game by its external (FreeToGame) id.
     *
     * @return array<string, mixed>|null
     */
    public function findByExternalId(int $externalId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM games WHERE external_id = :external_id LIMIT 1');
        $stmt->execute([':external_id' => $externalId]);

        $result = $stmt->fetch();

        return $result === false ? null : $result;
    }

    public function count(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) AS total FROM games');
        $row = $stmt->fetch();

        return (int) ($row['total'] ?? 0);
    }

    /**
     * Return the most recent updated_at timestamp among all games.
     */
    public function lastSyncedAt(): ?string
    {
        $stmt = $this->pdo->query('SELECT MAX(updated_at) AS last_sync FROM games');
        $row = $stmt->fetch();

        return $row['last_sync'] ?? null;
    }

    /**
     * Distinct list of genres currently stored, for filter dropdowns.
     *
     * @return array<int, string>
     */
    public function distinctGenres(): array
    {
        $stmt = $this->pdo->query('SELECT DISTINCT genre FROM games WHERE genre IS NOT NULL AND genre <> "" ORDER BY genre ASC');

        return array_column($stmt->fetchAll(), 'genre');
    }

    /**
     * Distinct list of platforms currently stored, for filter dropdowns.
     *
     * @return array<int, string>
     */
    public function distinctPlatforms(): array
    {
        $stmt = $this->pdo->query('SELECT DISTINCT platform FROM games WHERE platform IS NOT NULL AND platform <> "" ORDER BY platform ASC');

        return array_column($stmt->fetchAll(), 'platform');
    }
}
