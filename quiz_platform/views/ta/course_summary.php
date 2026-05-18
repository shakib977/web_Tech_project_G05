<?php require 'views/layout/header.php'; ?>

<div class="stats-grid">
    <div class="stat-card"><div class="stat-icon icon-blue">👥</div><div class="stat-info"><h3><?= $total_students ?></h3><p>Enrolled Students</p></div></div>
    <div class="stat-card"><div class="stat-icon icon-green">✏️</div><div class="stat-info"><h3><?= $attempted ?></h3><p>Students Attempted</p></div></div>
    <div class="stat-card"><div class="stat-icon icon-yellow">⭐</div><div class="stat-info"><h3><?= $avg_score ?: '—' ?></h3><p>Average Score</p></div></div>
    <div class="stat-card"><div class="stat-icon icon-red">⚠️</div><div class="stat-info"><h3><?= $at_risk_count ?></h3><p>At-Risk (below <?= $threshold ?>%)</p></div></div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">📊 Summary — <?= htmlspecialchars($course['title']) ?></span>
        <a href="index.php?page=ta&action=courses" class="btn btn-secondary btn-sm">← Back</a>
    </div>
    <div class="card-body">
        <?php $attempt_rate = $total_students>0 ? round(($attempted/$total_students)*100) : 0; ?>
        <p class="fw-600 mb-2" style="margin-bottom:8px">Quiz Attempt Rate</p>
        <div class="prog-wrap mb-4" style="margin-bottom:16px"><div class="prog-fill" style="width:<?= $attempt_rate ?>%"></div></div>
        <p class="text-sm text-muted"><?= $attempted ?> out of <?= $total_students ?> students (<?= $attempt_rate ?>%) have attempted at least one quiz.</p>
    </div>
</div>

<?php require 'views/layout/footer.php'; ?>