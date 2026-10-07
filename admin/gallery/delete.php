<?php

/**
 * admin/gallery/delete.php — Delete Gallery Photo (soft + file unlink)
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . base_url('admin/gallery/'));
    exit;
}

verify_csrf_token();

$pdo = db();
$id  = (int)($_POST['id'] ?? 0);

if ($id > 0) {
    // Fetch path before deleting
    $row = $pdo->prepare('SELECT image_path FROM gallery WHERE id = :id AND is_deleted = 0');
    $row->execute([':id' => $id]);
    $photo = $row->fetch();

    $pdo->prepare('UPDATE gallery SET is_deleted = 1 WHERE id = :id')->execute([':id' => $id]);

    // Unlink physical file
    if ($photo && !empty($photo['image_path'])) {
        $filePath = __DIR__ . '/../../' . $photo['image_path'];
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
    }

    set_flash('success', 'Photo deleted successfully.');
} else {
    set_flash('error', 'Invalid photo ID.');
}

header('Location: ' . base_url('admin/gallery/'));
exit;
