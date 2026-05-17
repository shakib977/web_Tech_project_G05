<?php // views/auth/login.php ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login — QuizPro</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="auth-page">
<div class="auth-card">
    <div class="auth-logo"><h1>🎓 QuizPro</h1><p>Online Learning & Assessment Platform</p></div>

    <?php if (!empty($error) && $error === 'DEACTIVATED'): ?>

<div style="background:#FEE2E2;border:2px solid #DC2626;
            border-radius:var(--radius);
            padding:20px;text-align:center;margin-bottom:20px">

    <div style="font-size:32px;margin-bottom:8px">🚫</div>

    <div style="font-size:16px;font-weight:800;
                color:#991B1B;margin-bottom:6px">
        Account Deactivated
    </div>

    <div style="font-size:14px;color:#B91C1C">
        Your account has been deactivated by an administrator.<br>
        Please contact the admin for assistance.
    </div>

</div>

<?php elseif (!empty($error)): ?>

<div class="alert alert-danger">
    ⚠️ <?= htmlspecialchars($error) ?>
</div>

<?php endif; ?>
    <?php if (!empty($success)): ?><div class="alert alert-success">✓ <?= $success ?></div><?php endif; ?>

    <h2 class="auth-title">Welcome back</h2>
    <p class="auth-subtitle">Sign in to your account</p>

    <!-- autocomplete="off" prevents browser from filling saved passwords -->
    <form method="POST" action="index.php?page=auth&action=login"
          autocomplete="off">
        <div class="form-group">
            <label class="form-label">Email <span class="required">*</span></label>
            <!-- value is intentionally NOT pre-filled -->
            <input type="email" name="email" id="login_email"
                   class="form-control"
                   placeholder="your@email.com"
                   autocomplete="new-password"
                   required>
        </div>
        <div class="form-group">
            <label class="form-label">Password <span class="required">*</span></label>
            <input type="password" name="password" id="login_pass"
                   class="form-control"
                   placeholder="Your password"
                   autocomplete="new-password"
                   required>
        </div>
        <button type="submit" class="btn btn-primary btn-full btn-lg"
                style="margin-top:8px">
            Sign In →
        </button>
    </form>

    <div class="text-center mt-4 text-sm text-gray">
        New student?
        <a href="index.php?page=auth&action=register" class="fw-600">
            Register here
        </a>
    </div>
    <hr class="divider">
    <p class="text-xs text-muted text-center">
        Demo: <strong>admin@quiz.com</strong> / <strong>password</strong>
    </p>
</div>
</div>
<script>
// Clear any browser-filled values on load
window.addEventListener('load', function() {
    setTimeout(function() {
        document.getElementById('login_email').value = '';
        document.getElementById('login_pass').value  = '';
    }, 100);
});
</script>
</body></html>