
<?php
// views/admin/dashboard.php — MEMBER 4
require 'views/layout/header.php';
?>

<div class="stats-grid">
    <div class="stat-card"><div class="stat-icon icon-blue">👨‍🎓</div><div class="stat-info"><h3><?= $role_counts['student'] ?? 0 ?></h3><p>Students</p></div></div>
    <div class="stat-card"><div class="stat-icon icon-purple">👨‍🏫</div><div class="stat-info"><h3><?= $role_counts['instructor'] ?? 0 ?></h3><p>Instructors</p></div></div>
    <div class="stat-card"><div class="stat-icon icon-green">📚</div><div class="stat-info"><h3><?= $active_courses ?></h3><p>Active Courses</p></div></div>
    <a href="index.php?page=admin&action=today_attempts" style="text-decoration:none">
    <div class="stat-card" style="cursor:pointer">
        <div class="stat-icon icon-yellow">✏️</div>
        <div class="stat-info">
            <h3><?= $attempts_today ?></h3>
            <p>Attempts Today
                <span style="display:block;font-size:11px;color:var(--primary);
                             font-weight:700;margin-top:2px">
                    Click to view →
                </span>
            </p>
        </div>
    </div>
</a>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header">
            <span class="card-title">👥 Recent Registrations</span>
            <a href="index.php?page=admin&action=users" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="card-body" style="padding:0">
            <div class="table-wrap">
            <table>
                <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th></tr></thead>
                <tbody>
                <?php foreach ($recent_users as $u): ?>
                <tr>
                    <td class="fw-600"><?= htmlspecialchars($u['name']) ?></td>
                    <td class="text-gray text-sm"><?= htmlspecialchars($u['email']) ?></td>
                    <td><span class="badge badge-<?= $u['role']==='admin'?'danger':($u['role']==='instructor'?'info':($u['role']==='ta'?'warning':'success')) ?>"><?= ucfirst($u['role']) ?></span></td>
                    <td class="text-sm text-muted"><?= date('M d', strtotime($u['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>

    <div class="card">
    <div class="card-header"><span class="card-title">⚡ Quick Actions</span></div>
    <div class="card-body" style="display:flex;flex-direction:column;gap:10px">
        <!-- Changed from "Manage Instructors" to "Create Instructor" -->
        <a href="index.php?page=admin&action=create_instructor"
           class="btn btn-primary">👨‍🏫 Create Instructor</a>
        <a href="index.php?page=admin&action=create_ta"
           class="btn btn-info">🎓 Create TA</a>
        <a href="index.php?page=admin&action=integrity"
           class="btn btn-warning">
            🔍 Review Flags
            <?php if ($pending_flags): ?>
                <span class="badge badge-danger" style="margin-left:6px">
                    <?= $pending_flags ?>
                </span>
            <?php endif; ?>
        </a>
        <a href="index.php?page=admin&action=analytics"
           class="btn btn-secondary">📊 View Analytics</a>
        <a href="index.php?page=admin&action=settings"
           class="btn btn-secondary">⚙️ Platform Settings</a>
    </div>
</div>
</div>

<?php require 'views/layout/footer.php'; ?>

