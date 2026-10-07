<?php

/**
 * admin/courses/create.php — Add New Mathematics Course
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

$adminPageTitle = 'Add Course — Admin Panel';
$adminPageHeading = 'Add New Mathematics Course';
$adminPageSubheading = 'Define course title, math specialization, class level, duration & syllabus details';
$activeModule = 'courses';

$pdo = db();
$teachers = $pdo->query('SELECT id, name_en FROM teachers WHERE is_deleted = 0 ORDER BY id ASC')->fetchAll();

$errors = [];
$titleEn = '';
$titleBn = '';
$slug = '';
$classLevel = 'Class 9-10 (SSC)';
$mathCategory = 'general_math';
$courseType = 'regular';
$duration = '';
$fee = '0';
$feeDisplayEn = '';
$feeDisplayBn = '';
$teacherId = 1;
$descEn = '';
$descBn = '';
$isPopular = 0;
$isActive = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $titleEn = clean_input($_POST['title_en'] ?? '');
    $titleBn = clean_input($_POST['title_bn'] ?? '');
    $slug = slugify($_POST['slug'] ?? $titleEn);
    $classLevel = clean_input($_POST['class_level'] ?? '');
    $mathCategory = clean_input($_POST['math_category'] ?? 'general_math');
    $courseType = clean_input($_POST['course_type'] ?? 'regular');
    $duration = clean_input($_POST['duration'] ?? '');
    $fee = (float)($_POST['fee'] ?? 0);
    $feeDisplayEn = clean_input($_POST['fee_display_en'] ?? '');
    $feeDisplayBn = clean_input($_POST['fee_display_bn'] ?? '');
    $teacherId = !empty($_POST['teacher_id']) ? (int)$_POST['teacher_id'] : null;
    $descEn = clean_input($_POST['description_en'] ?? '');
    $descBn = clean_input($_POST['description_bn'] ?? '');
    $isPopular = isset($_POST['is_popular']) ? 1 : 0;
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if (empty($titleEn)) {
        $errors[] = 'Course Title (English) is required.';
    }
    if (empty($slug)) {
        $errors[] = 'Valid URL slug is required.';
    }

    if (empty($errors)) {
        try {
            // Check slug uniqueness
            $check = $pdo->prepare('SELECT COUNT(*) FROM courses WHERE slug = :s');
            $check->execute([':s' => $slug]);
            if ((int)$check->fetchColumn() > 0) {
                $slug .= '-' . rand(100, 999);
            }

            $stmt = $pdo->prepare('INSERT INTO courses (slug, title_en, title_bn, class_level, math_category, course_type, duration, fee, fee_display_en, fee_display_bn, teacher_id, description_en, description_bn, is_popular, is_active) VALUES (:slug, :ten, :tbn, :cl, :mc, :ct, :dur, :fee, :fde, :fdb, :tid, :den, :dbn, :pop, :act)');
            $stmt->execute([
                ':slug' => $slug,
                ':ten' => $titleEn,
                ':tbn' => $titleBn,
                ':cl' => $classLevel,
                ':mc' => $mathCategory,
                ':ct' => $courseType,
                ':dur' => $duration,
                ':fee' => $fee,
                ':fde' => $feeDisplayEn,
                ':fdb' => $feeDisplayBn,
                ':tid' => $teacherId,
                ':den' => $descEn,
                ':dbn' => $descBn,
                ':pop' => $isPopular,
                ':act' => $isActive,
            ]);

            set_flash('success', "Mathematics Course '{$titleEn}' created successfully.");
            header('Location: ' . base_url('admin/courses/'));
            exit;
        } catch (Throwable $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="<?= e(base_url('admin/courses/')) ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Courses
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
            <form method="POST" action="">
                <?= csrf_field() ?>

                <div class="row g-3">
                    <!-- Title EN & BN -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Course Title (English) <span class="text-danger">*</span></label>
                        <input type="text" name="title_en" class="form-control" value="<?= e($titleEn) ?>" required placeholder="e.g. SSC Higher Mathematics">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Course Title (Bangla / বাংলা)</label>
                        <input type="text" name="title_bn" class="form-control" value="<?= e($titleBn) ?>" placeholder="যেমন: এসএসসি উচ্চতর গণিত">
                    </div>

                    <!-- Slug & Class Level -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">URL Slug (e.g. ssc-higher-math)</label>
                        <input type="text" name="slug" class="form-control" value="<?= e($slug) ?>" placeholder="ssc-higher-math">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Class Level <span class="text-danger">*</span></label>
                        <select name="class_level" class="form-select" required>
                            <option value="Class 6-8" <?= $classLevel === 'Class 6-8' ? 'selected' : '' ?>>Class 6–8 (Foundation)</option>
                            <option value="Class 9-10 (SSC)" <?= $classLevel === 'Class 9-10 (SSC)' ? 'selected' : '' ?>>Class 9-10 (SSC)</option>
                            <option value="HSC 1st/2nd" <?= $classLevel === 'HSC 1st/2nd' ? 'selected' : '' ?>>HSC 1st / 2nd Year</option>
                            <option value="Admission" <?= $classLevel === 'Admission' ? 'selected' : '' ?>>University / Engineering Admission</option>
                        </select>
                    </div>

                    <!-- Math Category & Course Type -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Math Category (Exclusive Mathematics) <span class="text-danger">*</span></label>
                        <select name="math_category" class="form-select" required>
                            <option value="general_math" <?= $mathCategory === 'general_math' ? 'selected' : '' ?>>General Math (সাধারণ গণিত)</option>
                            <option value="higher_math_1st" <?= $mathCategory === 'higher_math_1st' ? 'selected' : '' ?>>Higher Math 1st Paper (উচ্চতর গণিত ১ম)</option>
                            <option value="higher_math_2nd" <?= $mathCategory === 'higher_math_2nd' ? 'selected' : '' ?>>Higher Math 2nd Paper (উচ্চতর গণিত ২য়)</option>
                            <option value="combined_higher_math" <?= $mathCategory === 'combined_higher_math' ? 'selected' : '' ?>>Combined Higher Math (১ম ও ২য় পত্র)</option>
                            <option value="admission_engineering_math" <?= $mathCategory === 'admission_engineering_math' ? 'selected' : '' ?>>Admission & Engineering Math (ভর্তি স্পেশাল)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Course Type</label>
                        <select name="course_type" class="form-select">
                            <option value="regular" <?= $courseType === 'regular' ? 'selected' : '' ?>>Regular Academic Batch</option>
                            <option value="crash" <?= $courseType === 'crash' ? 'selected' : '' ?>>Crash Course</option>
                            <option value="model_test" <?= $courseType === 'model_test' ? 'selected' : '' ?>>Model Test & CQ-MCQ Practice</option>
                            <option value="special_care" <?= $courseType === 'special_care' ? 'selected' : '' ?>>Intensive Special Care</option>
                        </select>
                    </div>

                    <!-- Fee & Display Texts -->
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary">Numeric Fee Amount (BDT)</label>
                        <input type="number" step="0.01" name="fee" class="form-control" value="<?= e((string)$fee) ?>" placeholder="2500">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary">Display Fee (English)</label>
                        <input type="text" name="fee_display_en" class="form-control" value="<?= e($feeDisplayEn) ?>" placeholder="৳ 2,500 / month">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary">Display Fee (Bangla / বাংলা)</label>
                        <input type="text" name="fee_display_bn" class="form-control" value="<?= e($feeDisplayBn) ?>" placeholder="৳ ২,৫০০ / মাস">
                    </div>

                    <!-- Duration & Teacher -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Duration / Schedule Note</label>
                        <input type="text" name="duration" class="form-control" value="<?= e($duration) ?>" placeholder="e.g. 1 Year Full Syllabus">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Assign Teacher</label>
                        <select name="teacher_id" class="form-select">
                            <?php foreach ($teachers as $t): ?>
                                <option value="<?= $t['id'] ?>" <?= $teacherId === (int)$t['id'] ? 'selected' : '' ?>><?= e($t['name_en']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Descriptions EN & BN -->
                    <div class="col-12">
                        <label class="form-label small fw-bold text-secondary">Course Description & Syllabus (English)</label>
                        <textarea name="description_en" rows="3" class="form-control" placeholder="Key topics, syllabus coverage, CQ/MCQ practice strategy..."><?= e($descEn) ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold text-secondary">Course Description & Syllabus (Bangla / বাংলা)</label>
                        <textarea name="description_bn" rows="3" class="form-control" placeholder="সিলেবাস, অধ্যায়ভিত্তিক অনুশীলন এবং বিশেষ যত্নের বিবরণ..."><?= e($descBn) ?></textarea>
                    </div>

                    <!-- Options -->
                    <div class="col-12">
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_popular" value="1" id="isPopular" <?= $isPopular ? 'checked' : '' ?>>
                                <label class="form-check-label small" for="isPopular">Feature on Homepage</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" <?= $isActive ? 'checked' : '' ?>>
                                <label class="form-check-label small" for="isActive">Course Active / Published</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mt-4 text-end">
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-check-circle me-1"></i> Save Mathematics Course
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
