-- ============================================================
-- GameVault - Database Dump
-- Compatible with MySQL 5.7+/8+ and MariaDB 10.3+
--
-- Usage (Laragon / phpMyAdmin / CLI):
--   mysql -u root -p < database/gamevault.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS `gamevault`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `gamevault`;

CREATE TABLE IF NOT EXISTS `games` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `external_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `thumbnail` VARCHAR(500) DEFAULT NULL,
    `short_description` TEXT DEFAULT NULL,
    `game_url` VARCHAR(500) DEFAULT NULL,
    `genre` VARCHAR(100) DEFAULT NULL,
    `platform` VARCHAR(100) DEFAULT NULL,
    `publisher` VARCHAR(150) DEFAULT NULL,
    `developer` VARCHAR(150) DEFAULT NULL,
    `release_date` DATE DEFAULT NULL,
    `freetogame_profile_url` VARCHAR(500) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_games_external_id` (`external_id`),
    KEY `idx_games_genre` (`genre`),
    KEY `idx_games_platform` (`platform`),
    KEY `idx_games_title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- No seed data is included on purpose: run the "Sincronizar Catálogo"
-- button (or POST /api/sync.php) after importing this dump to populate
-- the table directly from the FreeToGame API.
