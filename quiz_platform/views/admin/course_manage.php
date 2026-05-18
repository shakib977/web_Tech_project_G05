<?php
// views/admin/course_manage.php — MEMBER 4
require 'views/layout/header.php';
?>

<?php if (!empty($error)):   ?><div class="alert alert-danger">⚠️ <?= $error ?></div><?php endif; ?>
<?php if (!empty($success)): ?><div class="alert alert-success">✓ <?= $success ?></div><?php endif; ?>

<!-- Breadcrumb -->
<div class="flex gap-2" style="margin-bottom:20px;align-items:center;font-size:14px">
    <a href="index.php?page=admin&action=courses" style="color:var(--gray)">All Courses</a>
    <span style="color:var(--gray-light)">›</span>
    <span class="fw-600"><?= htmlspecialchars($course['title']) ?></span>
</div>

<!-- Course Hero Card -->
<div style="background:linear-gradient(135deg,var(--primary),var(--secondary));
            border-radius:var(--radius);padding:24px 28px;
            color:white;margin-bottom:24px;
            display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px">
    <div>
        <p style="opacity:.7;font-size:12px;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px">
            <?= htmlspecialchars($course['subject_name']) ?>
        </p>
        <h2 style="font-size:20px;font-weight:800;margin-bottom:6px">
            <?= htmlspecialchars($course['title']) ?>
        </h2>
        <p style="opacity:.85;font-size:13px">
            👨‍🏫 <?= htmlspecialchars($course['instructor_name']) ?> •
            👥 <?= $enrolled_count ?> enrolled •
            📝 <?= count($quizzes) ?> quizzes
        </p>
    </div>
    <div class="flex gap-2 flex-wrap">
        <span class="badge" style="background:rgba(255,255,255,.2);color:white;font-size:13px">
            <?= ucfirst($course['status']) ?>
        </span>
        <a href="index.php?page=admin&action=course_students&course_id=<?= $course['id'] ?>"
           class="btn btn-sm"
           style="background:rgba(255,255,255,.15);color:white;border:1px solid rgba(255,255,255,.3)">
            👥 View All Students
        </a>
    </div>
</div>

<div class="grid-2">

    <!-- LEFT: Assign TA -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">🎓 Teaching Assistant</span>
            <?php if ($current_ta): ?>
                <span class="badge badge-success">Assigned</span>
            <?php else: ?>
                <span class="badge badge-warning">Not Assigned</span>
            <?php endif; ?>
        </div>
        <div class="card-body">

            <!-- Current TA info -->
            <?php if ($current_ta): ?>
            <div style="display:flex;align-items:center;gap:14px;
                        padding:16px;background:var(--light);
                        border-radius:var(--radius-sm);margin-bottom:20px;
                        border:1px solid var(--border)">
                <div style="width:46px;height:46px;border-radius:50%;
                            background:linear-gradient(135deg,var(--warning),var(--secondary));
                            color:white;display:flex;align-items:center;justify-content:center;
                            font-size:18px;font-weight:800;flex-shrink:0">
                    <?= strtoupper(substr($current_ta['name'], 0, 1)) ?>
                </div>
                <div>
                    <div class="fw-bold" style="font-size:14px"><?= htmlspecialchars($current_ta['name']) ?></div>
                    <div class="text-sm text-muted"><?= htmlspecialchars($current_ta['email']) ?></div>
                    <div style="font-size:11px;color:var(--success);font-weight:600;margin-top:2px">
                        ✓ Currently assigned to this course
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="alert alert-warning" style="margin-bottom:20px">
                ⚠️ No TA assigned to this course yet.
            </div>
            <?php endif; ?>

            <!-- Assign Form -->
            <form method="POST"
                  action="index.php?page=admin&action=course_manage&course_id=<?= $course['id'] ?>">
                <div class="form-group">
                    <label class="form-label">
                        <?= $current_ta ? 'Change TA' : 'Assign a TA' ?>
                    </label>
                    <select name="ta_id" class="form-control">
                        <option value="">— Remove / No TA —</option>
                        <?php if (empty($all_tas)): ?>
                            <!-- No TAs exist -->
                        <?php else: ?>
                            <?php foreach ($all_tas as $ta): ?>
                            <option value="<?= $ta['id'] ?>"
                                <?= ($current_ta && $current_ta['id']==$ta['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ta['name']) ?> — <?= htmlspecialchars($ta['email']) ?>
                            </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <?php if (empty($all_tas)): ?>
                    <p class="form-text text-danger">
                        No TA accounts found.
                        <a href="index.php?page=admin&action=create_ta">Create a TA account first →</a>
                    </p>
                    <?php else: ?>
                    <p class="form-text">Select a TA to assign them to this course.</p>
                    <?php endif; ?>
                </div>
                <button type="submit"
                        name="assign_ta"
                        class="btn btn-primary btn-full"
                        <?= empty($all_tas) ? 'disabled' : '' ?>>
                    💾 Save TA Assignment
                </button>
            </form>
        </div>
    </div>

    <!-- RIGHT: Course Info + Quizzes -->
    <div>

        <!-- Course Details (read-only for admin) -->
        <div class="card" style="margin-bottom:20px">
            <div class="card-header"><span class="card-title">📋 Course Details</span></div>
            <div class="card-body" style="padding:0">
                <?php
                $details = [
                    ['label' => 'Subject',          'value' => $course['subject_name']],
                    ['label' => 'Instructor',        'value' => $course['instructor_name']],
                    ['label' => 'Enrollment Type',   'value' => ucfirst($course['enrollment_type'])],
                    ['label' => 'Max Students',      'value' => $course['max_students']],
                    ['label' => 'Currently Enrolled','value' => $enrolled_count . ' students'],
                    ['label' => 'Status',            'value' => ucfirst($course['status'])],
                    ['label' => 'Created',           'value' => date('M d, Y', strtotime($course['created_at']))],
                ];
                ?>
                <?php foreach ($details as $d): ?>
                <div style="display:flex;justify-content:space-between;
                            padding:11px 20px;border-bottom:1px solid var(--border)">
                    <span class="text-sm text-muted"><?= $d['label'] ?></span>
                    <span class="text-sm fw-600"><?= htmlspecialchars($d['value']) ?></span>
                </div>
                <?php endforeach; ?>
                <?php if ($course['description']): ?>
                <div style="padding:14px 20px">
                    <div class="text-xs text-muted" style="margin-bottom:4px">Description</div>
                    <div class="text-sm"><?= nl2br(htmlspecialchars($course['description'])) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quizzes in this course -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">📝 Quizzes (<?= count($quizzes) ?>)</span>
            </div>
            <div class="card-body" style="padding:0">
                <?php if (empty($quizzes)): ?>
                    <div class="empty-state" style="padding:30px">
                        <div class="empty-icon">📝</div>
                        <p>No quizzes in this course.</p>
                    </div>
                <?php else: ?>
                <?php foreach ($quizzes as $q): ?>
                <div style="display:flex;align-items:center;justify-content:space-between;
                            gap:12px;padding:13px 20px;border-bottom:1px solid var(--border)">
                    <div style="flex:1;min-width:0">
                        <div class="fw-600 text-sm"
                             style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                            <?= htmlspecialchars($q['title']) ?>
                        </div>
                        <div class="text-xs text-muted" style="margin-top:3px">
                            <?= $q['total_marks'] ?> marks •
                            <?= $q['attempt_count'] ?> attempt<?= $q['attempt_count']!=1?'s':'' ?>
                        </div>
                    </div>
                    <div class="flex gap-2" style="flex-shrink:0">
                        <span class="badge badge-<?= $q['quiz_type']==='graded'?'info':'purple' ?>">
                            <?= ucfirst($q['quiz_type']) ?>
                        </span>
                        <span class="badge badge-<?= $q['status']==='published'?'success':'warning' ?>">
                            <?= ucfirst($q['status']) ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<!-- Back button -->
<div style="margin-top:20px">
    <a href="index.php?page=admin&action=courses" class="btn btn-secondary">← Back to All Courses</a>
</div>

<?php require 'views/layout/footer.php'; ?>