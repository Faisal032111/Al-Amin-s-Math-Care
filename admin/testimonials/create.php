<?php

/**
 * admin/testimonials/create.php — Add New Testimonial
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

$adminPageTitle    = 'Add Testimonial — Admin Panel';
$adminPageHeading  = 'Add Student Testimonial';
$adminPageSubheading = 'Record a success story and star rating to display on the homepage';
$activeModule      = 'testimonials';

$pdo    = db();
$errors = [];
$data   = [
    'student_name' => '',
    'student_role' => '',
    'quote_en'     => '',
    'quote_bn'     => '',
    'rating'       => 5,
    'is_featured'  => 1,
];

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

    $photoPath = null;
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
                $photoPath = 'assets/uploads/testimonials/' . $fileName;
            } else {
                $errors[] = 'Failed to upload photo.';
            }
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO testimonials (student_name, student_role, quote_en, quote_bn, rating, photo, is_featured)
                 VALUES (:sn, :sr, :qen, :qbn, :rat, :ph, :feat)'
            );
            $stmt->execute([
                ':sn'   => $data['student_name'],
                ':sr'   => $data['student_role'],
                ':qen'  => $data['quote_en'],
                ':qbn'  => $data['quote_bn'],
                ':rat'  => $data['rating'],
                ':ph'   => $photoPath,
                ':feat' => $data['is_featured'],
            ]);
            set_flash('success', 'Testimonial added successfully.');
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
                        <input type="text" name="student_name" class="form-control" value="<?= e($data['student_name']) ?>" required placeholder="e.g. Md. Rakibul Islam">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Student Role / Batch</label>
                        <input type="text" name="student_role" class="form-control" value="<?= e($data['student_role']) ?>" placeholder="e.g. HSC 2025 Batch — GPA 5.00">
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold text-secondary">Quote / Review (English) <span class="text-danger">*</span></label>
                        <textarea name="quote_en" rows="3" class="form-control" required
                                  placeholder="Write the student's review in English..."><?= e($data['quote_en']) ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold text-secondary">Quote / Review (Bangla / বাংলা)</label>
                        <textarea name="quote_bn" rows="3" class="form-control"
                                  placeholder="বাংলায় রিভিউ লিখুন..."><?= e($data['quote_bn']) ?></textarea>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary">Rating (1–5 Stars)</label>
                        <select name="rating" class="form-select">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <option value="<?= $i ?>" <?= $data['rating'] == $i ? 'selected' : '' ?>><?= $i ?> Star<?= $i > 1 ? 's' : '' ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary">Student Photo (Optional)</label>
                        <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp">
                        <div class="form-text">JPG/PNG/WebP, max 2MB.</div>
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
                            <i class="bi bi-check-circle-fill me-1"></i> Save Testimonial
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
