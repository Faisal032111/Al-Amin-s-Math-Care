<?php

/**
 * admin/leads/delete.php — Soft Delete Lead Inquiry
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
        $stmt = $pdo->prepare('UPDATE leads SET is_deleted = 1 WHERE id = :id');
        $stmt->execute([':id' => $id]);

        set_flash('success', 'Lead inquiry removed successfully.');
    } catch (Throwable $e) {
        set_flash('error', 'Failed to remove lead: ' . $e->getMessage());
    }
}

header('Location: ' . base_url('admin/leads/'));
exit;
