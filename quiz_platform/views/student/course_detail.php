<?php
// views/student/course_detail.php — MEMBER 1
require 'views/layout/header.php';
?>

<?php if (!empty($_GET['drop_error'])): ?>
<div class="alert alert-danger">
    ❌ Cannot drop this course — you have already completed a graded quiz.
    
</div>
<?php endif; ?>

<!-- Course Banner -->
<div style="background:linear-gradient(135deg,var(--primary),var(--secondary));
            border-radius:var(--radius);padding:24px 28px;margin-bottom:24px;color:white">
    <p style="font-size:11px;opacity:.7;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px">
        <?= htmlspecialchars($course['subject']) ?>
    </p>
    <h2 style="font-size:20px;font-weight:800;margin-bottom:6px">
        <?= htmlspecialchars($course['title']) ?>
    </h2>
    <p style="opacity:.85;font-size:13px;margin-bottom:14px">
        <?= htmlspecialchars($course['description']) ?>
    </p>
    <div class="flex gap-4 flex-wrap" style="font-size:13px;opacity:.85">
        <span>👨‍🏫 <?= htmlspecialchars($course['instructor']) ?></span>
        <?php if ($ta): ?><span>🎓 TA: <?= htmlspecialchars($ta['name']) ?></span><?php endif; ?>
        <span>👥 <?= $course['enrolled_count'] ?> students</span>
    </div>
</div>

<!-- Drop Course -->
<div style="display:flex;justify-content:flex-end;margin-bottom:16px">
    <button type="button"
            class="btn btn-danger btn-sm"
            onclick="askDropCourse(<?= $course['id'] ?>)">
        Leave Course
    </button>
</div>

<!-- Hidden form for drop -->
<form id="drop_form" method="POST"
      action="index.php?page=student&action=drop_course"
      style="display:none">
    <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
</form>

<div class="grid-2">
    <!-- LEFT COLUMN -->
    <div>

        <!-- available Quizzes (unattempted only) -->
<div class="card" style="margin-bottom:20px">
    <div class="card-header">
        <span class="card-title">📝 Quizzes</span>
        <span class="badge badge-info"><?= count($quizzes) ?></span>
    </div>
    <div class="card-body" style="padding:0">
        <?php if (empty($quizzes)): ?>
        <div class="empty-state" style="padding:30px">
            <div class="empty-icon">✅</div>
            <p class="fw-600">All graded quizzes completed!</p>
            <p class="text-sm text-muted">No pending quizzes right now.</p>
        </div>
        <?php else: ?>
        <?php foreach ($quizzes as $q): ?>
        <?php
            $now         = time();
            $from        = $q['available_from']  ? strtotime($q['available_from'])  : 0;
            $until       = $q['available_until'] ? strtotime($q['available_until']) : 0;
            $is_practice = ($q['quiz_type'] === 'practice');
            $is_closed   = ($until && $now > $until); // only hide if deadline passed
        ?>
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);
                    border-left:3px solid <?= $is_practice?'var(--secondary)':'var(--primary)' ?>">
            <div style="display:flex;align-items:flex-start;
                        justify-content:space-between;gap:12px;flex-wrap:wrap">
                <div style="flex:1">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
                        <span class="badge badge-<?= $is_practice?'purple':'info' ?>"
                              style="font-size:11px">
                            <?= $is_practice ? '📝 Practice' : '📊 Graded' ?>
                        </span>
                        <?php if ($is_practice && $q['attempt_count'] > 0): ?>
                        <span class="text-xs text-muted">
                            <?= $q['attempt_count'] ?> attempt<?= $q['attempt_count']!=1?'s':'' ?>
                            <?php if ($q['best_score'] !== null): ?>
                            · Best: <?= round($q['best_score']) ?>/<?= $q['total_marks'] ?>
                            <?php endif; ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <div class="fw-bold" style="font-size:14px;margin-bottom:4px">
                        <?= htmlspecialchars($q['title']) ?>
                    </div>
                    <div class="text-xs text-muted"
                         style="display:flex;gap:14px;flex-wrap:wrap">
                        <span>⏱ <?= $q['time_limit_minutes'] ?>min</span>
                        <span>📊 <?= $q['total_marks'] ?> marks</span>
                        <?php if (!$is_practice): ?>
                        <span>🎯 Pass: <?= $q['pass_mark'] ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Time window note (info only, does NOT block button) -->
                    <?php if ($from && $now < $from): ?>
                    <div style="margin-top:6px;font-size:12px;color:var(--warning);font-weight:600">
                        🕐 Scheduled from <?= date('M d, Y H:i', $from) ?>
                        — you may attempt early
                    </div>
                    <?php endif; ?>
                    <?php if ($until && !$is_closed): ?>
                    <div style="margin-top:4px;font-size:12px;color:var(--gray)">
                        ⏰ Closes <?= date('M d, Y H:i', $until) ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div style="flex-shrink:0">
                    <?php if ($is_closed): ?>
                        <span class="badge badge-danger">🔒 Closed</span>
                    <?php else: ?>
                        <!-- ALWAYS show button for published quizzes -->
                        <button class="btn btn-sm <?= $is_practice?'':'btn-primary' ?>"
                                style="<?= $is_practice?'background:var(--secondary);color:white':'' ?>"
                                onclick="askStartQuiz(
                                    <?= $q['id'] ?>,
                                    '<?= htmlspecialchars(addslashes($q['title'])) ?>',
                                    <?= $q['time_limit_minutes'] ?>,
                                    <?= $q['total_marks'] ?>,
                                    '<?= $q['quiz_type'] ?>'
                                )">
                            <?= $is_practice ? '📝 Practice' : '🚀 Start Quiz' ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
        <!-- Course Materials -->
        <div class="card">
            <div class="card-header"><span class="card-title">📁 Materials</span></div>
            <div class="card-body" style="padding:0">
                <?php if (empty($materials)): ?>
                    <div class="empty-state" style="padding:24px"><p>No materials yet.</p></div>
                <?php else: ?>
                <?php foreach ($materials as $m): ?>
                <div style="display:flex;align-items:center;gap:12px;
                            padding:12px 20px;border-bottom:1px solid var(--border)">
                    <span style="font-size:20px">
                        <?= $m['material_type']==='document'?'📄':($m['material_type']==='video'?'🎥':'🔗') ?>
                    </span>
                    <div style="flex:1">
                        <div class="fw-600 text-sm"><?= htmlspecialchars($m['title']) ?></div>
                    </div>
                    <?php if ($m['material_type']==='link'): ?>
                        <a href="<?= htmlspecialchars($m['file_path']) ?>"
                           target="_blank" class="btn btn-outline btn-sm">Open</a>
                    <?php elseif ($m['file_path']): ?>
                        <a href="<?= BASE_URL ?>/uploads/materials/<?= htmlspecialchars($m['file_path']) ?>"
                           download class="btn btn-outline btn-sm">Download</a>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- RIGHT COLUMN -->
    <div>

       <!-- Leaderboard AJAX — uses ALL published quizzes -->
<div class="card" style="margin-bottom:20px">
    <div class="card-header">
        <span class="card-title">🏆 Leaderboard</span>
        <span class="badge badge-info" style="font-size:11px">AJAX</span>
    </div>
    <div class="card-body" style="padding:0">
        <div style="padding:14px 20px;border-bottom:1px solid var(--border)">
            <select class="form-control"
                    onchange="loadLeaderboard(this.value,'lb_container')">
                <option value="">Select a quiz to view top 5 scorers...</option>
                <?php foreach ($all_quizzes as $q): ?>
                <option value="<?= $q['id'] ?>">
                    <?= $q['quiz_type']==='practice'?'[Practice] ':'' ?><?= htmlspecialchars($q['title']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div id="lb_container">
            <p class="text-center text-muted text-sm" style="padding:24px">
                Select a quiz above.
            </p>
        </div>
    </div>
</div>

        <!-- Announcements -->
        <div class="card" style="margin-bottom:20px">
            <div class="card-header"><span class="card-title">📣 Announcements</span></div>
            <div class="card-body">
                <?php if (empty($announcements)): ?>
                    <div class="empty-state" style="padding:20px"><p>No announcements.</p></div>
                <?php else: ?>
                <?php foreach ($announcements as $a): ?>
                <div class="ann-item">
                    <div class="ann-title">
                        <?= htmlspecialchars($a['title']) ?>
                        <?php if ($a['from_ta']): ?>
                            <span class="badge badge-warning" style="font-size:10px;margin-left:6px">
                                From TA
                            </span>
                        <?php endif; ?>
                    </div>
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

        <!-- Q&A Link -->
        <div class="card">
            <div class="card-header"><span class="card-title">❓ Q&A Board</span></div>
            <div class="card-body">
                <p class="text-sm text-gray" style="margin-bottom:14px">
                    Ask questions about this course.
                </p>
                <a href="index.php?page=student&action=qa_board&course_id=<?= $course['id'] ?>"
                   class="btn btn-primary btn-full">
                    Open Q&A Board →
                </a>
            </div>
        </div>

    </div>
</div>

<!-- Start Quiz Confirm Modal script -->
<script>
function askStartQuiz(quizId, title, timeLimit, totalMarks) {
    cconfirmShow({
        icon:    '🚀',
        title:   'Start Quiz ',
        msg:     '"' + title + '"\n\nTime limit: ' + timeLimit + ' minutes\nTotal marks: ' + totalMarks + '\n\nThe timer will start immediately. Make sure you are ready!',
        okText:  'Start Now',
        okClass: 'btn-primary',
        onOk:    function() {
            window.location.href =
                'index.php?page=student&action=take_quiz&quiz_id=' + quizId;
        }
    });
}

function askDropCourse(courseId) {
    cconfirmShow({
        icon:    '⚠️',
        title:   'Drop Course',
        msg:     'Are you sure you want to drop this course?\n\nThis cannot be undone if you have attempted any graded quiz.',
        okText:  'Drop Course',
        okClass: 'btn-danger',
        onOk:    function() {
            document.getElementById('drop_form').submit();
        }
    });
}
</script>

<?php require 'views/layout/footer.php'; ?>