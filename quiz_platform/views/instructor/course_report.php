<?php require 'views/layout/header.php'; ?>

<div class="stats-grid">
    <div class="stat-card"><div class="stat-icon icon-blue">👥</div><div class="stat-info"><h3><?= $enrolled ?></h3><p>Enrolled Students</p></div></div>
    <div class="stat-card"><div class="stat-icon icon-red">⬇️</div><div class="stat-info"><h3><?= $dropped ?></h3><p>Dropped Out</p></div></div>
    <div class="stat-card"><div class="stat-icon icon-green">📝</div><div class="stat-info"><h3><?= count($quiz_stats) ?></h3><p>Total Quizzes</p></div></div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">📊 Quiz Performance — <?= htmlspecialchars($course['title']) ?></span>
        <a href="index.php?page=instructor&action=edit_course&course_id=<?= $course['id'] ?>" class="btn btn-secondary btn-sm">← Back</a>
    </div>
    <div class="card-body" style="padding:0">
        <div class="table-wrap">
        <table>
            <thead><tr><th>Quiz</th><th>Attempts</th><th>Avg Score</th><th>Passed</th><th>Pass Rate</th></tr></thead>
            <tbody>
            <?php foreach ($quiz_stats as $q): ?>
            <?php $rate = $q['attempts']>0 ? round(($q['passed']/$q['attempts'])*100) : 0; ?>
            <tr>
                <td class="fw-600"><?= htmlspecialchars($q['title']) ?></td>
                <td><?= $q['attempts'] ?></td>
                <td><?= $q['avg_score'] ?> / <?= $q['total_marks'] ?></td>
                <td><?= $q['passed'] ?></td>
                <td><span class="badge <?= $rate>=60?'badge-success':'badge-danger' ?>"><?= $rate ?>%</span></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($quiz_stats)): ?>
            <tr><td colspan="5" class="text-center text-muted" style="padding:30px">No quizzes yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<?php require 'views/layout/footer.php'; ?>