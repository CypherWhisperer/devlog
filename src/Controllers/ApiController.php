<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Entry;
use App\Models\User;

class ApiController
{
    // ── Token auth helper ────────────────────────────────────────────────────

    private function authenticatedUserId(): ?int
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!str_starts_with($header, 'Bearer ')) {
            return null;
        }
        $token = substr($header, 7);
        // TODO: Milestone 7 — User::findByToken($token)
        return null;
    }

    private function requireAuth(): int
    {
        $userId = $this->authenticatedUserId();
        if ($userId === null) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorised']);
            exit;
        }
        return $userId;
    }

    private function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    // ── Routes ───────────────────────────────────────────────────────────────

    public function index(): void
    {
        $userId  = $this->requireAuth();
        $entries = Entry::findByUser($userId);
        $this->json($entries);
    }

    public function store(): void
    {
        $userId = $this->requireAuth();
        // TODO: Milestone 7
        $this->json(['error' => 'Not implemented'], 501);
    }

    public function update(): void
    {
        $userId = $this->requireAuth();
        // TODO: Milestone 7
        $this->json(['error' => 'Not implemented'], 501);
    }

    public function destroy(): void
    {
        $userId = $this->requireAuth();
        // TODO: Milestone 7
        $this->json(['error' => 'Not implemented'], 501);
    }
}
