<?php

/**
 * admin/batches/delete.php — Soft Delete Mathematics Batch
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
        $stmt = $pdo->prepare('UPDATE batches SET is_deleted = 1, status = "completed", admission_status = "closed" WHERE id = :id');
        $stmt->execute([':id' => $id]);

        set_flash('success', 'Batch removed successfully.');
    } catch (Throwable $e) {
        set_flash('error', 'Failed to remove batch: ' . $e->getMessage());
    }
}

header('Location: ' . base_url('admin/batches/'));
exit;
