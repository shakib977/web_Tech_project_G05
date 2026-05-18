<?php
// views/admin/create_instructor.php — MEMBER 4
require 'views/layout/header.php';
?>

<?php if (!empty($error)):   ?><div class="alert alert-danger">⚠️ <?= $error ?></div><?php endif; ?>
<?php if (!empty($success)): ?><div class="alert alert-success">✓ <?= $success ?></div><?php endif; ?>

<div style="max-width:500px">
<div class="card">
    <div class="card-header">
        <span class="card-title">👨‍🏫 Create Instructor Account</span>
        <a href="index.php?page=admin&action=users&role=instructor"
           class="btn btn-secondary btn-sm">← View Instructors</a>
    </div>
    <div class="card-body">

        <div style="background:var(--light);border-radius:var(--radius-sm);
                    padding:12px 16px;margin-bottom:20px;
                    border-left:4px solid var(--primary)">
            <p class="text-sm">
                🆔 Instructor ID will be <strong>auto-generated</strong>
                (format: ins-1, ins-2...) after account creation.
            </p>
        </div>

        <form method="POST" action="index.php?page=admin&action=create_instructor"
              autocomplete="off">
            <div class="form-group">
                <label class="form-label">Full Name <span class="required">*</span></label>
                <input type="text" name="name" class="form-control"
                       placeholder="Dr. John Smith" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email <span class="required">*</span></label>
                <input type="email" name="email" class="form-control"
                       placeholder="instructor@university.edu" required>
            </div>
            <div class="form-group">
                <label class="form-label">Department</label>
                <input type="text" name="program" class="form-control"
                       placeholder="Computer Science Department">
            </div>
            <div class="form-group">
                <label class="form-label">Phone</label>
                <input type="tel" name="phone" class="form-control"
                       placeholder="+880 1700 000000">
            </div>
            <div class="form-group">
                <label class="form-label">Password <span class="required">*</span></label>
                <input type="password" name="password" class="form-control"
                       placeholder="Min 8 characters"
                       autocomplete="new-password"
                       required>
                <p class="form-text">
                    Instructor can change this password after logging in.
                </p>
            </div>
            <button type="submit" class="btn btn-primary btn-full">
                Create Instructor Account
            </button>
        </form>
    </div>
</div>
</div>

<?php require 'views/layout/footer.php'; ?>