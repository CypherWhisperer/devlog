<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Middleware\AuthMiddleware;
use App\Models\Entry;

class EntryController
{
    public function index(): void
    {
        AuthMiddleware::require();
        $userId  = AuthMiddleware::userId();
        $filters = [
            'q'      => $_GET['q']      ?? '',
            'status' => $_GET['status'] ?? '',
            'sort'   => $_GET['sort']   ?? 'created_at',
        ];
        $entries = Entry::search($userId, $filters);
        View::render('entries/index', compact('entries', 'filters'));
    }

    public function create(): void
    {
        AuthMiddleware::require();
        View::render('entries/create');
    }

    public function store(): void
    {
        AuthMiddleware::require();
        // TODO: Milestone 4 — validation, Entry::create(), redirect
    }

    public function show(): void
    {
        // TODO: Milestone 4
    }

    public function edit(): void
    {
        // TODO: Milestone 4
    }

    public function update(): void
    {
        // TODO: Milestone 4
    }

    public function destroy(): void
    {
        // TODO: Milestone 4
    }
}
