<?php
// views/ta/course_detail.php — MEMBER 3
require 'views/layout/header.php';
?>

<!-- Course Banner -->
<div style="background:linear-gradient(135deg,var(--warning),var(--secondary));
            border-radius:var(--radius);padding:24px;color:white;margin-bottom:24px">
    <p class="text-xs" style="opacity:.7;margin-bottom:4px;
                               text-transform:uppercase;letter-spacing:1px">
        <?= htmlspecialchars($course['subject']) ?>
    </p>
    <h2 style="font-size:20px;font-weight:800;margin-bottom:6px">
        <?= htmlspecialchars($course['title']) ?>
    </h2>
    <p style="opacity:.85;font-size:13px">
        Instructor: <?= htmlspecialchars($course['instructor']) ?> •
        <?= $course['enrolled_count'] ?> students
    </p>
</div>

<div class="grid-2">
    <!-- Students List -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">
                👥 Enrolled Students (<?= count($students) ?>)
            </span>
        </div>
        <div class="card-body" style="padding:0">
            <?php if (empty($students)): ?>
                <div class="empty-state" style="padding:30px">
                    <p>No students enrolled.</p>
                </div>
            <?php else: ?>
            <?php foreach ($students as $s): ?>
            <div style="display:flex;align-items:center;gap:12px;
                        padding:12px 20px;border-bottom:1px solid var(--border)">
                <div style="width:34px;height:34px;border-radius:50%;
                            background:var(--warning);color:white;
                            display:flex;align-items:center;justify-content:center;
                            font-weight:700;font-size:13px;flex-shrink:0">
                    <?= strtoupper(substr($s['name'],0,1)) ?>
                </div>
                <div>
                    <div class="fw-600 text-sm">
                        <?= htmlspecialchars($s['name']) ?>
                    </div>
                    <div class="text-xs text-muted">
                        <?= htmlspecialchars($s['student_id']) ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right Column -->
    <div>
        <!-- Quiz List -->
        <div class="card" style="margin-bottom:20px">
            <div class="card-header">
                <span class="card-title">
                    📝 Quizzes (<?= count($quizzes) ?>)
                </span>
                <a href="index.php?page=ta&action=create_quiz&course_id=<?= $course['id'] ?>"
                   class="btn btn-primary btn-sm">+ Practice Quiz</a>
            </div>
            <div class="card-body" style="padding:0">
                <?php if (empty($quizzes)): ?>
                    <div class="empty-state" style="padding:30px">
                        <p>No quizzes yet.</p>
                    </div>
                <?php else: ?>
                <?php foreach ($quizzes as $q): ?>
                <div style="padding:14px 20px;border-bottom:1px solid var(--border)">
                    <div class="flex-between" style="margin-bottom:4px">
                        <div>
                            <div class="fw-600 text-sm">
                                <?= htmlspecialchars($q['title']) ?>
                            </div>
                            <div class="text-xs text-muted" style="margin-top:2px">
                                <?= ucfirst($q['quiz_type']) ?> •
                                <?= $q['total_marks'] ?> marks •
                                <?= $q['time_limit_minutes'] ?>min
                            </div>
                        </div>
                        <div class="flex gap-2" style="align-items:center">
                            <span class="badge badge-<?= $q['status']==='published'?'success':'warning' ?>">
                                <?= ucfirst($q['status']) ?>
                            </span>

                            <?php
                            // ── PRACTICE QUIZZES CREATED BY THIS TA ARE EDITABLE ──
                            $is_ta_quiz = ($q['created_by'] == $_SESSION['user_id']
                                           && $q['quiz_type'] === 'practice');
                            ?>
                            <?php if ($is_ta_quiz): ?>
                            <a href="index.php?page=ta&action=manage_quiz&quiz_id=<?= $q['id'] ?>"
                               class="btn btn-primary btn-sm">
                                ✏️ Edit
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Post Announcement -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">📣 Post Announcement</span>
            </div>
            <div class="card-body">
                <form method="POST"
                      action="index.php?page=ta&action=post_announcement">
                    <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                    <div class="form-group">
                        <input type="text" name="title" class="form-control"
                               placeholder="Announcement title" required>
                    </div>
                    <div class="form-group">
                        <textarea name="body" class="form-control"
                                  rows="3" placeholder="Message..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-full">
                        Post (as TA)
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require 'views/layout/footer.php'; ?>