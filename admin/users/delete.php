<?php

/**
 * admin/users/delete.php — Soft Delete User Account
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';
require_role_or_abort(['super_admin', 'admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . base_url('admin/users/'));
    exit;
}

verify_csrf_token();

$id = (int)($_POST['id'] ?? 0);
$currentUserId = (int)($_SESSION['admin_user_id'] ?? 0);

if ($id <= 0) {
    set_flash('error', 'Invalid user ID.');
    header('Location: ' . base_url('admin/users/'));
    exit;
}

// 1. Cannot delete primary super admin account (ID 1)
if ($id === 1) {
    set_flash('error', 'The Primary Super Administrator account cannot be deleted.');
    header('Location: ' . base_url('admin/users/'));
    exit;
}

// 2. Cannot delete your own currently logged-in account
if ($id === $currentUserId) {
    set_flash('error', 'You cannot delete your own active account.');
    header('Location: ' . base_url('admin/users/'));
    exit;
}

$pdo = db();

// Check if user exists
$stmt = $pdo->prepare('SELECT id, name FROM users WHERE id = :id AND is_deleted = 0 LIMIT 1');
$stmt->execute([':id' => $id]);
$user = $stmt->fetch();

if (!$user) {
    set_flash('error', 'User account not found or already deleted.');
    header('Location: ' . base_url('admin/users/'));
    exit;
}

// Soft delete
$stmtDelete = $pdo->prepare('
    UPDATE users
    SET is_deleted = 1, status = "inactive", updated_at = NOW()
    WHERE id = :id
');
$stmtDelete->execute([':id' => $id]);

set_flash('success', 'User account "' . $user['name'] . '" has been deleted.');
header('Location: ' . base_url('admin/users/'));
exit;
