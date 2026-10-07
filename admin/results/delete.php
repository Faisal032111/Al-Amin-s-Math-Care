<?php

/**
 * admin/results/delete.php — Soft Delete Result & File Unlink
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
        $stmt = $pdo->prepare('SELECT file_path FROM results WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $file = $stmt->fetchColumn();

        if ($file && file_exists(__DIR__ . '/../../' . $file)) {
            @unlink(__DIR__ . '/../../' . $file);
        }

        $pdo->prepare('UPDATE results SET is_deleted = 1 WHERE id = :id')->execute([':id' => $id]);
        set_flash('success', 'Exam result sheet removed successfully.');
    } catch (Throwable $e) {
        set_flash('error', 'Failed to remove result: ' . $e->getMessage());
    }
}

header('Location: ' . base_url('admin/results/'));
exit;
