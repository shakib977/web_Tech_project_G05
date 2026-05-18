
<?php
// views/student/dashboard.php — MEMBER 1


require 'views/layout/header.php';
?>


<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon icon-blue">📚</div>
        <div class="stat-info"><h3><?= $total_courses ?></h3><p>Enrolled Courses</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon icon-green">✏️</div>
        <div class="stat-info"><h3><?= $total_attempts ?></h3><p>Quizzes attempts</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon icon-yellow">⭐</div>
        <div class="stat-info"><h3><?= round($avg_score, 1) ?></h3><p>Average Score</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon icon-purple">🏆</div>
        <div class="stat-info"><h3><?= $passed ?></h3><p>Quizzes Passed</p></div>
    </div>
</div>

<div class="grid-2">
    <!-- My Courses -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">📚 My Courses</span>
            <a href="index.php?page=student&action=browse_courses" class="btn btn-outline btn-sm">Browse More</a>
        </div>
        <div class="card-body" style="padding:0">
            <?php if (empty($courses)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📖</div>
                    <h3>No courses yet</h3>
                    <p>Browse and enroll in courses to get started.</p>
                    <a href="index.php?page=student&action=browse_courses" class="btn btn-primary">Browse Courses</a>
                </div>
            <?php else: ?>
                <?php foreach ($courses as $c): ?>
                <a href="index.php?page=student&action=course_detail&course_id=<?= $c['id'] ?>"
                   style="display:flex;align-items:center;gap:14px;padding:14px 20px;border-bottom:1px solid var(--border);text-decoration:none;transition:var(--transition)" onmouseover="this.style.background='var(--light)'" onmouseout="this.style.background=''">
                    <div style="width:44px;height:44px;border-radius:10px;background:linear-gradient(135deg,var(--primary),var(--secondary));display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0">📚</div>
                    <div style="flex:1;min-width:0">
                        <div class="fw-600" style="font-size:14px;color:var(--dark);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($c['title']) ?></div>
                        <div class="text-xs text-muted"><?= htmlspecialchars($c['subject']) ?> • <?= htmlspecialchars($c['instructor']) ?></div>
                    </div>
                    <span class="badge badge-info"><?= $c['quiz_count'] ?> quiz<?= $c['quiz_count'] != 1 ? 'zes' : '' ?></span>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Attempts -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">🕒 recent Attempts</span>
            <a href="index.php?page=student&action=attempt_history" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="card-body" style="padding:0">
            <?php if (empty($recent_attempts)): ?>
                <div class="empty-state">
                    <div class="empty-icon">✏️</div>
                    <h3>No attempts yet</h3>
                    <p>Take your first quiz to see results here.</p>
                </div>
            <?php else: ?>
                <?php foreach ($recent_attempts as $a): ?>
                <?php $pass = $a['score'] >= $a['pass_mark']; ?>
                <div style="display:flex;align-items:center;gap:14px;padding:14px 20px;border-bottom:1px solid var(--border)">
                    <div style="width:44px;height:44px;border-radius:50%;background:<?= $pass ? '#D1FAE5' : '#FEE2E2' ?>;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:800;color:<?= $pass ? 'var(--success)' : 'var(--danger)' ?>;flex-shrink:0">
                        <?= round($a['score']) ?>
                    </div>
                    <div style="flex:1;min-width:0">
                        <div class="fw-600 text-sm" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($a['quiz_title']) ?></div>
                        <div class="text-xs text-muted"><?= htmlspecialchars($a['course_title']) ?> • <?= date('M d', strtotime($a['completed_at'])) ?></div>
                    </div>
                    <span class="badge <?= $pass ? 'badge-success' : 'badge-danger' ?>"><?= $pass ? 'Pass' : 'Fail' ?></span>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require 'views/layout/footer.php'; ?>
