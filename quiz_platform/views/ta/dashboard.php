<?php require 'views/layout/header.php'; ?>

<div class="stats-grid">
    <div class="stat-card"><div class="stat-icon icon-blue">📚</div><div class="stat-info"><h3><?= $course_count ?></h3><p>Assigned Courses</p></div></div>
    <div class="stat-card"><div class="stat-icon icon-green">👥</div><div class="stat-info"><h3><?= $total_students ?></h3><p>Total Students</p></div></div>
    <div class="stat-card"><div class="stat-icon icon-red">⚠️</div><div class="stat-info"><h3><?= $at_risk_count ?></h3><p>At-Risk Students</p></div></div>
    <div class="stat-card"><div class="stat-icon icon-yellow">🎓</div><div class="stat-info"><h3><?= $upcoming_sessions ?></h3><p>Upcoming Sessions</p></div></div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header"><span class="card-title">📚 My Courses</span><a href="index.php?page=ta&action=courses" class="btn btn-outline btn-sm">View All</a></div>
        <div class="card-body" style="padding:0">
            <?php if (empty($courses)): ?>
                <div class="empty-state"><div class="empty-icon">📚</div><p>No courses assigned yet.</p></div>
            <?php else: ?>
            <?php foreach ($courses as $c): ?>
            <a href="index.php?page=ta&action=course_detail&course_id=<?= $c['id'] ?>"
               style="display:flex;align-items:center;gap:12px;padding:13px 20px;border-bottom:1px solid var(--border);text-decoration:none" onmouseover="this.style.background='var(--light)'" onmouseout="this.style.background=''">
                <div style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,var(--warning),var(--secondary));display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0">📚</div>
                <div style="flex:1;min-width:0">
                    <div class="fw-600 text-sm" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($c['title']) ?></div>
                    <div class="text-xs text-muted"><?= htmlspecialchars($c['instructor_name']) ?> • <?= $c['enrolled'] ?> students</div>
                </div>
                <span class="badge badge-success"><?= htmlspecialchars($c['subject']) ?></span>
            </a>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><span class="card-title">⚡ Quick Links</span></div>
        <div class="card-body">
            <div style="display:flex;flex-direction:column;gap:10px">
                <a href="index.php?page=ta&action=at_risk"        class="btn btn-warning">⚠️ View At-Risk Students</a>
                <a href="index.php?page=ta&action=doubt_sessions" class="btn btn-primary">🎓 Manage Doubt Sessions</a>
                <a href="index.php?page=ta&action=courses"        class="btn btn-secondary">📚 View All Courses</a>
            </div>
        </div>
    </div>
</div>

<?php require 'views/layout/footer.php'; ?>
<?php
// views/ta/dashboard.php — MEMBER 3
require 'views/layout/header.php';
?>

<?php if (!empty($platform_announcements)): ?>
<div style="margin-bottom:20px">
    <?php foreach ($platform_announcements as $pa): ?>
    <div class="alert alert-info" style="margin-bottom:8px">
        <span style="font-size:16px">📣</span>
        <strong><?= htmlspecialchars($pa['title']) ?></strong>
        — <?= nl2br(htmlspecialchars($pa['body'])) ?>
        <span class="text-xs" style="opacity:.7;margin-left:8px">
            Admin • <?= date('M d, Y', strtotime($pa['created_at'])) ?>
        </span>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- rest stays same -->
<div class="stats-grid">
...