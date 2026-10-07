<?php

/**
 * admin/gallery/upload.php — Batch Photo Upload
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

$adminPageTitle    = 'Upload Photos — Admin Panel';
$adminPageHeading  = 'Upload Gallery Photos';
$adminPageSubheading = 'Upload up to 10 images at once (JPG, PNG, WebP — max 3MB each)';
$activeModule      = 'gallery';

$pdo    = db();
$errors = [];
$successCount = 0;
$category  = 'classroom';
$captionEn = '';
$captionBn = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $category  = clean_input($_POST['category']   ?? 'classroom');
    $captionEn = clean_input($_POST['caption_en'] ?? '');
    $captionBn = clean_input($_POST['caption_bn'] ?? '');

    $allowedCats = ['classroom', 'events', 'achievements'];
    if (!in_array($category, $allowedCats, true)) {
        $category = 'classroom';
    }

    $uploadDir = __DIR__ . '/../../assets/uploads/gallery';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];

    // Support multiple file upload: name="photos[]"
    $files = $_FILES['photos'] ?? [];
    if (!empty($files['name'][0])) {
        $count = count($files['name']);
        for ($i = 0; $i < $count; $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;

            $mimeType = mime_content_type($files['tmp_name'][$i]);
            if (!in_array($mimeType, $allowedMime, true)) {
                $errors[] = "File #{$i}: Invalid image type — only JPG, PNG, WebP allowed.";
                continue;
            }
            if ($files['size'][$i] > 3 * 1024 * 1024) {
                $errors[] = "File #{$i}: Exceeds 3MB limit.";
                continue;
            }

            $ext      = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
            $fileName = 'gallery_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            if (move_uploaded_file($files['tmp_name'][$i], $uploadDir . '/' . $fileName)) {
                try {
                    $stmt = $pdo->prepare(
                        'INSERT INTO gallery (image_path, caption_en, caption_bn, category)
                         VALUES (:img, :cen, :cbn, :cat)'
                    );
                    $stmt->execute([
                        ':img' => 'assets/uploads/gallery/' . $fileName,
                        ':cen' => $captionEn ?: null,
                        ':cbn' => $captionBn ?: null,
                        ':cat' => $category,
                    ]);
                    $successCount++;
                } catch (Throwable $ex) {
                    $errors[] = 'DB error for file ' . e($files['name'][$i]) . ': ' . $ex->getMessage();
                }
            } else {
                $errors[] = 'Failed to move uploaded file: ' . e($files['name'][$i]);
            }
        }
    } else {
        $errors[] = 'No files selected for upload.';
    }

    if ($successCount > 0 && empty($errors)) {
        set_flash('success', "{$successCount} photo(s) uploaded successfully.");
        header('Location: ' . base_url('admin/gallery/'));
        exit;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="mb-3">
            <a href="<?= e(base_url('admin/gallery/')) ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Gallery
            </a>
        </div>

        <?php if ($successCount > 0): ?>
            <div class="alert alert-success py-2 small"><?= $successCount ?> photo(s) uploaded. Some may have had errors (see below).</div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger py-2 small">
                <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <div class="card card-custom p-4">
            <form method="POST" action="" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Category <span class="text-danger">*</span></label>
                        <select name="category" class="form-select">
                            <option value="classroom"    <?= $category === 'classroom'    ? 'selected' : '' ?>>Classroom Photos</option>
                            <option value="events"       <?= $category === 'events'       ? 'selected' : '' ?>>Events &amp; Programs</option>
                            <option value="achievements" <?= $category === 'achievements' ? 'selected' : '' ?>>Student Achievements</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Caption (English) — Applied to all uploads</label>
                        <input type="text" name="caption_en" class="form-control" value="<?= e($captionEn) ?>"
                               placeholder="e.g. Annual Prize Distribution 2025">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Caption (Bangla / বাংলা)</label>
                        <input type="text" name="caption_bn" class="form-control" value="<?= e($captionBn) ?>"
                               placeholder="যেমন: বার্ষিক পুরস্কার বিতরণী ২০২৫">
                    </div>
                    <div class="col-md-6"></div>

                    <div class="col-12">
                        <label class="form-label small fw-bold text-secondary">Select Photos <span class="text-danger">*</span></label>
                        <input type="file" name="photos[]" class="form-control" accept="image/jpeg,image/png,image/webp" multiple required>
                        <div class="form-text">Select up to 10 files. JPG, PNG, WebP only. Max 3MB per file.</div>
                    </div>

                    <div class="col-12 mt-3 text-end">
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-cloud-upload-fill me-1"></i> Upload Photos
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
