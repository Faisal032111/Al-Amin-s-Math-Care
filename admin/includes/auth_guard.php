<?php

/**
 * admin/includes/auth_guard.php — Authentication Guard & RBAC for Admin Panel
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/i18n.php';
require_once __DIR__ . '/../../includes/helpers.php';

// 1. Verify Admin Login
if (empty($_SESSION['admin_logged_in'])) {
    $_SESSION['flash_error'] = 'Please log in to access the admin panel.';
    header('Location: ' . base_url('admin/login.php'));
    exit;
}

// 2. Role Verification Function
function check_admin_role(array $allowedRoles = ['super_admin', 'admin']): bool
{
    $currentRole = $_SESSION['admin_role'] ?? 'receptionist';
    return in_array($currentRole, $allowedRoles, true);
}

function require_role_or_abort(array $allowedRoles = ['super_admin', 'admin']): void
{
    if (!check_admin_role($allowedRoles)) {
        http_response_code(403);
        echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Access Denied</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="bg-light d-flex align-items-center justify-content-center" style="min-height: 100vh;"><div class="card p-4 shadow-sm text-center" style="max-width: 480px;"><h3 class="text-danger fw-bold">403 — Access Denied</h3><p class="text-muted">You do not have permission to access this module.</p><a href="' . e(base_url('admin/index.php')) . '" class="btn btn-primary mt-2">Back to Dashboard</a></div></body></html>';
        exit;
    }
}

// Flash message helpers
function set_flash(string $type, string $message): void
{
    $_SESSION['flash_' . $type] = $message;
}

function get_flash(string $type): ?string
{
    $key = 'flash_' . $type;
    if (isset($_SESSION[$key])) {
        $msg = (string)$_SESSION[$key];
        unset($_SESSION[$key]);
        return $msg;
    }
    return null;
}
