<?php

/**
 * admin/students/delete.php — Soft Delete Student Record
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
        $pdo->beginTransaction();

        // 1. Find active enrollment and free up the seat in batch
        $enrStmt = $pdo->prepare('SELECT batch_id FROM enrollments WHERE student_id = :sid AND status = "active"');
        $enrStmt->execute([':sid' => $id]);
        $batchId = (int)$enrStmt->fetchColumn();

        if ($batchId > 0) {
            $pdo->prepare('UPDATE batches SET available_seats = available_seats + 1, admission_status = "open" WHERE id = :bid')->execute([':bid' => $batchId]);
        }

        // 2. Soft delete student and update status
        $pdo->prepare('UPDATE students SET is_deleted = 1, status = "dropped" WHERE id = :id')->execute([':id' => $id]);
        $pdo->prepare('UPDATE enrollments SET status = "transferred" WHERE student_id = :sid')->execute([':sid' => $id]);

        $pdo->commit();
        set_flash('success', 'Student record removed successfully and batch seat freed up.');
    } catch (Throwable $e) {
        $pdo->rollBack();
        set_flash('error', 'Failed to remove student: ' . $e->getMessage());
    }
}

header('Location: ' . base_url('admin/students/'));
exit;
