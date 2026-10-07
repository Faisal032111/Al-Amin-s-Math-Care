<?php

/**
 * admin/faqs/delete.php — Soft-Delete FAQ
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . base_url('admin/faqs/'));
    exit;
}

verify_csrf_token();

$pdo = db();
$id  = (int)($_POST['id'] ?? 0);

if ($id > 0) {
    $pdo->prepare('UPDATE faqs SET is_deleted = 1 WHERE id = :id')->execute([':id' => $id]);
    set_flash('success', 'FAQ deleted successfully.');
} else {
    set_flash('error', 'Invalid FAQ ID.');
}

header('Location: ' . base_url('admin/faqs/'));
exit;
