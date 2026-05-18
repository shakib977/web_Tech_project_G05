<?php require 'views/layout/header.php'; ?>

<!-- Enrollments by Subject -->
<div class="card">
    <div class="card-header"><span class="card-title">📊 Enrollments by Subject</span></div>
    <div class="card-body">
        <?php if (empty($by_subject)): ?>
            <div class="empty-state"><div class="empty-icon">📊</div><p>No enrollment data yet.</p></div>
        <?php else: ?>
        <?php $max_e = max(array_column($by_subject, 'enrollments') ?: [1]); ?>
        <?php foreach ($by_subject as $s): ?>
        <?php $pct = $max_e > 0 ? round(($s['enrollments'] / $max_e) * 100) : 0; ?>
        <div style="margin-bottom:16px">
            <div class="flex-between mb-1" style="margin-bottom:5px">
                <span class="fw-600 text-sm"><?= htmlspecialchars($s['name']) ?></span>
                <span class="text-sm text-primary fw-bold"><?= $s['enrollments'] ?> students</span>
            </div>
            <div class="prog-wrap"><div class="prog-fill" style="width:<?= $pct ?>%"></div></div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Pass Rates by Subject -->
<div class="card">
    <div class="card-header"><span class="card-title">🎯 Quiz Pass Rates by Subject</span></div>
    <div class="card-body" style="padding:0">
        <div class="table-wrap">
        <table>
            <thead><tr><th>Subject</th><th>Total Attempts</th><th>Passed</th><th>Pass Rate</th></tr></thead>
            <tbody>
            <?php if (empty($pass_by_subject)): ?>
            <tr><td colspan="4" class="text-center text-muted" style="padding:30px">No quiz data yet.</td></tr>
            <?php else: ?>
            <?php foreach ($pass_by_subject as $s): ?>
            <?php $rate = $s['attempts'] > 0 ? round(($s['passed'] / $s['attempts']) * 100) : 0; ?>
            <tr>
                <td class="fw-600"><?= htmlspecialchars($s['name']) ?></td>
                <td><?= $s['attempts'] ?></td>
                <td><?= $s['passed'] ?></td>
                <td>
                    <div class="flex gap-2" style="align-items:center">
                        <span class="fw-bold <?= $rate>=60?'text-success':'text-danger' ?>"><?= $rate ?>%</span>
                        <div class="prog-wrap" style="width:80px"><div class="prog-fill" style="width:<?= $rate ?>%;background:<?= $rate>=60?'var(--success)':'var(--danger)' ?>"></div></div>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- Top Instructors -->
<div class="card">
    <div class="card-header"><span class="card-title">🏆 Most Active Instructors</span></div>
    <div class="card-body" style="padding:0">
        <div class="table-wrap">
        <table>
            <thead><tr><th>Instructor</th><th>Active Courses</th><th>Total Students</th></tr></thead>
            <tbody>
            <?php if (empty($top_instructors)): ?>
            <tr><td colspan="3" class="text-center text-muted" style="padding:30px">No instructors yet.</td></tr>
            <?php else: ?>
            <?php foreach ($top_instructors as $i => $inst): ?>
            <tr>
                <td>
                    <div class="flex gap-2" style="align-items:center">
                        <div style="width:32px;height:32px;border-radius:50%;background:var(--primary);color:white;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700"><?= strtoupper(substr($inst['name'],0,1)) ?></div>
                        <span class="fw-600"><?= htmlspecialchars($inst['name']) ?></span>
                    </div>
                </td>
                <td><?= $inst['courses'] ?></td>
                <td><?= $inst['students'] ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<?php require 'views/layout/footer.php'; ?>