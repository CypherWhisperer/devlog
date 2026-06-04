<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevLog</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <nav>
        <?php if (!empty($_SESSION['user_id'])): ?>
            <span>Hello, <?= htmlspecialchars($_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
            <a href="/">Entries</a>
            <form method="POST" action="/logout" style="display:inline">
                <button type="submit">Logout</button>
            </form>
        <?php else: ?>
            <a href="/login">Login</a>
            <a href="/register">Register</a>
        <?php endif; ?>
    </nav>

    <main>
        <?= $content ?? '' ?>
    </main>

    <script src="/assets/js/app.js"></script>
</body>
</html>
