<h1>Your Entries</h1>

<form method="GET" action="/">
    <input type="text" name="q" value="<?= htmlspecialchars($filters['q'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Search...">
    <select name="status">
        <option value="">All statuses</option>
        <option value="draft"     <?= ($filters['status'] ?? '') === 'draft'     ? 'selected' : '' ?>>Draft</option>
        <option value="published" <?= ($filters['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
        <option value="archived"  <?= ($filters['status'] ?? '') === 'archived'  ? 'selected' : '' ?>>Archived</option>
    </select>
    <button type="submit">Filter</button>
</form>

<a href="/entries/create">New Entry</a>

<?php if (empty($entries)): ?>
    <p>No entries yet.</p>
<?php else: ?>
    <ul>
    <?php foreach ($entries as $entry): ?>
        <li>
            <a href="/entries/<?= (int) $entry['id'] ?>">
                <?= htmlspecialchars($entry['title'], ENT_QUOTES, 'UTF-8') ?>
            </a>
            — <?= htmlspecialchars($entry['status'], ENT_QUOTES, 'UTF-8') ?>
            <small><?= htmlspecialchars($entry['created_at'], ENT_QUOTES, 'UTF-8') ?></small>
        </li>
    <?php endforeach; ?>
    </ul>
<?php endif; ?>
