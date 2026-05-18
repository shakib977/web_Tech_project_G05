<?php require 'views/layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">📋 Attempts — <?= htmlspecialchars($quiz['title']) ?></div>
            <div class="text-xs text-muted"><?= htmlspecialchars($quiz['course_title']) ?> • <?= count($attempts) ?> total attempts</div>
        </div>
        <a href="index.php?page=instructor&action=manage_quiz&quiz_id=<?= $quiz['id'] ?>" class="btn btn-secondary btn-sm">← Back</a>
    </div>
    <div class="card-body" style="padding:0">
        <div class="table-wrap">
        <table>
            <thead><tr><th>Student</th><th>Student ID</th><th>Score</th><th>Percentage</th><th>Status</th><th>Duration</th><th>Date</th></tr></thead>
            <tbody>
            <?php if (empty($attempts)): ?>
            <tr><td colspan="7" class="text-center text-muted" style="padding:30px">No attempts yet.</td></tr>
            <?php else: ?>
            <?php foreach ($attempts as $a): ?>
            <?php
                $pass = $a['score'] >= $quiz['pass_mark'];
                $pct  = $quiz['total_marks']>0 ? round(($a['score']/$quiz['total_marks'])*100) : 0;
                $dur  = $a['duration_sec'];
            ?>
            <tr>
                <td class="fw-600"><?= htmlspecialchars($a['name']) ?></td>
                <td class="text-sm text-gray"><?= htmlspecialchars($a['student_id']) ?></td>
                <td class="fw-bold"><?= round($a['score']) ?> / <?= $quiz['total_marks'] ?></td>
                <td><?= $pct ?>%</td>
                <td><span class="badge <?= $pass?'badge-success':'badge-danger' ?>"><?= $pass?'Pass':'Fail' ?></span></td>
                <td class="text-sm"><?= floor($dur/60) ?>m <?= $dur%60 ?>s</td>
                <td class="text-sm text-muted"><?= date('M d, Y H:i', strtotime($a['completed_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<?php require 'views/layout/footer.php'; ?>