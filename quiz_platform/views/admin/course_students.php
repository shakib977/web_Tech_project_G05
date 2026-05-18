<?php
// views/admin/course_students.php — MEMBER 4
require 'views/layout/header.php';
?>

<!-- Breadcrumb -->
<div class="flex gap-2" style="margin-bottom:20px;align-items:center;font-size:14px">
    <a href="index.php?page=admin&action=courses" style="color:var(--gray)">All Courses</a>
    <span style="color:var(--gray-light)">›</span>
    <a href="index.php?page=admin&action=course_manage&course_id=<?= $course['id'] ?>"
       style="color:var(--gray)"><?= htmlspecialchars($course['title']) ?></a>
    <span style="color:var(--gray-light)">›</span>
    <span class="fw-600">Students</span>
</div>

<!-- Summary Stats -->
<div class="stats-grid" style="margin-bottom:24px">
    <div class="stat-card">
        <div class="stat-icon icon-green">✅</div>
        <div class="stat-info"><h3><?= $active ?></h3><p>Enrolled</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon icon-yellow">⏳</div>
        <div class="stat-info"><h3><?= $pending ?></h3><p>Pending Approval</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon icon-red">❌</div>
        <div class="stat-info"><h3><?= $dropped ?></h3><p>Dropped</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon icon-blue">👥</div>
        <div class="stat-info"><h3><?= count($students) ?></h3><p>Total</p></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <span class="card-title">👥 Students — <?= htmlspecialchars($course['title']) ?></span>
            <div class="text-xs text-muted" style="margin-top:4px">
                All enrollment records for this course
            </div>
        </div>
        <a href="index.php?page=admin&action=course_manage&course_id=<?= $course['id'] ?>"
           class="btn btn-secondary btn-sm">← Back to Course</a>
    </div>

    <div class="card-body" style="padding:0">
        <?php if (empty($students)): ?>
            <div class="empty-state" style="padding:60px">
                <div class="empty-icon">👥</div>
                <h3>No students yet</h3>
                <p>No one has enrolled in this course.</p>
            </div>
        <?php else: ?>
        <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Student ID</th>
                    <th>Program</th>
                    <th>Status</th>
                    <th>Quizzes Taken</th>
                    <th>Avg Score</th>
                    <th>Enrolled Date</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($students as $s): ?>
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:10px">
                        <div style="width:36px;height:36px;border-radius:50%;
                                    background:var(--primary);color:white;
                                    display:flex;align-items:center;justify-content:center;
                                    font-size:14px;font-weight:700;flex-shrink:0">
                            <?= strtoupper(substr($s['name'], 0, 1)) ?>
                        </div>
                        <div>
                            <div class="fw-600 text-sm"><?= htmlspecialchars($s['name']) ?></div>
                            <div class="text-xs text-muted"><?= htmlspecialchars($s['email']) ?></div>
                        </div>
                    </div>
                </td>
                <td class="text-sm"><?= htmlspecialchars($s['student_id'] ?: '—') ?></td>
                <td class="text-sm text-gray"><?= htmlspecialchars($s['program'] ?: '—') ?></td>
                <td>
                    <span class="badge badge-<?= $s['enrollment_status']==='active' ? 'success' : ($s['enrollment_status']==='pending' ? 'warning' : 'danger') ?>">
                        <?= ucfirst($s['enrollment_status']) ?>
                    </span>
                </td>
                <td class="text-sm">
                    <?= $s['attempt_count'] ?> attempt<?= $s['attempt_count']!=1?'s':'' ?>
                </td>
                <td>
                    <?php if ($s['avg_score'] !== null): ?>
                    <span class="fw-bold <?= $s['avg_score'] >= 50 ? 'text-success' : 'text-danger' ?>">
                        <?= $s['avg_score'] ?>
                    </span>
                    <?php else: ?>
                    <span class="text-muted">—</span>
                    <?php endif; ?>
                </td>
                <td class="text-sm text-muted">
                    <?= date('M d, Y', strtotime($s['enrolled_at'])) ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require 'views/layout/footer.php'; ?>