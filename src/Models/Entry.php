<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Enums\EntryStatus;
use PDO;

class Entry
{
    public static function create(int $userId, string $title, string $body): int
    {
        $pdo  = Database::connect();
        $stmt = $pdo->prepare(
            'INSERT INTO entries (user_id, title, body, status) VALUES (:user_id, :title, :body, :status)'
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':title'   => $title,
            ':body'    => $body,
            ':status'  => EntryStatus::Draft->value,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function findById(int $id, int $userId): ?array
    {
        $pdo  = Database::connect();
        $stmt = $pdo->prepare(
            'SELECT * FROM entries WHERE id = :id AND user_id = :user_id AND deleted_at IS NULL'
        );
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    /** @return array<int, array<string, mixed>> */
    public static function findByUser(int $userId): array
    {
        $pdo  = Database::connect();
        $stmt = $pdo->prepare(
            'SELECT id, title, status, created_at FROM entries
             WHERE user_id = :user_id AND deleted_at IS NULL
             ORDER BY created_at DESC'
        );
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public static function update(int $id, int $userId, array $fields): bool
    {
        $allowed = ['title', 'body', 'status'];
        $set     = [];
        $params  = [':id' => $id, ':user_id' => $userId];

        foreach ($fields as $col => $val) {
            if (in_array($col, $allowed, true)) {
                $set[]           = "{$col} = :{$col}";
                $params[":{$col}"] = $val;
            }
        }

        if (empty($set)) {
            return false;
        }

        $stmt = Database::connect()->prepare(
            'UPDATE entries SET ' . implode(', ', $set) .
            ' WHERE id = :id AND user_id = :user_id AND deleted_at IS NULL'
        );
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    /** @param array{status?: string, q?: string, sort?: string} $filters */
    public static function search(int $userId, array $filters = []): array
    {
        $pdo        = Database::connect();
        $conditions = ['user_id = :user_id', 'deleted_at IS NULL'];
        $params     = [':user_id' => $userId];

        if (!empty($filters['status'])) {
            $conditions[]      = 'status = :status';
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['q'])) {
            $conditions[] = '(title LIKE :q OR body LIKE :q)';
            $params[':q'] = '%' . $filters['q'] . '%';
        }

        $allowedSort = ['created_at', 'updated_at', 'title'];
        $sort = in_array($filters['sort'] ?? '', $allowedSort, true)
            ? $filters['sort'] : 'created_at';

        $sql  = 'SELECT id, title, status, created_at FROM entries WHERE ';
        $sql .= implode(' AND ', $conditions);
        $sql .= " ORDER BY {$sort} DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function softDelete(int $id, int $userId): bool
    {
        $stmt = Database::connect()->prepare(
            'UPDATE entries SET deleted_at = NOW() WHERE id = :id AND user_id = :user_id'
        );
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        return $stmt->rowCount() > 0;
    }
}
