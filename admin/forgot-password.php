<?php

/**
 * admin/forgot-password.php — Admin Password Recovery Request
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

$message = '';
$error = '';
$email = '';
$resetLinkDemo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $email = clean_input($_POST['email'] ?? '');

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        try {
            $pdo = db();
            $stmt = $pdo->prepare('SELECT id, name, email FROM users WHERE email = :email AND is_deleted = 0 AND status = "active" LIMIT 1');
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if ($user) {
                // Generate a 64-char crypto token
                $plainToken = bin2hex(random_bytes(32));
                $tokenHash = hash('sha256', $plainToken);
                $expiresAt = date('Y-m-d H:i:s', time() + 900); // 15 minutes expiry

                $updateStmt = $pdo->prepare('UPDATE users SET reset_token_hash = :hash, reset_token_expires_at = :exp WHERE id = :id');
                $updateStmt->execute([
                    ':hash' => $tokenHash,
                    ':exp' => $expiresAt,
                    ':id' => $user['id']
                ]);

                $resetUrl = base_url("admin/reset-password.php?token={$plainToken}&email=" . urlencode($email));
                $resetLinkDemo = $resetUrl;

                // Log audit
                $pdo->prepare('INSERT INTO activity_logs (user_id, action, module, ip_address) VALUES (:uid, "Password reset requested", "auth", :ip)')->execute([
                    ':uid' => $user['id'],
                    ':ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
                ]);
            }
        } catch (Throwable $e) {
            error_log('Forgot password error: ' . $e->getMessage());
        }

        // Always show generic message to prevent email enumeration
        $message = 'If the provided email is registered, password reset instructions have been generated. (Valid for 15 minutes).';
    } else {
        $error = 'Please enter a valid email address.';
    }
}

$pageTitle = 'Forgot Password — Al Amin\'s Math Care';
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
        .admin-recovery-card {
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

<div class="admin-recovery-card">
    <div class="text-center mb-4">
        <div class="brand-mark mx-auto mb-2 text-warning fs-3 fw-bold" style="width: 52px; height: 52px; background: #112239; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center;">∑</div>
        <h4 class="fw-bold text-dark mb-1">Reset Password</h4>
        <p class="text-muted small">Enter your email address to receive a secure password reset link.</p>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success py-2 small d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div><?= e($message) ?></div>
        </div>
        <?php if ($resetLinkDemo): ?>
            <div class="card bg-light p-3 border mb-3 small">
                <strong class="text-primary"><i class="bi bi-shield-lock me-1"></i> Security Reset Link (Local Demo):</strong>
                <a href="<?= e($resetLinkDemo) ?>" class="text-break mt-1 small font-monospace"><?= e($resetLinkDemo) ?></a>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2 small d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-circle-fill fs-5"></i>
            <div><?= e($error) ?></div>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <?= csrf_field() ?>
        
        <div class="mb-3">
            <label class="form-label small fw-bold text-secondary">Registered Email Address</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                <input type="email" name="email" class="form-control border-start-0" value="<?= e($email) ?>" required autofocus placeholder="admin@alaminmathcare.com">
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm mt-2">
            <i class="bi bi-send-fill me-1"></i> Send Recovery Link
        </button>
    </form>

    <div class="text-center mt-4 pt-3 border-top">
        <a href="<?= e(base_url('admin/login.php')) ?>" class="text-muted small text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i> Back to Login
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
