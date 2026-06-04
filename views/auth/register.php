<h1>Register</h1>

<?php if (!empty($error)): ?>
    <p style="color:red"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<form method="POST" action="/register">
    <label>Username
        <input type="text" name="username" required autofocus>
    </label>
    <label>Email
        <input type="email" name="email" required>
    </label>
    <label>Password
        <input type="password" name="password" required>
    </label>
    <button type="submit">Create Account</button>
</form>

<p><a href="/login">Already have an account? Login</a></p>
