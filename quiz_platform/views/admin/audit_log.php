<?php
// views/admin/audit_log.php — MEMBER 4
require 'views/layout/header.php';
?>

<div class="card">
    <div class="card-header"><span class="card-title">📋 Admin Audit Log</span><span class="text-sm text-muted">Last 200 actions</span></div>
    <div class="card-body" style="padding:0">
        <div class="table-wrap">
        <table>
            <thead><tr><th>#</th><th>Admin</th><th>Action</th><th>Time</th></tr></thead>
            <tbody>
            <?php foreach ($logs as $i => $l): ?>
            <tr>
                <td class="text-muted text-sm"><?= $i+1 ?></td>
                <td class="fw-600 text-sm"><?= htmlspecialchars($l['admin_name']) ?></td>
                <td class="text-sm"><?= htmlspecialchars($l['action']) ?></td>
                <td class="text-sm text-muted"><?= date('M d, Y H:i:s', strtotime($l['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($logs)): ?>
            <tr><td colspan="4" class="text-center text-muted" style="padding:30px">No log entries yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<?php require 'views/layout/footer.php'; ?>
