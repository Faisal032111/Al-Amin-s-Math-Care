<?php

/**
 * admin/testimonials/delete.php — Soft-Delete Testimonial
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . base_url('admin/testimonials/'));
    exit;
}

verify_csrf_token();

$pdo = db();
$id  = (int)($_POST['id'] ?? 0);

if ($id > 0) {
    $pdo->prepare('UPDATE testimonials SET is_deleted = 1 WHERE id = :id')->execute([':id' => $id]);
    set_flash('success', 'Testimonial deleted successfully.');
} else {
    set_flash('error', 'Invalid testimonial ID.');
}

header('Location: ' . base_url('admin/testimonials/'));
exit;
