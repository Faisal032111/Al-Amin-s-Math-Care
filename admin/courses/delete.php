<?php

/**
 * admin/courses/delete.php — Soft Delete Mathematics Course
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

verify_csrf_token();

$id = (int)($_POST['id'] ?? 0);

if ($id > 0) {
    try {
        $pdo = db();
        $stmt = $pdo->prepare('UPDATE courses SET is_deleted = 1, is_active = 0 WHERE id = :id');
        $stmt->execute([':id' => $id]);

        set_flash('success', 'Mathematics Course removed successfully.');
    } catch (Throwable $e) {
        set_flash('error', 'Failed to remove course: ' . $e->getMessage());
    }
}

header('Location: ' . base_url('admin/courses/'));
exit;
