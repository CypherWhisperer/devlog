<h1>Login</h1>

<?php if (!empty($error)): ?>
    <p style="color:red"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<form method="POST" action="/login">
    <label>Email
        <input type="email" name="email" required autofocus>
    </label>
    <label>Password
        <input type="password" name="password" required>
    </label>
    <button type="submit">Login</button>
</form>

<p><a href="/register">No account? Register</a></p>
