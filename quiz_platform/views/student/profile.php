<?php
// views/student/profile.php — MEMBER 1
require 'views/layout/header.php';
$pic_url = file_exists('uploads/profiles/' . $user['profile_pic']) ? BASE_URL . '/uploads/profiles/' . htmlspecialchars($user['profile_pic']) : '';
?>

<?php if ($error): ?><div class="alert alert-danger">⚠️ <?= $error ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success">✓ <?= $success ?></div><?php endif; ?>

<!-- Profile Header -->
<div class="profile-header" style="margin-bottom:24px">
    <div class="profile-avatar-wrap">
        <?php if ($pic_url): ?>
            <img src="<?= $pic_url ?>" id="pic_preview" class="profile-avatar" alt="">
        <?php else: ?>
            <div class="profile-avatar" id="pic_preview"><?= strtoupper(substr($user['name'],0,1)) ?></div> 
        <?php endif; ?>
    </div>
    <div class="profile-info">
        <h2><?= htmlspecialchars($user['name']) ?></h2>
        <p><?= htmlspecialchars($user['email']) ?></p>
        <p><?= htmlspecialchars($user['program'] ?: 'No program set') ?> <?= $user['student_id'] ? '• ID: ' . htmlspecialchars($user['student_id']) : '' ?></p>
    </div>
</div>

<div class="grid-2">
    <!-- Edit Profile -->
    <div class="card">
        <div class="card-header"><span class="card-title">👤 Edit Profile</span></div>
        <div class="card-body">
            <form method="POST" action="index.php?page=student&action=profile">
                <input type="hidden" name="form_type" value="profile">
                <div class="form-group">
                    <label class="form-label">Full Name <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Program / Department</label>
                    <input type="text" name="program" class="form-control" value="<?= htmlspecialchars($user['program']) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Student ID</label> 
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['student_id']) ?>" disabled>
                    <p class="form-text">Student ID cannot be changed. Contact admin if needed.</p>
                </div>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
    </div> 

    <!-- Right Column -->
    <div>
        <!-- Profile Picture -->
        <div class="card"> 
            <div class="card-header"><span class="card-title">📷 Profile Picture</span></div>
            <div class="card-body">
                <form method="POST" action="index.php?page=student&action=profile" enctype="multipart/form-data">
                    <input type="hidden" name="form_type" value="photo">
                    <div class="form-group">
                        <label class="form-label">Upload New Photo</label>
                        <input type="file" id="profile_pic_input" name="profile_pic" class="form-control" accept="image/*">
                        <p class="form-text">JPG, PNG, GIF or WebP. Max 2MB.</p>
                    </div>
                    <button type="submit" class="btn btn-primary">Upload Photo</button>
                </form> 
            </div>
        </div>

        <!-- Change Password -->
        <div class="card">
            <div class="card-header"><span class="card-title">🔒 Change Password</span></div>
            <div class="card-body">
                <form method="POST" action="index.php?page=student&action=profile">
                    <input type="hidden" name="form_type" value="password">
                    <div class="form-group">
                        <label class="form-label">Current Password <span class="required">*</span></label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">New Password <span class="required">*</span></label>
                        <input type="password" name="new_password" class="form-control" placeholder="Min 8 characters" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm New Password <span class="required">*</span></label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require 'views/layout/footer.php'; ?>
