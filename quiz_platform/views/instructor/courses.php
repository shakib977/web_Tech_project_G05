<?php
// views/instructor/courses.php — MEMBER 2
require 'views/layout/header.php';
?>

<div class="flex-between mb-4" style="margin-bottom:16px">
    <p class="text-sm text-muted"><?= count($courses) ?> total course<?= count($courses)!=1?'s':'' ?></p>
    <a href="index.php?page=instructor&action=create_course" class="btn btn-primary">+ New Course</a>
</div>

<?php if (empty($courses)): ?>
    <div class="empty-state card" style="padding:60px">
        <div class="empty-icon">📚</div>
        <h3>No courses yet</h3>
        <p>Create your first course to get started.</p>
        <a href="index.php?page=instructor&action=create_course" class="btn btn-primary">Create Course</a>
    </div>
<?php else: ?>

<div style="display:flex;flex-direction:column;gap:16px">
    <?php foreach ($courses as $c): ?>
    <div class="card" style="margin-bottom:0">
        <div class="card-body" style="padding:20px 24px">
            <div class="flex-between flex-wrap gap-3">

                <!-- Course Info -->
                <div style="display:flex;align-items:center;gap:16px;flex:1;min-width:200px">
                    <div style="width:48px;height:48px;border-radius:12px;
                                background:linear-gradient(135deg,var(--primary),var(--secondary));
                                display:flex;align-items:center;justify-content:center;
                                font-size:22px;flex-shrink:0">📚</div>
                    <div>
                        <div class="fw-bold" style="font-size:15px"><?= htmlspecialchars($c['title']) ?></div>
                        <div class="text-sm text-muted" style="margin-top:2px">
                            <?= htmlspecialchars($c['subject']) ?> •
                            <?= $c['enrolled_count'] ?> student<?= $c['enrolled_count']!=1?'s':'' ?> •
                            <?= $c['quiz_count'] ?> quiz<?= $c['quiz_count']!=1?'zes':'' ?>
                            <?php if ($c['pending'] > 0): ?>
                                • <span class="text-warning fw-600"><?= $c['pending'] ?> pending</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Status badge -->
                <span class="badge badge-<?= $c['status']==='active'?'success':($c['status']==='archived'?'gray':'warning') ?>">
                    <?= ucfirst($c['status']) ?>
                </span>

                <!-- Action buttons -->
                <div class="flex gap-2 flex-wrap">
                    <a href="index.php?page=instructor&action=edit_course&course_id=<?= $c['id'] ?>"
                       class="btn btn-outline btn-sm" title="Edit course details">
                        ⚙️ Settings
                    </a>
                    <a href="index.php?page=instructor&action=create_quiz&course_id=<?= $c['id'] ?>"
                       class="btn btn-primary btn-sm" title="Create a new quiz">
                        📝 Add Quiz
                    </a>
                    <a href="index.php?page=instructor&action=materials&course_id=<?= $c['id'] ?>"
                       class="btn btn-info btn-sm" title="Upload files and links">
                        📁 Materials
                    </a>
                    <a href="index.php?page=instructor&action=announcements&course_id=<?= $c['id'] ?>"
                       class="btn btn-secondary btn-sm" title="Post announcement">
                        📣 Announce
                    </a>
                    <a href="index.php?page=instructor&action=qa_board&course_id=<?= $c['id'] ?>"
                       class="btn btn-secondary btn-sm" title="View Q&A board">
                        ❓ Q&A
                    </a>
                    <?php if ($c['pending'] > 0): ?>
                    <a href="index.php?page=instructor&action=enrollments&course_id=<?= $c['id'] ?>"
                       class="btn btn-warning btn-sm">
                        ⏳ <?= $c['pending'] ?> Pending
                    </a>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php endif; ?>

<?php require 'views/layout/footer.php'; ?>