<?php

/**
 * admin/teachers/create.php — Add New Mathematics Teacher
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

$adminPageTitle = 'Add Teacher — Admin Panel';
$adminPageHeading = 'Add Mathematics Teacher';
$adminPageSubheading = 'Add faculty instructor profile, educational degrees & photo portrait';
$activeModule = 'teachers';

$pdo = db();
$errors = [];
$nameEn = '';
$nameBn = '';
$slug = '';
$designationEn = 'Mathematics Mentor';
$designationBn = 'গণিত শিক্ষক';
$qualification = 'B.Sc / M.Sc in Mathematics';
$experienceYears = 5;
$bioEn = '';
$bioBn = '';
$isHeadTeacher = 0;
$sortOrder = 1;
$isActive = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $nameEn = clean_input($_POST['name_en'] ?? '');
    $nameBn = clean_input($_POST['name_bn'] ?? '');
    $slug = slugify($_POST['slug'] ?? $nameEn);
    $designationEn = clean_input($_POST['designation_en'] ?? '');
    $designationBn = clean_input($_POST['designation_bn'] ?? '');
    $qualification = clean_input($_POST['qualification'] ?? '');
    $experienceYears = (int)($_POST['experience_years'] ?? 0);
    $bioEn = clean_input($_POST['bio_en'] ?? '');
    $bioBn = clean_input($_POST['bio_bn'] ?? '');
    $isHeadTeacher = isset($_POST['is_head_teacher']) ? 1 : 0;
    $sortOrder = (int)($_POST['sort_order'] ?? 1);
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    $photoPath = null;

    if (empty($nameEn)) {
        $errors[] = 'Teacher Name (English) is required.';
    }

    // Photo Upload Handling
    if (!empty($_FILES['photo']['name'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $fileType = mime_content_type($_FILES['photo']['tmp_name']);
        $fileSize = $_FILES['photo']['size'];

        if (!in_array($fileType, $allowedTypes, true)) {
            $errors[] = 'Invalid photo format. Only JPG, PNG, and WebP are allowed.';
        } elseif ($fileSize > 2 * 1024 * 1024) {
            $errors[] = 'Photo size must not exceed 2MB.';
        } else {
            $uploadDir = __DIR__ . '/../../assets/uploads/teachers';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $fileName = 'teacher_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
            $targetPath = $uploadDir . '/' . $fileName;

            if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
                $photoPath = 'assets/uploads/teachers/' . $fileName;
            } else {
                $errors[] = 'Failed to upload photo.';
            }
        }
    }

    if (empty($errors)) {
        try {
            $check = $pdo->prepare('SELECT COUNT(*) FROM teachers WHERE slug = :s');
            $check->execute([':s' => $slug]);
            if ((int)$check->fetchColumn() > 0) {
                $slug .= '-' . rand(100, 999);
            }

            $stmt = $pdo->prepare('INSERT INTO teachers (slug, name_en, name_bn, designation_en, designation_bn, qualification, experience_years, bio_en, bio_bn, photo, is_head_teacher, sort_order, is_active) VALUES (:slug, :nen, :nbn, :den, :dbn, :qual, :exp, :ben, :bbn, :photo, :head, :sort, :act)');
            $stmt->execute([
                ':slug' => $slug,
                ':nen' => $nameEn,
                ':nbn' => $nameBn,
                ':den' => $designationEn,
                ':dbn' => $designationBn,
                ':qual' => $qualification,
                ':exp' => $experienceYears,
                ':ben' => $bioEn,
                ':bbn' => $bioBn,
                ':photo' => $photoPath,
                ':head' => $isHeadTeacher,
                ':sort' => $sortOrder,
                ':act' => $isActive,
            ]);

            set_flash('success', "Teacher profile '{$nameEn}' added successfully.");
            header('Location: ' . base_url('admin/teachers/'));
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
            <a href="<?= e(base_url('admin/teachers/')) ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Faculty
            </a>
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
                        <label class="form-label small fw-bold text-secondary">Teacher Name (English) <span class="text-danger">*</span></label>
                        <input type="text" name="name_en" class="form-control" value="<?= e($nameEn) ?>" required placeholder="e.g. Al Amin Sir">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Teacher Name (Bangla / বাংলা)</label>
                        <input type="text" name="name_bn" class="form-control" value="<?= e($nameBn) ?>" placeholder="যেমন: আল আমিন স্যার">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Profile URL Slug</label>
                        <input type="text" name="slug" class="form-control" value="<?= e($slug) ?>" placeholder="al-amin-sir">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Portrait Photo (JPG, PNG, WebP — max 2MB)</label>
                        <input type="file" name="photo" class="form-control" accept="image/*">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Designation (English)</label>
                        <input type="text" name="designation_en" class="form-control" value="<?= e($designationEn) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Designation (Bangla / বাংলা)</label>
                        <input type="text" name="designation_bn" class="form-control" value="<?= e($designationBn) ?>">
                    </div>

                    <div class="col-md-8">
                        <label class="form-label small fw-bold text-secondary">Educational Qualification</label>
                        <input type="text" name="qualification" class="form-control" value="<?= e($qualification) ?>" placeholder="e.g. B.Sc (Hons), M.Sc in Mathematics">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary">Experience (Years)</label>
                        <input type="number" name="experience_years" class="form-control" value="<?= e((string)$experienceYears) ?>" min="0">
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold text-secondary">Teacher Biography & Teaching Style (English)</label>
                        <textarea name="bio_en" rows="3" class="form-control" placeholder="Short description of teaching experience and methodologies..."><?= e($bioEn) ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold text-secondary">Teacher Biography (Bangla / বাংলা)</label>
                        <textarea name="bio_bn" rows="3" class="form-control" placeholder="শিক্ষকতা ও পাঠদান পদ্ধতির সংক্ষিপ্ত বিবরণ..."><?= e($bioBn) ?></textarea>
                    </div>

                    <div class="col-12">
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_head_teacher" value="1" id="headTeacher" <?= $isHeadTeacher ? 'checked' : '' ?>>
                                <label class="form-check-label small" for="headTeacher">Set as Head Teacher & Founder</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="activeTeacher" <?= $isActive ? 'checked' : '' ?>>
                                <label class="form-check-label small" for="activeTeacher">Active / Visible on Public Site</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mt-4 text-end">
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-check-circle me-1"></i> Save Teacher Profile
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
