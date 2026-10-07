<?php

/**
 * admin/teachers/delete.php — Soft Delete Teacher Profile
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
        // Prevent deleting Head Teacher
        $stmt = $pdo->prepare('SELECT is_head_teacher, photo FROM teachers WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $teacher = $stmt->fetch();

        if ($teacher && $teacher['is_head_teacher']) {
            set_flash('error', 'The Head Teacher & Founder profile cannot be deleted.');
        } else {
            $pdo->prepare('UPDATE teachers SET is_deleted = 1, is_active = 0 WHERE id = :id')->execute([':id' => $id]);
            set_flash('success', 'Teacher profile removed successfully.');
        }
    } catch (Throwable $e) {
        set_flash('error', 'Failed to remove teacher: ' . $e->getMessage());
    }
}

header('Location: ' . base_url('admin/teachers/'));
exit;
