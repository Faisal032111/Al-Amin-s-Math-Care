<?php

/**
 * admin/notices/edit.php — Edit Published Notice
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

$adminPageTitle = 'Edit Notice — Admin Panel';
$adminPageHeading = 'Edit Official Announcement';
$adminPageSubheading = 'Modify announcement text, replace attachments & adjust expiry dates';
$activeModule = 'notices';

$pdo = db();
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM notices WHERE id = :id AND is_deleted = 0 LIMIT 1');
$stmt->execute([':id' => $id]);
$notice = $stmt->fetch();

if (!$notice) {
    set_flash('error', 'Notice not found.');
    header('Location: ' . base_url('admin/notices/'));
    exit;
}

$errors = [];
$titleEn = $notice['title_en'];
$titleBn = $notice['title_bn'] ?? '';
$category = $notice['category'];
$descEn = $notice['description_en'] ?? '';
$descBn = $notice['description_bn'] ?? '';
$isPinned = (int)$notice['is_pinned'];
$isPublished = (int)$notice['is_published'];
$expiryDate = $notice['expiry_date'];
$attachmentPath = $notice['attachment_file'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $titleEn = clean_input($_POST['title_en'] ?? '');
    $titleBn = clean_input($_POST['title_bn'] ?? '');
    $category = clean_input($_POST['category'] ?? 'general');
    $descEn = clean_input($_POST['description_en'] ?? '');
    $descBn = clean_input($_POST['description_bn'] ?? '');
    $isPinned = isset($_POST['is_pinned']) ? 1 : 0;
    $isPublished = isset($_POST['is_published']) ? 1 : 0;
    $expiryDate = !empty($_POST['expiry_date']) ? clean_input($_POST['expiry_date']) : null;

    if (empty($titleEn)) {
        $errors[] = 'Notice Title (English) is required.';
    }

    if (!empty($_FILES['attachment']['name'])) {
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];
        $fileType = mime_content_type($_FILES['attachment']['tmp_name']);
        if (!in_array($fileType, $allowedTypes, true)) {
            $errors[] = 'Only PDF documents or image files (JPG, PNG, WebP) are allowed.';
        } elseif ($_FILES['attachment']['size'] > 5 * 1024 * 1024) {
            $errors[] = 'Attachment size must not exceed 5MB.';
        } else {
            $uploadDir = __DIR__ . '/../../assets/uploads/notices';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $ext = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
            $fileName = 'notice_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadDir . '/' . $fileName)) {
                if ($attachmentPath && file_exists(__DIR__ . '/../../' . $attachmentPath)) {
                    @unlink(__DIR__ . '/../../' . $attachmentPath);
                }
                $attachmentPath = 'assets/uploads/notices/' . $fileName;
            }
        }
    }

    if (empty($errors)) {
        try {
            $update = $pdo->prepare('UPDATE notices SET title_en = :ten, title_bn = :tbn, category = :cat, description_en = :den, description_bn = :dbn, attachment_file = :att, is_pinned = :pin, is_published = :pub, expiry_date = :exp WHERE id = :id');
            $update->execute([
                ':ten' => $titleEn,
                ':tbn' => $titleBn,
                ':cat' => $category,
                ':den' => $descEn,
                ':dbn' => $descBn,
                ':att' => $attachmentPath,
                ':pin' => $isPinned,
                ':pub' => $isPublished,
                ':exp' => $expiryDate,
                ':id' => $id,
            ]);

            set_flash('success', "Notice '{$titleEn}' updated successfully.");
            header('Location: ' . base_url('admin/notices/'));
            exit;
        } catch (Throwable $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="<?= e(base_url('admin/notices/')) ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Notices
            </a>
            <span class="badge bg-light text-secondary border">Notice ID #<?= $id ?></span>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger py-2 small">
                <ul class="mb-0">
                    <?php foreach ($errors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card card-custom p-4">
            <form method="POST" action="" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Notice Title (English) <span class="text-danger">*</span></label>
                        <input type="text" name="title_en" class="form-control" value="<?= e($titleEn) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Notice Title (Bangla / বাংলা)</label>
                        <input type="text" name="title_bn" class="form-control" value="<?= e($titleBn) ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Notice Category <span class="text-danger">*</span></label>
                        <select name="category" class="form-select" required>
                            <option value="general" <?= $category === 'general' ? 'selected' : '' ?>>General Announcement</option>
                            <option value="admission" <?= $category === 'admission' ? 'selected' : '' ?>>Admission Notice</option>
                            <option value="exam" <?= $category === 'exam' ? 'selected' : '' ?>>Exam Schedule / Routine</option>
                            <option value="holiday" <?= $category === 'holiday' ? 'selected' : '' ?>>Holiday Notice</option>
                            <option value="urgent" <?= $category === 'urgent' ? 'selected' : '' ?>>Urgent Notice</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">PDF Attachment / Circular Image</label>
                        <input type="file" name="attachment" class="form-control" accept=".pdf,image/*">
                        <?php if ($attachmentPath): ?>
                            <small class="text-success d-block mt-1">Current file: <?= e($attachmentPath) ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold text-secondary">Notice Details (English)</label>
                        <textarea name="description_en" rows="4" class="form-control"><?= e($descEn) ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold text-secondary">Notice Details (Bangla / বাংলা)</label>
                        <textarea name="description_bn" rows="4" class="form-control"><?= e($descBn) ?></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Expiry Date (Optional)</label>
                        <input type="date" name="expiry_date" class="form-control" value="<?= e($expiryDate ?? '') ?>">
                    </div>

                    <div class="col-md-6 d-flex align-items-center gap-4 pt-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_pinned" value="1" id="isPinned" <?= $isPinned ? 'checked' : '' ?>>
                            <label class="form-check-label small fw-semibold" for="isPinned">Pin to top of homepage</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_published" value="1" id="isPublished" <?= $isPublished ? 'checked' : '' ?>>
                            <label class="form-check-label small fw-semibold" for="isPublished">Publish immediately</label>
                        </div>
                    </div>

                    <div class="col-12 mt-4 text-end">
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-save me-1"></i> Update Notice
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
