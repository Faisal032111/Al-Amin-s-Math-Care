<?php

/**
 * admin/logout.php — Secure Logout Processor
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/i18n.php';

// Unset all admin session keys
unset(
    $_SESSION['admin_logged_in'],
    $_SESSION['admin_user'],
    $_SESSION['admin_role'],
    $_SESSION['admin_id'],
    $_SESSION['admin_user_id'],
    $_SESSION['user']
);

// Clear any lockout keys in session
foreach ($_SESSION as $k => $v) {
    if (str_starts_with((string)$k, 'admin_lockout_') || str_starts_with((string)$k, 'admin_attempts_')) {
        unset($_SESSION[$k]);
    }
}

// Destroy session
if (session_id()) {
    session_destroy();
}

// Redirect to login with flash notice
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['flash_success'] = 'You have been successfully logged out.';
header('Location: ' . base_url('admin/login.php'));
exit;
