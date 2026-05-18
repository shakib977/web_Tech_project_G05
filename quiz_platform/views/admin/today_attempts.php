<?php
// views/admin/today_attempts.php — MEMBER 4
require 'views/layout/header.php';

function getGrade($pct) {
    if ($pct >= 90) return ['A+', '#059669'];
    if ($pct >= 80) return ['B+', '#2563EB'];
    if ($pct >= 70) return ['C+', '#D97706'];
    if ($pct >= 50) return ['D+', '#7C3AED'];
    return ['F',   '#DC2626'];
}
?>

<div class="stats-grid" style="margin-bottom:20px">
    <div class="stat-card">
        <div class="stat-icon icon-blue">✏️</div>
        <div class="stat-info"><h3><?= $total ?></h3><p>Total Attempts Today</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon icon-green">✅</div>
        <div class="stat-info"><h3><?= $completed ?></h3><p>Completed</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon icon-yellow">🏆</div>
        <div class="stat-info"><h3><?= $passed ?></h3><p>Passed</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon icon-red">❌</div>
        <div class="stat-info"><h3><?= $completed - $passed ?></h3><p>Failed</p></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">📋 Today's Quiz Attempts — <?= date('M d, Y') ?></span>
        <a href="index.php?page=admin&action=dashboard" class="btn btn-secondary btn-sm">← Dashboard</a>
    </div>
    <div class="card-body" style="padding:0">
        <?php if (empty($attempts)): ?>
        <div class="empty-state" style="padding:60px">
            <div class="empty-icon">📝</div>
            <h3>No attempts today</h3>
            <p>No quiz attempts have been made today.</p>
        </div>
        <?php else: ?>
        <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Quiz</th>
                    <th>Course</th>
                    <th>Score</th>
                    <th>Grade</th>
                    <th>Status</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($attempts as $a): ?>
            <?php
                $pct  = $a['total_marks'] > 0
                      ? round(($a['score'] / $a['total_marks']) * 100)
                      : 0;
                $pass = $a['completed_at'] && $a['score'] >= $a['pass_mark'];
                [$grade, $gc] = getGrade($pct);
            ?>
            <tr>
                <td>
                    <div class="fw-600 text-sm"><?= htmlspecialchars($a['student_name']) ?></div>
                    <div class="text-xs text-muted"><?= htmlspecialchars($a['student_id']) ?></div>
                </td>
                <td class="text-sm"><?= htmlspecialchars($a['quiz_title']) ?></td>
                <td class="text-sm text-gray"><?= htmlspecialchars($a['course_title']) ?></td>
                <td class="fw-bold">
                    <?= $a['completed_at'] ? round($a['score']).'/'.$a['total_marks'] : '—' ?>
                </td>
                <td>
                    <?php if ($a['completed_at']): ?>
                    <span style="font-weight:900;font-size:15px;color:<?= $gc ?>">
                        <?= $grade ?>
                    </span>
                    <?php else: ?>
                    <span class="badge badge-warning">In Progress</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($a['completed_at']): ?>
                    <span class="badge badge-<?= $pass?'success':'danger' ?>">
                        <?= $pass ? 'Pass' : 'Fail' ?>
                    </span>
                    <?php else: ?>
                    <span class="badge badge-warning">In Progress</span>
                    <?php endif; ?>
                </td>
                <td class="text-sm text-muted">
                    <?= date('H:i:s', strtotime($a['started_at'])) ?>
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