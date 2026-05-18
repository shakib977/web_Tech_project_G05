<?php require 'views/layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <span class="card-title">📋 Student Results — <?= htmlspecialchars($course['title']) ?></span>
        <a href="index.php?page=ta&action=courses" class="btn btn-secondary btn-sm">← Back</a>
    </div>
    <div class="card-body" style="padding:0">
        <div class="table-wrap">
        <table>
            <thead><tr><th>Student</th><th>Quiz</th><th>Score</th><th>%</th><th>Status</th><th>Duration</th><th>Date</th></tr></thead>
            <tbody>
            <?php if (empty($results)): ?>
            <tr><td colspan="7" class="text-center text-muted" style="padding:30px">No attempts yet.</td></tr>
            <?php else: ?>
            <?php foreach ($results as $r): ?>
            <?php
                $pass = $r['score'] >= $r['pass_mark'];
                $pct  = $r['total_marks']>0 ? round(($r['score']/$r['total_marks'])*100) : 0;
                $dur  = $r['duration'];
            ?>
            <tr>
                <td><div class="fw-600 text-sm"><?= htmlspecialchars($r['student_name']) ?></div><div class="text-xs text-muted"><?= htmlspecialchars($r['student_id']) ?></div></td>
                <td class="text-sm"><?= htmlspecialchars($r['quiz_title']) ?></td>
                <td class="fw-bold"><?= round($r['score']) ?>/<?= $r['total_marks'] ?></td>
                <td><?= $pct ?>%</td>
                <td><span class="badge <?= $pass?'badge-success':'badge-danger' ?>"><?= $pass?'Pass':'Fail' ?></span></td>
                <td class="text-sm"><?= floor($dur/60) ?>m <?= $dur%60 ?>s</td>
                <td class="text-sm text-muted"><?= date('M d, H:i', strtotime($r['completed_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<?php require 'views/layout/footer.php'; ?>