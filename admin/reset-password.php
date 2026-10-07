<?php

/**
 * admin/reset-password.php — Admin Password Reset Handler
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/i18n.php';
require_once __DIR__ . '/../includes/helpers.php';

$token = clean_input($_GET['token'] ?? ($_POST['token'] ?? ''));
$email = clean_input($_GET['email'] ?? ($_POST['email'] ?? ''));

$error = '';
$success = false;
$user = null;

if (empty($token) || empty($email)) {
    $error = 'Invalid password reset token or expired link.';
} else {
    $tokenHash = hash('sha256', $token);

    try {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT id, name, email, reset_token_expires_at FROM users WHERE email = :email AND reset_token_hash = :hash AND is_deleted = 0 AND status = "active" LIMIT 1');
        $stmt->execute([
            ':email' => $email,
            ':hash' => $tokenHash
        ]);
        $user = $stmt->fetch();

        if (!$user || empty($user['reset_token_expires_at']) || strtotime($user['reset_token_expires_at']) < time()) {
            $error = 'The reset token has expired or is invalid. Please request a new link.';
            $user = null;
        }
    } catch (Throwable $e) {
        $error = 'Database error verifying reset token.';
    }
}

if ($user && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $newPassword = (string)($_POST['password'] ?? '');
    $confirmPassword = (string)($_POST['confirm_password'] ?? '');

    if (strlen($newPassword) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        try {
            $pdo = db();
            $newHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);

            $update = $pdo->prepare('UPDATE users SET password = :p, reset_token_hash = NULL, reset_token_expires_at = NULL, failed_login_attempts = 0, lockout_until = NULL WHERE id = :id');
            $update->execute([
                ':p' => $newHash,
                ':id' => $user['id']
            ]);

            // Audit
            $pdo->prepare('INSERT INTO activity_logs (user_id, action, module, ip_address) VALUES (:uid, "Password reset completed", "auth", :ip)')->execute([
                ':uid' => $user['id'],
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
            ]);

            $success = true;
        } catch (Throwable $e) {
            $error = 'Failed to update password. Please try again.';
        }
    }
}

$pageTitle = 'Set New Password — Al Amin\'s Math Care';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= e(base_url('assets/css/main.css')) ?>" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0d1b2a 0%, #1b263b 50%, #112239 100%);
            padding: 1.5rem;
            font-family: 'Inter', sans-serif;
        }
        .admin-reset-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
            width: 100%;
            max-width: 440px;
            padding: 2.5rem 2rem;
        }
    </style>
</head>
<body>

<div class="admin-reset-card">
    <div class="text-center mb-4">
        <div class="brand-mark mx-auto mb-2 text-warning fs-3 fw-bold" style="width: 52px; height: 52px; background: #112239; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center;">∑</div>
        <h4 class="fw-bold text-dark mb-1">Set New Password</h4>
        <p class="text-muted small">Enter and confirm your new secure password.</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success py-3 small text-center">
            <i class="bi bi-check-circle-fill fs-3 text-success d-block mb-2"></i>
            <strong>Password successfully updated!</strong>
            <p class="mb-3 mt-1 text-muted">You can now log in with your new credentials.</p>
            <a href="<?= e(base_url('admin/login.php')) ?>" class="btn btn-primary btn-sm px-4 fw-bold">Proceed to Login</a>
        </div>
    <?php else: ?>
        <?php if ($error): ?>
            <div class="alert alert-danger py-2 small d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <div><?= e($error) ?></div>
            </div>
        <?php endif; ?>

        <?php if ($user): ?>
            <form method="POST" action="">
                <?= csrf_field() ?>
                <input type="hidden" name="token" value="<?= e($token) ?>">
                <input type="hidden" name="email" value="<?= e($email) ?>">
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">New Password (min. 8 characters)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                        <input type="password" name="password" class="form-control border-start-0" required minlength="8" placeholder="••••••••">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Confirm New Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-shield-check text-muted"></i></span>
                        <input type="password" name="confirm_password" class="form-control border-start-0" required minlength="8" placeholder="••••••••">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm mt-2">
                    <i class="bi bi-check-lg me-1"></i> Update Password
                </button>
            </form>
        <?php else: ?>
            <div class="text-center mt-3">
                <a href="<?= e(base_url('admin/forgot-password.php')) ?>" class="btn btn-outline-primary btn-sm">Request New Reset Link</a>
            </div>
        <?php endif; ?>

        <div class="text-center mt-4 pt-3 border-top">
            <a href="<?= e(base_url('admin/login.php')) ?>" class="text-muted small text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i> Back to Login
            </a>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
