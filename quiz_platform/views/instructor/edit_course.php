<?php
// views/instructor/edit_course.php — MEMBER 2
require 'views/layout/header.php';
?>

<?php if (!empty($error)):   ?><div class="alert alert-danger">⚠️ <?= $error ?></div><?php endif; ?>
<?php if (!empty($success)): ?><div class="alert alert-success">✓ <?= $success ?></div><?php endif; ?>

<!-- Page Tabs -->
<div style="display:flex;gap:4px;background:var(--light-2);border-radius:var(--radius-sm);
            padding:4px;margin-bottom:24px;width:fit-content">
    <button class="tab-btn active" data-tab="tab_settings" onclick="switchTab(this,'tab_settings')">⚙️ Settings</button>
    <button class="tab-btn"        data-tab="tab_quizzes"  onclick="switchTab(this,'tab_quizzes')">📝 Quizzes</button>
    <button class="tab-btn"        data-tab="tab_students" onclick="switchTab(this,'tab_students')">👥 Students</button>
    <button class="tab-btn"        data-tab="tab_ta"       onclick="switchTab(this,'tab_ta')">🎓 Assign TA</button>
</div>

<!-- TAB: Settings -->
<div id="tab_settings">
    <div style="max-width:680px">
    <div class="card">
        <div class="card-header">
            <span class="card-title">✏️ Edit Course Details</span>
            <a href="index.php?page=instructor&action=courses" class="btn btn-secondary btn-sm">← Back</a>
        </div>
        <div class="card-body">
            <form method="POST" action="index.php?page=instructor&action=edit_course">
                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                <div class="form-group">
                    <label class="form-label">Title <span class="required">*</span></label>
                    <input type="text" name="title" class="form-control"
                           value="<?= htmlspecialchars($course['title']) ?>" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Subject</label>
                        <select name="subject_id" class="form-control">
                            <?php foreach ($subjects as $s): ?>
                            <option value="<?= $s['id'] ?>"
                                <?= $course['subject_id']==$s['id']?'selected':'' ?>>
                                <?= htmlspecialchars($s['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Max Students</label>
                        <input type="number" name="max_students" class="form-control"
                               value="<?= $course['max_students'] ?>" min="1">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($course['description']) ?></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Enrollment Type</label>
                        <select name="enrollment_type" class="form-control">
                            <option value="open"     <?= $course['enrollment_type']==='open'    ?'selected':'' ?>>Open (anyone can join)</option>
                            <option value="approval" <?= $course['enrollment_type']==='approval'?'selected':'' ?>>Requires Approval</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="draft"    <?= $course['status']==='draft'   ?'selected':'' ?>>Draft</option>
                            <option value="active"   <?= $course['status']==='active'  ?'selected':'' ?>>Active</option>
                            <option value="archived" <?= $course['status']==='archived'?'selected':'' ?>>Archived</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
    </div>
    </div>
</div>

<!-- TAB: Quizzes -->
<div id="tab_quizzes" style="display:none">
    <?php
    // Fetch quizzes for this course
    $qz_stmt = $conn->prepare(
        "SELECT q.*,
                (SELECT COUNT(*) FROM questions WHERE quiz_id=q.id) AS question_count,
                (SELECT COUNT(*) FROM attempts WHERE quiz_id=q.id AND completed_at IS NOT NULL) AS attempt_count
         FROM quizzes q
         WHERE q.course_id = ?
         ORDER BY q.created_at DESC"
    );
    $qz_stmt->bind_param('i', $course['id']);
    $qz_stmt->execute();
    $course_quizzes = $qz_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $qz_stmt->close();
    ?>

    <div class="flex-between mb-4" style="margin-bottom:16px">
        <p class="text-sm text-muted"><?= count($course_quizzes) ?> quiz<?= count($course_quizzes)!=1?'zes':'' ?> in this course</p>
        <a href="index.php?page=instructor&action=create_quiz&course_id=<?= $course['id'] ?>"
           class="btn btn-primary">+ Create New Quiz</a>
    </div>

    <?php if (empty($course_quizzes)): ?>
        <div class="empty-state card" style="padding:50px">
            <div class="empty-icon">📝</div>
            <h3>No quizzes yet</h3>
            <p>Create your first quiz for this course.</p>
            <a href="index.php?page=instructor&action=create_quiz&course_id=<?= $course['id'] ?>"
               class="btn btn-primary">Create Quiz</a>
        </div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:12px">
        <?php foreach ($course_quizzes as $q): ?>
        <div class="card" style="margin-bottom:0">
            <div class="card-body" style="padding:16px 20px">
                <div class="flex-between flex-wrap gap-3">
                    <div>
                        <div class="fw-bold" style="font-size:14px"><?= htmlspecialchars($q['title']) ?></div>
                        <div class="text-xs text-muted" style="margin-top:4px">
                            <?= $q['question_count'] ?> question<?= $q['question_count']!=1?'s':'' ?> •
                            <?= $q['total_marks'] ?> marks •
                            <?= $q['time_limit_minutes'] ?>min •
                            <?= $q['attempt_count'] ?> attempt<?= $q['attempt_count']!=1?'s':'' ?>
                        </div>
                    </div>
                    <div class="flex gap-2" style="align-items:center">
                        <span class="badge badge-<?= $q['quiz_type']==='graded'?'info':'purple' ?>">
                            <?= ucfirst($q['quiz_type']) ?>
                        </span>
                        <span class="badge badge-<?= $q['status']==='published'?'success':'warning' ?>">
                            <?= ucfirst($q['status']) ?>
                        </span>
                        <!-- THIS IS THE BUTTON TO ADD/EDIT QUESTIONS -->
                        <a href="index.php?page=instructor&action=manage_quiz&quiz_id=<?= $q['id'] ?>"
                           class="btn btn-primary btn-sm">
                            ✏️ Add / Edit Questions
                        </a>
                        <a href="index.php?page=instructor&action=quiz_attempts&quiz_id=<?= $q['id'] ?>"
                           class="btn btn-secondary btn-sm">
                            📊 Results
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- TAB: Students -->
<div id="tab_students" style="display:none">
    <div class="card">
        <div class="card-header">
            <span class="card-title">👥 Enrolled Students (<?= count($students) ?>)</span>
            <a href="index.php?page=instructor&action=enrollments&course_id=<?= $course['id'] ?>"
               class="btn btn-outline btn-sm">Manage Enrollments</a>
        </div>
        <div class="card-body" style="padding:0">
            <?php if (empty($students)): ?>
                <div class="empty-state" style="padding:40px">
                    <div class="empty-icon">👥</div>
                    <p>No students enrolled yet.</p>
                </div>
            <?php else: ?>
            <div class="table-wrap">
            <table>
                <thead><tr><th>Name</th><th>Student ID</th><th>Program</th><th>Enrolled</th></tr></thead>
                <tbody>
                <?php foreach ($students as $s): ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <div style="width:32px;height:32px;border-radius:50%;
                                        background:var(--primary);color:white;
                                        display:flex;align-items:center;justify-content:center;
                                        font-size:13px;font-weight:700;flex-shrink:0">
                                <?= strtoupper(substr($s['name'],0,1)) ?>
                            </div>
                            <div>
                                <div class="fw-600 text-sm"><?= htmlspecialchars($s['name']) ?></div>
                                <div class="text-xs text-muted"><?= htmlspecialchars($s['email']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="text-sm"><?= htmlspecialchars($s['student_id'] ?: '—') ?></td>
                    <td class="text-sm text-gray"><?= htmlspecialchars($s['program'] ?: '—') ?></td>
                    <td class="text-sm text-muted"><?= date('M d, Y', strtotime($s['enrolled_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- TAB: Assign TA -->
<div id="tab_ta" style="display:none">
    <?php
    $all_tas = $conn->query(
        "SELECT id, name, email FROM users WHERE role='ta' AND is_active=1 ORDER BY name"
    )->fetch_all(MYSQLI_ASSOC);

    $cur_ta_stmt = $conn->prepare(
        "SELECT u.id, u.name, u.email
         FROM course_tas ct JOIN users u ON ct.ta_id=u.id
         WHERE ct.course_id=? LIMIT 1"
    );
    $cur_ta_stmt->bind_param('i', $course['id']);
    $cur_ta_stmt->execute();
    $current_ta = $cur_ta_stmt->get_result()->fetch_assoc();
    $cur_ta_stmt->close();
    ?>
    <div style="max-width:480px">
    <div class="card">
        <div class="card-header"><span class="card-title">🎓 Assign Teaching Assistant</span></div>
        <div class="card-body">
            <?php if ($current_ta): ?>
            <div class="alert alert-info" style="margin-bottom:16px">
                Currently assigned: <strong><?= htmlspecialchars($current_ta['name']) ?></strong>
                (<?= htmlspecialchars($current_ta['email']) ?>)
            </div>
            <?php endif; ?>
            <form method="POST" action="index.php?page=instructor&action=assign_ta">
                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                <div class="form-group">
                    <label class="form-label">Select TA</label>
                    <select name="ta_id" class="form-control">
                        <option value="">— Remove TA —</option>
                        <?php foreach ($all_tas as $ta): ?>
                        <option value="<?= $ta['id'] ?>"
                            <?= ($current_ta && $current_ta['id']==$ta['id'])?'selected':'' ?>>
                            <?= htmlspecialchars($ta['name']) ?> — <?= htmlspecialchars($ta['email']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (empty($all_tas)): ?>
                    <p class="form-text text-danger">
                        No TA accounts exist yet.
                        <a href="index.php?page=instructor&action=courses">Ask your admin to create one.</a>
                    </p>
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn btn-primary">Assign TA</button>
            </form>
        </div>
    </div>
    </div>
</div>

<script>
function switchTab(btn, tabId) {
    // Hide all tabs
    document.querySelectorAll('#tab_settings,#tab_quizzes,#tab_students,#tab_ta')
            .forEach(t => t.style.display = 'none');
    // Remove active from all buttons
    btn.closest('div').querySelectorAll('.tab-btn')
       .forEach(b => b.classList.remove('active'));
    // Show selected
    document.getElementById(tabId).style.display = 'block';
    btn.classList.add('active');
}
</script>

<?php require 'views/layout/footer.php'; ?>