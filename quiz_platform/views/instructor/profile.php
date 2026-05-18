<?php require 'views/layout/header.php'; ?>

<?php if (!empty($error)):   ?><div class="alert alert-danger">⚠️ <?= $error ?></div><?php endif; ?>
<?php if (!empty($success)): ?><div class="alert alert-success">✓ <?= $success ?></div><?php endif; ?>

<div style="max-width:600px">
<div class="card">
    
        <form method="POST" action="index.php?page=instructor&action=profile" enctype="multipart/form-data">
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
            <div class="form-group">
                <label class="form-label">Department / Program</label>
                <input type="text" name="program" class="form-control" value="<?= htmlspecialchars($user['program']) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Profile Picture</label>
                <input type="file" id="profile_pic_input" name="profile_pic" class="form-control" accept="image/*">
                <p class="form-text">Max 2MB. JPG, PNG, GIF or WebP.</p>
            </div>
            <button type="submit" class="btn btn-primary">Save Profile</button>
        </form>
    </div>
</div>
</div>

<?php require 'views/layout/footer.php'; ?>