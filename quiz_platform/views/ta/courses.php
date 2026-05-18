<?php require 'views/layout/header.php'; ?>

<p class="text-sm text-muted mb-4" style="margin-bottom:16px"><?= count($courses) ?> course<?= count($courses)!=1?'s':'' ?> assigned to you</p>

<?php if (empty($courses)): ?>
    <div class="empty-state card" style="padding:60px">
        <div class="empty-icon">📚</div><h3>No courses assigned</h3><p>Ask your instructor to assign you to a course.</p>
    </div>
<?php else: ?>
<div class="course-grid">
    <?php foreach ($courses as $c): ?>
    <div class="course-card">
        <div class="course-card-banner"><h3><?= htmlspecialchars($c['title']) ?></h3><p>📂 <?= htmlspecialchars($c['subject']) ?></p></div>
        <div class="course-card-body">
            <div class="course-card-meta">
                <span class="meta-item">👨‍🏫 <?= htmlspecialchars($c['instructor_name']) ?></span>
                <span class="meta-item">👥 <?= $c['enrolled'] ?> students</span>
            </div>
        </div>
        <div class="course-card-footer">
            <div class="flex gap-2">
                <a href="index.php?page=ta&action=course_detail&course_id=<?= $c['id'] ?>"     class="btn btn-outline btn-sm">Overview</a>
                <a href="index.php?page=ta&action=student_results&course_id=<?= $c['id'] ?>"  class="btn btn-info btn-sm">Results</a>
            </div>
            <a href="index.php?page=ta&action=course_summary&course_id=<?= $c['id'] ?>" class="btn btn-secondary btn-sm">📊</a>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require 'views/layout/footer.php'; ?>