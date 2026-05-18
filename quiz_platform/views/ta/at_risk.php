<?php
// views/ta/at_risk.php — MEMBER 3
require 'views/layout/header.php';
?>

<div class="card">
    <div class="card-header">
        <span class="card-title">⚠️ At-Risk Students</span>
        <span class="badge badge-warning">Threshold: below <?= $threshold ?>%</span>
    </div>
    <div class="card-body" style="padding:0">
        <?php if (empty($at_risk)): ?>
            <div class="empty-state"><div class="empty-icon">✅</div><h3>No at-risk students</h3><p>All students are performing above the threshold.</p></div>
        <?php else: ?>
            <div class="table-wrap">
            <table>
                <thead><tr><th>Student</th><th>Course</th><th>Avg Score</th><th>Attempts</th><th>Action</th></tr></thead>
                <tbody>
                <?php foreach ($at_risk as $s): ?>
                <tr>
                    <td>
                        <div class="fw-600"><?= htmlspecialchars($s['name']) ?></div>
                        <div class="text-xs text-muted"><?= htmlspecialchars($s['email']) ?></div>
                    </td>
                    <td class="text-sm"><?= htmlspecialchars($s['course_title']) ?></td>
                    <td>
                        <span class="fw-bold text-danger"><?= $s['avg_score'] ?>%</span>
                        <div class="prog-wrap" style="width:80px;margin-top:4px">
                            <div class="prog-fill" style="width:<?= $s['avg_score'] ?>%;background:var(--danger)"></div>
                        </div>
                    </td>
                    <td><?= $s['attempt_count'] ?></td>
                    <td>
                        <button class="btn btn-warning btn-sm"
                                id="flag_btn_<?= $s['id'] ?>_<?= $s['course_id'] ?>"
                                onclick="flagStudent(<?= $s['id'] ?>, <?= $s['course_id'] ?>, this)">
                            🚩 Flag for Review
                        </button>
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
