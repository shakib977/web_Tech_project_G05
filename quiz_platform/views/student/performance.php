<?php
// views/student/performance.php — MEMBER 1
require 'views/layout/header.php';

function getGrade($pct) {
    if ($pct >= 90) return ['A+', '#059669'];
    if ($pct >= 80) return ['B+', '#2563EB'];
    if ($pct >= 70) return ['C+', '#D97706'];
    if ($pct >= 50) return ['D+', '#7C3AED'];
    return ['F',   '#DC2626'];
}

$pass_rate = $overall['total'] > 0
           ? round(($overall['passed'] / $overall['total']) * 100)
           : 0;
?>

<!-- Top Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon icon-blue">✏️</div>
        <div class="stat-info"><h3><?= $overall['total'] ?></h3><p>Total Attempts</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon icon-green">📊</div>
        <div class="stat-info">
            <h3><?= $overall['avg_pct'] ?? 0 ?>%</h3>
            <p>Average Score</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon icon-purple">🏆</div>
        <div class="stat-info"><h3><?= $overall['passed'] ?></h3><p>Passed</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon icon-yellow">🎯</div>
        <div class="stat-info"><h3><?= $pass_rate ?>%</h3><p>Pass Rate</p></div>
    </div>
</div>

<div class="grid-2">

    <!-- Top Quiz Scores -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">🏆 Top Quiz Scores</span>
            <span class="text-xs text-muted">Best score per quiz</span>
        </div>
        <div class="card-body" style="padding:0">
            <?php if (empty($top_scores)): ?>
            <div class="empty-state" style="padding:40px">
                <div class="empty-icon">🏆</div>
                <p>No quiz attempts yet.</p>
            </div>
            <?php else: ?>
            <?php foreach ($top_scores as $i => $s): ?>
            <?php [$grade, $gc] = getGrade($s['score_pct']); ?>
            <div style="display:flex;align-items:center;gap:14px;
                        padding:13px 20px;border-bottom:1px solid var(--border)">
                <!-- Rank -->
                <div style="width:28px;height:28px;border-radius:50%;
                            background:<?= $i===0?'#FEF3C7':($i===1?'#F3F4F6':($i===2?'#FEF3C7':'var(--light-2)')) ?>;
                            display:flex;align-items:center;justify-content:center;
                            font-size:12px;font-weight:800;flex-shrink:0;
                            color:<?= $i===0?'#92400E':($i===1?'#374151':($i===2?'#B45309':'var(--gray)')) ?>">
                    <?= $i+1 ?>
                </div>
                <!-- Info -->
                <div style="flex:1;min-width:0">
                    <div class="fw-600 text-sm"
                         style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        <?= htmlspecialchars($s['quiz_title']) ?>
                    </div>
                    <div class="text-xs text-muted" style="margin-top:2px">
                        📚 <?= htmlspecialchars($s['course_title']) ?>
                    </div>
                </div>
                <!-- Score % + Grade -->
                <div style="text-align:right;flex-shrink:0">
                    <div style="font-size:18px;font-weight:900;color:<?= $gc ?>">
                        <?= $s['score_pct'] ?>%
                    </div>
                    <div style="font-size:11px;font-weight:700;color:<?= $gc ?>">
                        <?= $grade ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- By Subject -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">📊 Performance by Subject</span>
        </div>
        <div class="card-body">
            <?php if (empty($by_subject)): ?>
            <div class="empty-state" style="padding:30px">
                <p>No data yet.</p>
            </div>
            <?php else: ?>
            <?php foreach ($by_subject as $s): ?>
            <div style="margin-bottom:18px">
                <div style="display:flex;justify-content:space-between;margin-bottom:5px">
                    <span class="fw-600 text-sm"><?= htmlspecialchars($s['subject']) ?></span>
                    <span class="text-sm text-primary fw-bold"><?= $s['avg_pct'] ?>%</span>
                </div>
                <div class="prog-wrap">
                    <div class="prog-fill" style="width:<?= min(100,$s['avg_pct']) ?>%"></div>
                </div>
                <div class="text-xs text-muted" style="margin-top:3px">
                    <?= $s['attempt_count'] ?> attempt<?= $s['attempt_count']!=1?'s':'' ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php require 'views/layout/footer.php'; ?>