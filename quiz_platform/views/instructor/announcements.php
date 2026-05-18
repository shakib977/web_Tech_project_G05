<?php require 'views/layout/header.php'; ?>

<div class="grid-2">
    <div class="card">
        <div class="card-header"><span class="card-title">📣 Post Announcement</span></div>
        <div class="card-body">
            <form method="POST" action="index.php?page=instructor&action=post_announcement">
                <div class="form-group">
                    <label class="form-label">Course <span class="required">*</span></label>
                    <select name="course_id" class="form-control" required>
                        <option value="">Select course...</option>
                        <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($course_id??0)==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Title <span class="required">*</span></label>
                    <input type="text" name="title" class="form-control" placeholder="Announcement title" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Message <span class="required">*</span></label>
                    <textarea name="body" class="form-control" rows="5" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Post</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><span class="card-title">📋 Recent Announcements (<?= count($announcements) ?>)</span></div>
        <div class="card-body">
            <?php if (empty($announcements)): ?>
                <div class="empty-state"><div class="empty-icon">📣</div><p>No announcements yet.</p></div>
            <?php else: ?>
            <?php foreach ($announcements as $a): ?>
            <div class="ann-item">
                <div style="font-size:11px;color:var(--gray-light);margin-bottom:4px"><?= htmlspecialchars($a['course_title']) ?></div>
                <div class="ann-title"><?= htmlspecialchars($a['title']) ?></div>
                <div class="ann-body"><?= nl2br(htmlspecialchars($a['body'])) ?></div>
                <div class="ann-meta"><span>📅 <?= date('M d, Y', strtotime($a['created_at'])) ?></span></div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require 'views/layout/footer.php'; ?>