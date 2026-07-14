<?php

declare(strict_types=1);

namespace Config;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Database
 *
 * Single responsibility: provide a shared PDO connection instance.
 * No business logic, no SQL statements related to domain tables here.
 */
final class Database
{
    private static ?PDO $instance = null;

    private function __construct()
    {
        // Prevent direct instantiation.
    }

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                DB_HOST,
                DB_PORT,
                DB_NAME
            );

            try {
                self::$instance = new PDO(
                    $dsn,
                    DB_USER,
                    DB_PASS,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $e) {
                // Never leak raw PDO exceptions (may contain credentials) to output.
                throw new RuntimeException('Database connection failed.', 0, $e);
            }
        }

        return self::$instance;
    }
}
