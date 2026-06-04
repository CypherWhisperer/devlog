<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class User
{
    public static function create(string $username, string $email, string $password): int
    {
        $pdo  = Database::connect();
        $stmt = $pdo->prepare(
            'INSERT INTO users (username, email, password_hash) VALUES (:username, :email, :hash)'
        );
        $stmt->execute([
            ':username' => $username,
            ':email'    => $email,
            ':hash'     => password_hash($password, PASSWORD_BCRYPT),
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function findByEmail(string $email): ?array
    {
        $pdo  = Database::connect();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    public static function findById(int $id): ?array
    {
        $pdo  = Database::connect();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }
}
