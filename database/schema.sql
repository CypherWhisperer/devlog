-- database/schema.sql
-- ─────────────────────────────────────────────────────────────────────────────
-- Run via: dl-migrate (devenv script) or manually:
--   mysql -u devlog -pdevlog -h 127.0.0.1 devlog < database/schema.sql
-- ─────────────────────────────────────────────────────────────────────────────

CREATE DATABASE IF NOT EXISTS devlog
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE devlog;

CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50)  NOT NULL UNIQUE,
    email         VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    api_token     VARCHAR(64)  DEFAULT NULL UNIQUE,
    created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS entries (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    title      VARCHAR(255) NOT NULL,
    body       LONGTEXT     NOT NULL,
    status     ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
    deleted_at TIMESTAMP    DEFAULT NULL,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FULLTEXT INDEX ft_title_body (title, body)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS attachments (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entry_id   INT UNSIGNED NOT NULL,
    filename   VARCHAR(255) NOT NULL,
    mime_type  VARCHAR(100) NOT NULL,
    size_bytes INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (entry_id) REFERENCES entries(id) ON DELETE CASCADE
) ENGINE=InnoDB;
