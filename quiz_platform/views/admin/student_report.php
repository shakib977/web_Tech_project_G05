<?php require 'views/layout/header.php'; ?>

<div class="card" style="margin-bottom:20px">
    <div class="card-header"><span class="card-title">🔍 Search Student</span></div>
    <div class="card-body">
        <form method="GET" action="index.php" class="flex gap-3">
            <input type="hidden" name="page" value="admin">
            <input type="hidden" name="action" value="student_report">
            <input type="text" name="search" class="form-control" placeholder="Search by name, email, or student ID..."
                   value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
        <?php if (!empty($users)): ?>
        <div style="margin-top:16px">
            <?php foreach ($users as $u): ?>
            <a href="index.php?page=admin&action=student_report&search=<?= urlencode($_GET['search']??'') ?>&uid=<?= $u['id'] ?>"
               class="btn btn-outline btn-sm" style="margin:4px"><?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['student_id']) ?>)</a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($selected_user): ?>
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title"><?= htmlspecialchars($selected_user['name']) ?></div>
            <div class="text-xs text-muted"><?= htmlspecialchars($selected_user['email']) ?> • <?= htmlspecialchars($selected_user['student_id']) ?> • <?= htmlspecialchars($selected_user['program']) ?></div>
        </div>
    </div>
    <div class="card-body" style="padding:0">
        <div class="table-wrap">
        <table>
            <thead><tr><th>Course</th><th>Subject</th><th>Attempts</th><th>Avg Score</th></tr></thead>
            <tbody>
            <?php if (empty($user_stats)): ?>
            <tr><td colspan="4" class="text-center text-muted" style="padding:30px">No data for this student.</td></tr>
            <?php else: ?>
            <?php foreach ($user_stats as $s): ?>
            <tr>
                <td class="fw-600 text-sm"><?= htmlspecialchars($s['course_title']) ?></td>
                <td class="text-sm text-gray"><?= htmlspecialchars($s['subject']) ?></td>
                <td><?= $s['attempts'] ?></td>
                <td class="fw-bold <?= ($s['avg_score']??0)>=50?'text-success':'text-danger' ?>"><?= $s['avg_score'] ?: '—' ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require 'views/layout/footer.php'; ?>