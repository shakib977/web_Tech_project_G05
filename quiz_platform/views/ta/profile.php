<?php require 'views/layout/header.php'; ?>

<?php if (!empty($error)):   ?><div class="alert alert-danger">⚠️ <?= $error ?></div><?php endif; ?>
<?php if (!empty($success)): ?><div class="alert alert-success">✓ <?= $success ?></div><?php endif; ?>

<div style="max-width:500px">
<div class="card">
    <div class="card-header"><span class="card-title">👤 TA Profile</span></div>
    <div class="card-body">
        <form method="POST" action="index.php?page=ta&action=profile">
            <div class="form-group">
                <label class="form-label">Full Name <span class="required">*</span></label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email (read only)</label>
                <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
            </div>
            <div class="form-group">
                <label class="form-label">Phone</label>
                <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>">
            </div>
            <button type="submit" class="btn btn-primary">Save Profile</button>
        </form>
    </div>
</div>
</div>

<?php require 'views/layout/footer.php'; ?>