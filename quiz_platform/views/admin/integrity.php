<?php require 'views/layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <span class="card-title">🔍 Academic Integrity Flags</span>
        <span class="badge badge-warning"><?= count(array_filter($flags, fn($f) => $f['status']==='pending')) ?> pending</span>
    </div>
    <div class="card-body" style="padding:0">
        <?php if (empty($flags)): ?>
            <div class="empty-state"><div class="empty-icon">✅</div><h3>No flags</h3><p>No integrity issues reported.</p></div>
        <?php else: ?>
        <div class="table-wrap">
        <table>
            <thead><tr><th>Student</th><th>Course</th><th>Reported By</th><th>Reason</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ($flags as $f): ?>
            <tr>
                <td class="fw-600 text-sm"><?= htmlspecialchars($f['reported_user'] ?? '—') ?></td>
                <td class="text-sm text-gray"><?= htmlspecialchars($f['course_title'] ?? '—') ?></td>
                <td class="text-sm"><?= htmlspecialchars($f['reporter_name'] ?? '—') ?></td>
                <td class="text-sm" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($f['reason']) ?></td>
                <td>
                    <span class="badge badge-<?= $f['status']==='pending'?'warning':($f['status']==='resolved'?'success':'danger') ?>">
                        <?= ucfirst($f['status']) ?>
                    </span>
                </td>
                <td class="text-xs text-muted"><?= date('M d, Y', strtotime($f['created_at'])) ?></td>
                <td>
                    <?php if ($f['status']==='pending'): ?>
                    <form method="POST" action="index.php?page=admin&action=resolve_flag" style="display:flex;gap:6px">
                        <input type="hidden" name="flag_id" value="<?= $f['id'] ?>">
                        <button name="flag_status" value="resolved"  class="btn btn-success btn-sm">Resolve</button>
                        <button name="flag_status" value="escalated" class="btn btn-danger  btn-sm">Escalate</button>
                    </form>
                    <?php else: ?>
                        <span class="text-xs text-muted">Done</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require 'views/layout/footer.php'; ?>