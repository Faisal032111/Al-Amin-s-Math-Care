<?php

/**
 * admin/testimonials/edit.php — Edit Testimonial
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

$adminPageTitle    = 'Edit Testimonial — Admin Panel';
$adminPageHeading  = 'Edit Testimonial';
$adminPageSubheading = 'Update student review details';
$activeModule      = 'testimonials';

$pdo = db();
$id  = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM testimonials WHERE id = :id AND is_deleted = 0');
$stmt->execute([':id' => $id]);
$t = $stmt->fetch();

if (!$t) {
    set_flash('error', 'Testimonial not found.');
    header('Location: ' . base_url('admin/testimonials/'));
    exit;
}

$errors = [];
$data   = $t;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $data['student_name'] = clean_input($_POST['student_name'] ?? '');
    $data['student_role'] = clean_input($_POST['student_role'] ?? '');
    $data['quote_en']     = clean_input($_POST['quote_en'] ?? '');
    $data['quote_bn']     = clean_input($_POST['quote_bn'] ?? '');
    $data['rating']       = max(1, min(5, (int)($_POST['rating'] ?? 5)));
    $data['is_featured']  = isset($_POST['is_featured']) ? 1 : 0;

    if (empty($data['student_name'])) $errors[] = 'Student name is required.';
    if (empty($data['quote_en']))     $errors[] = 'English quote is required.';

    $newPhotoPath = $data['photo'] ?? null;
    if (!empty($_FILES['photo']['name'])) {
        $allowed  = ['image/jpeg', 'image/png', 'image/webp'];
        $mimeType = mime_content_type($_FILES['photo']['tmp_name']);
        if (!in_array($mimeType, $allowed, true)) {
            $errors[] = 'Photo must be JPG, PNG, or WebP.';
        } elseif ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Photo must not exceed 2MB.';
        } else {
            $uploadDir = __DIR__ . '/../../assets/uploads/testimonials';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $ext      = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $fileName = 'testimonial_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . '/' . $fileName)) {
                // Unlink old photo
                if (!empty($t['photo']) && file_exists(__DIR__ . '/../../' . $t['photo'])) {
                    @unlink(__DIR__ . '/../../' . $t['photo']);
                }
                $newPhotoPath = 'assets/uploads/testimonials/' . $fileName;
            } else {
                $errors[] = 'Failed to upload new photo.';
            }
        }
    }

    if (empty($errors)) {
        try {
            $upd = $pdo->prepare(
                'UPDATE testimonials SET student_name=:sn, student_role=:sr, quote_en=:qen, quote_bn=:qbn,
                 rating=:rat, photo=:ph, is_featured=:feat WHERE id=:id'
            );
            $upd->execute([
                ':sn'   => $data['student_name'],
                ':sr'   => $data['student_role'],
                ':qen'  => $data['quote_en'],
                ':qbn'  => $data['quote_bn'],
                ':rat'  => $data['rating'],
                ':ph'   => $newPhotoPath,
                ':feat' => $data['is_featured'],
                ':id'   => $id,
            ]);
            set_flash('success', 'Testimonial updated successfully.');
            header('Location: ' . base_url('admin/testimonials/'));
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
        <div class="mb-3">
            <a href="<?= e(base_url('admin/testimonials/')) ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Testimonials
            </a>
        </div>

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
                        <label class="form-label small fw-bold text-secondary">Student Name <span class="text-danger">*</span></label>
                        <input type="text" name="student_name" class="form-control" value="<?= e($data['student_name']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Student Role / Batch</label>
                        <input type="text" name="student_role" class="form-control" value="<?= e($data['student_role'] ?? '') ?>">
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold text-secondary">Quote / Review (English) <span class="text-danger">*</span></label>
                        <textarea name="quote_en" rows="3" class="form-control" required><?= e($data['quote_en']) ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold text-secondary">Quote / Review (Bangla)</label>
                        <textarea name="quote_bn" rows="3" class="form-control"><?= e($data['quote_bn'] ?? '') ?></textarea>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary">Rating</label>
                        <select name="rating" class="form-select">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <option value="<?= $i ?>" <?= $data['rating'] == $i ? 'selected' : '' ?>><?= $i ?> Star<?= $i > 1 ? 's' : '' ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary">New Photo (Optional)</label>
                        <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp">
                        <?php if (!empty($data['photo'])): ?>
                            <div class="mt-2">
                                <img src="<?= e(base_url($data['photo'])) ?>" alt="Current photo"
                                     width="48" height="48" class="rounded-circle object-fit-cover border">
                                <small class="text-muted ms-2">Current photo</small>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4 d-flex align-items-center pt-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="isFeatured"
                                   <?= $data['is_featured'] ? 'checked' : '' ?>>
                            <label class="form-check-label small fw-semibold" for="isFeatured">Show on homepage</label>
                        </div>
                    </div>

                    <div class="col-12 mt-3 text-end">
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-check-circle-fill me-1"></i> Update Testimonial
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
