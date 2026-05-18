<?php require 'views/layout/header.php'; ?>

<?php if (!empty($error)):   ?><div class="alert alert-danger">⚠️ <?= $error ?></div><?php endif; ?>
<?php if (!empty($success)): ?><div class="alert alert-success">✓ <?= $success ?></div><?php endif; ?>

<div class="grid-2">
    <!-- Post Announcement -->
    <div class="card">
        <div class="card-header"><span class="card-title">📣 Post Platform Announcement</span></div>
        <div class="card-body">
            <form method="POST" action="index.php?page=admin&action=post_announcement">
                <div class="form-group">
                    <label class="form-label">Title <span class="required">*</span></label>
                    <input type="text" name="title" class="form-control" placeholder="Announcement title" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Message <span class="required">*</span></label>
                    <textarea name="body" class="form-control" rows="5" placeholder="Write your announcement..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Post Announcement</button>
            </form>
        </div>
    </div>

    <!-- Past Announcements -->
    <div class="card">
        <div class="card-header"><span class="card-title">📋 Past Announcements (<?= count($announcements) ?>)</span></div>
        <div class="card-body">
            <?php if (empty($announcements)): ?>
                <div class="empty-state"><div class="empty-icon">📣</div><p>No announcements yet.</p></div>
            <?php else: ?>
            <?php foreach ($announcements as $a): ?>
            <div class="ann-item">
                <div class="ann-title"><?= htmlspecialchars($a['title']) ?></div>
                <div class="ann-body"><?= nl2br(htmlspecialchars($a['body'])) ?></div>
                <div class="ann-meta">
                    <span>👤 <?= htmlspecialchars($a['author']) ?></span>
                    <span>📅 <?= date('M d, Y H:i', strtotime($a['created_at'])) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require 'views/layout/footer.php'; ?>