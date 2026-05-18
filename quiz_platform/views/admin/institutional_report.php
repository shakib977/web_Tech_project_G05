<?php require 'views/layout/header.php'; ?>

<div class="card" style="margin-bottom:20px">
    <div class="card-header"><span class="card-title">📅 Date Range</span></div>
    <div class="card-body">
        <form method="GET" action="index.php" class="flex gap-3 flex-wrap">
            <input type="hidden" name="page" value="admin">
            <input type="hidden" name="action" value="institutional_report">
            <div class="form-group" style="margin:0">
                <label class="form-label">From</label>
                <input type="date" name="from" class="form-control" value="<?= htmlspecialchars($from) ?>">
            </div>
            <div class="form-group" style="margin:0">
                <label class="form-label">To</label>
                <input type="date" name="to" class="form-control" value="<?= htmlspecialchars($to) ?>">
            </div>
            <div style="align-self:flex-end"><button type="submit" class="btn btn-primary">Generate</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">📊 Institutional Report (<?= $from ?> → <?= $to ?>)</span>
    </div>
    <div class="card-body" style="padding:0">
        <div class="table-wrap">
        <table>
            <thead><tr><th>Subject</th><th>Courses</th><th>Enrollments</th><th>Total Attempts</th><th>Avg Score</th><th>Passed</th><th>Pass Rate</th></tr></thead>
            <tbody>
            <?php if (empty($report)): ?>
            <tr><td colspan="7" class="text-center text-muted" style="padding:30px">No data for this period.</td></tr>
            <?php else: ?>
            <?php foreach ($report as $r): ?>
            <?php $rate = $r['attempts'] > 0 ? round(($r['passed'] / $r['attempts']) * 100) : 0; ?>
            <tr>
                <td class="fw-600"><?= htmlspecialchars($r['subject']) ?></td>
                <td><?= $r['courses'] ?></td>
                <td><?= $r['enrollments'] ?></td>
                <td><?= $r['attempts'] ?></td>
                <td><?= $r['avg_score'] ?: '—' ?></td>
                <td><?= $r['passed'] ?></td>
                <td><span class="badge <?= $rate>=60?'badge-success':'badge-danger' ?>"><?= $rate ?>%</span></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<?php require 'views/layout/footer.php'; ?>