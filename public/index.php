<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

// ── Environment ───────────────────────────────────────────────────────────────
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// ── Session hardening ─────────────────────────────────────────────────────────
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
session_start();

// ── Global exception handler ──────────────────────────────────────────────────
set_exception_handler(function (\Throwable $e): void {
    error_log($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    if (getenv('APP_ENV') === 'development') {
        echo '<pre>' . htmlspecialchars((string) $e, ENT_QUOTES, 'UTF-8') . '</pre>';
    } else {
        http_response_code(500);
        echo 'Something went wrong.';
    }
    exit(1);
});

// ── Router ────────────────────────────────────────────────────────────────────
$router = new \App\Core\Router();

// Auth
$router->get('/login',    \App\Controllers\AuthController::class, 'showLogin');
$router->get('/register', \App\Controllers\AuthController::class, 'showRegister');
$router->post('/login',   \App\Controllers\AuthController::class, 'login');
$router->post('/register',\App\Controllers\AuthController::class, 'register');
$router->post('/logout',  \App\Controllers\AuthController::class, 'logout');

// Entries
$router->get('/',               \App\Controllers\EntryController::class, 'index');
$router->get('/entries/create', \App\Controllers\EntryController::class, 'create');
$router->post('/entries',       \App\Controllers\EntryController::class, 'store');

// API
$router->get('/api/entries',    \App\Controllers\ApiController::class, 'index');
$router->post('/api/entries',   \App\Controllers\ApiController::class, 'store');

$router->dispatch(
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI']
);
