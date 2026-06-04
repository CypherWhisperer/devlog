<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\User;

class AuthController
{
    public function showLogin(): void
    {
        View::render('auth/login');
    }

    public function login(): void
    {
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        $user = User::findByEmail($email);

        if ($user === null || !password_verify($password, $user['password_hash'])) {
            View::render('auth/login', ['error' => 'Invalid credentials.']);
            return;
        }

        session_regenerate_id(true);
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];

        header('Location: /');
        exit;
    }

    public function showRegister(): void
    {
        View::render('auth/register');
    }

    public function register(): void
    {
        // TODO: Milestone 2 — validation, duplicate check, create user
        View::render('auth/register', ['error' => 'Registration not yet implemented.']);
    }

    public function logout(): void
    {
        session_destroy();
        header('Location: /login');
        exit;
    }
}
