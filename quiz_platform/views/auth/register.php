<?php // views/auth/register.php ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Register — QuizPro</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="auth-page">
<div class="auth-card" style="max-width:500px">
    <div class="auth-logo"><h1>🎓 QuizPro</h1><p>Student Registration</p></div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">⚠️ <?= $error ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success">✓ <?= $success ?></div>
    <?php endif; ?>

    <h2 class="auth-title">Create your account</h2>
    <p class="auth-subtitle">
        Your Student ID will be auto-generated after registration.
    </p>

    <form method="POST" action="index.php?page=auth&action=register"
          autocomplete="off">
        <div class="form-group">
            <label class="form-label">Full Name <span class="required">*</span></label>
            <input type="text" name="name" class="form-control"
                   placeholder="John Doe"
                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                   required>
        </div>
        <div class="form-group">
            <label class="form-label">Email <span class="required">*</span></label>
            <input type="email" name="email" class="form-control"
                   placeholder="you@example.com"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                   required>
        </div>
        <div class="form-group">
            <label class="form-label">Phone</label>
            <input type="tel" name="phone" class="form-control"
                   placeholder="+880 1700 000000"
                   value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Password <span class="required">*</span></label>
                <input type="password" name="password" class="form-control"
                       placeholder="Min 8 chars, 1 capital"
                       autocomplete="new-password"
                       required>
            </div>
            <div class="form-group">
                <label class="form-label">Confirm Password <span class="required">*</span></label>
                <input type="password" name="confirm_password" class="form-control"
                       placeholder="Repeat password"
                       autocomplete="new-password"
                       required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-full btn-lg">
            Create Account →
        </button>
    </form>

    <div class="text-center mt-4 text-sm text-gray">
        Already registered?
        <a href="index.php?page=auth&action=login" class="fw-600">Sign in</a>
    </div>
</div>
</div>
</body></html>