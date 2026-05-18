<?php
// views/admin/profile.php — MEMBER 4
require 'views/layout/header.php';

$pic_src = ($user['profile_pic'] && $user['profile_pic'] !== 'default.png'
         && file_exists('uploads/profiles/' . $user['profile_pic']))
    ? BASE_URL . '/uploads/profiles/' . htmlspecialchars($user['profile_pic'])
    : '';
?>

<?php if (!empty($error)):   ?><div class="alert alert-danger">⚠️ <?= $error ?></div><?php endif; ?>
<?php if (!empty($success)): ?><div class="alert alert-success">✓ <?= $success ?></div><?php endif; ?>

<!-- Profile Banner -->
<div style="background:linear-gradient(135deg,var(--danger),#7C3AED);
            border-radius:var(--radius);padding:24px 28px;
            display:flex;align-items:center;gap:20px;
            margin-bottom:24px;color:white">
    <div style="width:72px;height:72px;border-radius:50%;
                overflow:hidden;border:3px solid rgba(255,255,255,.4);
                background:rgba(255,255,255,.2);flex-shrink:0;
                display:flex;align-items:center;justify-content:center">
        <?php if ($pic_src): ?>
            <img src="<?= $pic_src ?>?t=<?= time() ?>" alt=""
                 style="width:100%;height:100%;object-fit:cover">
        <?php else: ?>
            <span style="font-size:28px;font-weight:800">
                <?= strtoupper(substr($user['name'],0,1)) ?>
            </span>
        <?php endif; ?>
    </div>
    <div>
        <div style="font-size:20px;font-weight:800"><?= htmlspecialchars($user['name']) ?></div>
        <div style="opacity:.8;font-size:14px"><?= htmlspecialchars($user['email']) ?></div>
        <div style="opacity:.7;font-size:12px;margin-top:4px">
            🆔 <?= htmlspecialchars($user['student_id'] ?: 'Admin') ?>
        </div>
    </div>
</div>

<div class="grid-2">

    <!-- Edit Info -->
    <div class="card">
        <div class="card-header"><span class="card-title">✏️ Edit Info</span></div>
        <div class="card-body">
            <!-- IMPORTANT: no enctype needed here (no file) -->
            <form method="POST" action="index.php?page=admin&action=profile">
                <input type="hidden" name="form_type" value="info">
                <div class="form-group">
                    <label class="form-label">Full Name <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control"
                           value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email (read only)</label>
                    <input type="email" class="form-control"
                           value="<?= htmlspecialchars($user['email']) ?>" disabled>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" class="form-control"
                           value="<?= htmlspecialchars($user['phone']) ?>">
                </div>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
    </div>

    <!-- Photo Upload — SEPARATE form with enctype -->
    <div class="card">
        <div class="card-header"><span class="card-title">📷 Profile Photo</span></div>
        <div class="card-body">
            <!-- enctype="multipart/form-data" is REQUIRED for file uploads -->
            <form method="POST"
                  action="index.php?page=admin&action=profile"
                  enctype="multipart/form-data">
                <input type="hidden" name="form_type" value="photo">

                <?php if ($pic_src): ?>
                <div style="text-align:center;margin-bottom:16px">
                    <img src="<?= $pic_src ?>?t=<?= time() ?>" alt="Current photo"
                         style="width:80px;height:80px;border-radius:50%;
                                object-fit:cover;border:3px solid var(--border)">
                    <div class="text-xs text-muted" style="margin-top:6px">Current photo</div>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label">Choose New Photo</label>
                    <input type="file" name="profile_pic"
                           id="profile_pic_input"
                           class="form-control"
                           accept="image/jpeg,image/png,image/gif,image/webp">
                    <p class="form-text">JPG, PNG, GIF or WebP. Max 2MB.</p>
                </div>
                <button type="submit" class="btn btn-primary btn-full">
                    📷 Upload Photo
                </button>
            </form>
        </div>
    </div>

</div>

<?php require 'views/layout/footer.php'; ?>