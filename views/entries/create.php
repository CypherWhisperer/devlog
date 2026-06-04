<h1>New Entry</h1>

<?php if (!empty($error)): ?>
    <p style="color:red"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<form method="POST" action="/entries">
    <label>Title
        <input type="text" name="title" required autofocus>
    </label>
    <label>Body (Markdown supported)
        <textarea name="body" rows="15" required></textarea>
    </label>
    <button type="submit">Save Draft</button>
    <a href="/">Cancel</a>
</form>
