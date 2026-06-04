<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private static ?PDO $instance = null;

    public static function connect(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            getenv('DB_HOST') ?: 'localhost',
            (int)(getenv('DB_PORT') ?: 3306),
            getenv('DB_NAME') ?: 'devlog'
        );

        try {
            self::$instance = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'), [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            error_log('DB connection failed: ' . $e->getMessage());
            throw new RuntimeException('Database unavailable.', previous: $e);
        }

        return self::$instance;
    }

    // Prevent instantiation — this is a pure static utility.
    private function __construct() {}
    private function __clone() {}
}
