<?php
// views/admin/users.php — MEMBER 4
require 'views/layout/header.php';
?>

<?php if (!empty($_GET['msg'])): ?>
<div class="alert alert-success">✅ <?= htmlspecialchars($_GET['msg']) ?></div>
<?php endif; ?>
<?php if (!empty($_GET['err'])): ?>
<div class="alert alert-danger">
    ⚠️
    <?php
    $errs = [
        'self'     => 'You cannot deactivate your own account.',
        'admin'    => 'Admin accounts cannot be deactivated.',
        'notfound' => 'User not found.',
    ];
    echo $errs[$_GET['err']] ?? 'Action failed.';
    ?>
</div>
<?php endif; ?>

<!-- Filters only — NO create buttons here -->
<div class="card">
    <div class="card-header">
        <span class="card-title">👥 All Users (<?= count($users) ?>)</span>
    </div>

    <!-- Search + Role Filter -->
    <div class="card-body"
         style="padding:14px 20px;border-bottom:1px solid var(--border)">
        <form method="GET" action="index.php" class="flex gap-3 flex-wrap">
            <input type="hidden" name="page"   value="admin">
            <input type="hidden" name="action" value="users">
            <input type="text" name="search"
                   class="form-control" style="flex:2;min-width:180px"
                   placeholder="Search name, email, ID..."
                   value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <select name="role" class="form-control" style="width:160px"
                    onchange="this.form.submit()">
                <option value="">All Roles</option>
                <option value="student"    <?= ($_GET['role']??'')==='student'   ?'selected':'' ?>>Students</option>
                <option value="instructor" <?= ($_GET['role']??'')==='instructor'?'selected':'' ?>>Instructors</option>
                <option value="ta"         <?= ($_GET['role']??'')==='ta'        ?'selected':'' ?>>TAs</option>
                <option value="admin"      <?= ($_GET['role']??'')==='admin'     ?'selected':'' ?>>Admins</option>
            </select>
            
            <a href="index.php?page=admin&action=users" class="btn btn-secondary">Reset</a>
        </form>
    </div>

    <div class="card-body" style="padding:0">
        <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Program</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($users)): ?>
            <tr>
                <td colspan="8" class="text-center text-muted"
                    style="padding:40px">
                    No users found.
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($users as $u): ?>
            <tr>
                <td>
                    <code style="background:var(--light-2);padding:2px 8px;
                                 border-radius:4px;font-size:12px;font-weight:700;
                                 color:var(--primary)">
                        <?= htmlspecialchars($u['student_id'] ?: '—') ?>
                    </code>
                </td>
                <td class="fw-600"><?= htmlspecialchars($u['name']) ?></td>
                <td class="text-sm text-gray"><?= htmlspecialchars($u['email']) ?></td>
                <td>
                    <span class="badge badge-<?= $u['role']==='admin'?'danger':($u['role']==='instructor'?'info':($u['role']==='ta'?'warning':'success')) ?>">
                        <?= ucfirst($u['role']) ?>
                    </span>
                </td>
                <td class="text-sm text-gray">
                    <?= htmlspecialchars($u['program'] ?: '—') ?>
                </td>
                <td>
                    <span class="badge badge-<?= $u['is_active']?'success':'danger' ?>">
                        <?= $u['is_active'] ? 'Active' : 'Inactive' ?>
                    </span>
                </td>
                <td class="text-sm text-muted">
                    <?= date('M d, Y H:i', strtotime($u['created_at'])) ?>
                </td>
                <td>
                    <?php if ($u['role'] === 'admin'): ?>
                        <span class="text-xs text-muted">Protected</span>
                    <?php elseif ($u['id'] == $_SESSION['user_id']): ?>
                        <span class="text-xs text-muted">Current</span>
                    <?php else: ?>
                        <a href="index.php?page=admin&action=toggle_user&user_id=<?= $u['id'] ?>"
                           class="btn <?= $u['is_active']?'btn-danger':'btn-success' ?> btn-sm"
                           onclick="return confirm('<?= $u['is_active']?'Deactivate':'Activate' ?> <?= htmlspecialchars(addslashes($u['name'])) ?>?')">
                            <?= $u['is_active'] ? 'Deactivate' : 'Activate' ?>
                        </a>
                    <?php endif; ?>
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