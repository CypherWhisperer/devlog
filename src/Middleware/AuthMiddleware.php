<?php
declare(strict_types=1);

namespace App\Middleware;

class AuthMiddleware
{
    /**
     * Redirect unauthenticated requests to /login.
     * Call at the top of any controller method that requires a logged-in user.
     */
    public static function require(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
    }

    /** Return the authenticated user ID, or null if not logged in. */
    public static function userId(): ?int
    {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }
}
