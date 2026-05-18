<?php
// views/admin/create_ta.php — MEMBER 4
require 'views/layout/header.php';
?>

<?php if (!empty($error)):   ?><div class="alert alert-danger">⚠️ <?= $error ?></div><?php endif; ?>
<?php if (!empty($success)): ?><div class="alert alert-success">✓ <?= $success ?></div><?php endif; ?>

<div style="max-width:480px">
<div class="card">
    <div class="card-header">
        <span class="card-title">🎓 Create TA Account</span>
        <a href="index.php?page=admin&action=users&role=ta"
           class="btn btn-secondary btn-sm">← View TAs</a>
    </div>
    <div class="card-body">
        

        <!-- autocomplete="off" prevents browser filling admin credentials -->
        <form method="POST" action="index.php?page=admin&action=create_ta"
              autocomplete="off">

            <!-- Dummy hidden fields trick to prevent autocomplete -->
            <input type="text"     style="display:none" name="fake_user">
            <input type="password" style="display:none" name="fake_pass">

            <div class="form-group">
                <label class="form-label">Full Name <span class="required">*</span></label>
                <input type="text" name="name" class="form-control"
                       placeholder=" full name"
                       autocomplete="off"
                       required>
            </div>
            <div class="form-group">
                <label class="form-label">Email <span class="required">*</span></label>
                <!-- key trick: use a unique name so browser doesn't map to saved credentials -->
                <input type="email" name="email" id="ta_email_field"
                       class="form-control"
                       placeholder="enter email"
                       autocomplete="off"
                       required>
            </div>
            <div class="form-group">
                <label class="form-label">Password <span class="required">*</span></label>
                <input type="password" name="password" id="ta_pass_field"
                       class="form-control"
                       placeholder="Min 8 characters"
                       autocomplete="new-password"
                       required>
                <p class="form-text"></p>
            </div>

            <button type="submit" class="btn btn-primary btn-full">
                Create TA Account
            </button>
        </form>
    </div>
</div>
</div>

<script>
// Extra safety — clear these fields on load
window.addEventListener('load', function() {
    var e = document.getElementById('ta_email_field');
    var p = document.getElementById('ta_pass_field');
    if (e) e.value = '';
    if (p) p.value = '';
});
</script>

<?php require 'views/layout/footer.php'; ?>