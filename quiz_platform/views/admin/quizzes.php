<?php require 'views/layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <span class="card-title">📝 All Quizzes (<?= count($quizzes) ?>)</span>
    </div>
    <div class="card-body" style="padding:14px 20px;border-bottom:1px solid var(--border)">
        <form method="GET" action="index.php" class="flex gap-3 flex-wrap">
            <input type="hidden" name="page" value="admin">
            <input type="hidden" name="action" value="quizzes">
            <select name="type" class="form-control" style="width:150px">
                <option value="">All Types</option>
                <option value="graded"   <?= ($_GET['type']??'')==='graded'  ?'selected':'' ?>>Graded</option>
                <option value="practice" <?= ($_GET['type']??'')==='practice'?'selected':'' ?>>Practice</option>
            </select>
            <select name="status" class="form-control" style="width:150px">
                <option value="">All Status</option>
                <option value="published" <?= ($_GET['status']??'')==='published'?'selected':'' ?>>Published</option>
                <option value="draft"     <?= ($_GET['status']??'')==='draft'    ?'selected':'' ?>>Draft</option>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="index.php?page=admin&action=quizzes" class="btn btn-secondary">Reset</a>


        </form>
    </div>
    <div class="card-body" style="padding:0">
        <div class="table-wrap">
        <table>
            <thead><tr><th>Title</th><th>Course</th><th>Type</th><th>Marks</th><th>Pass</th><th>Attempts</th><th>Status</th><th>Available</th></tr></thead>
            <tbody>
            <?php if (empty($quizzes)): ?>
            <tr><td colspan="8" class="text-center text-muted" style="padding:30px">No quizzes found.</td></tr>
            <?php else: ?>
            <?php foreach ($quizzes as $q): ?>
            <tr>
                <td class="fw-600 text-sm"><?= htmlspecialchars($q['title']) ?></td>
                <td class="text-sm text-gray"><?= htmlspecialchars($q['course_title']) ?></td>
                <td><span class="badge <?= $q['quiz_type']==='graded'?'badge-info':'badge-purple' ?>"><?= ucfirst($q['quiz_type']) ?></span></td>
                <td><?= $q['total_marks'] ?></td>
                <td><?= $q['pass_mark'] ?></td>
                <td><?= $q['attempt_count'] ?></td>
                <td><span class="badge <?= $q['status']==='published'?'badge-success':'badge-warning' ?>"><?= ucfirst($q['status']) ?></span></td>
                <td class="text-xs text-muted">
                    <?= $q['available_from'] ? date('M d', strtotime($q['available_from'])) : '—' ?>
                    <?= $q['available_until'] ? ' → '.date('M d', strtotime($q['available_until'])) : '' ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<?php require 'views/layout/footer.php'; ?>