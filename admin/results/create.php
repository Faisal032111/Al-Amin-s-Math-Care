<?php

/**
 * admin/results/create.php — Upload New Result Scorecard
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

$adminPageTitle = 'Upload Result — Admin Panel';
$adminPageHeading = 'Upload Exam Result Sheet';
$adminPageSubheading = 'Publish chapter test marks, merit lists & scorecard PDF files';
$activeModule = 'results';

$pdo = db();
$batches = $pdo->query('SELECT id, batch_name, class_days FROM batches WHERE is_deleted = 0 ORDER BY id ASC')->fetchAll();

$errors = [];
$examTitleEn = '';
$examTitleBn = '';
$batchId = 0;
$classLevel = 'Class 9-10 (SSC)';
$publishedDate = date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $examTitleEn = clean_input($_POST['exam_title_en'] ?? '');
    $examTitleBn = clean_input($_POST['exam_title_bn'] ?? '');
    $batchId = !empty($_POST['batch_id']) ? (int)$_POST['batch_id'] : null;
    $classLevel = clean_input($_POST['class_level'] ?? '');
    $publishedDate = clean_input($_POST['published_date'] ?? date('Y-m-d'));

    $filePath = null;

    if (empty($examTitleEn)) {
        $errors[] = 'Exam Title (English) is required.';
    }

    if (empty($_FILES['result_file']['name'])) {
        $errors[] = 'Result sheet PDF or Image file is required.';
    } else {
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];
        $fileType = mime_content_type($_FILES['result_file']['tmp_name']);
        if (!in_array($fileType, $allowedTypes, true)) {
            $errors[] = 'Only PDF or Image (JPG, PNG, WebP) files are supported.';
        } elseif ($_FILES['result_file']['size'] > 10 * 1024 * 1024) {
            $errors[] = 'Result file size must not exceed 10MB.';
        } else {
            $uploadDir = __DIR__ . '/../../assets/uploads/results';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $ext = pathinfo($_FILES['result_file']['name'], PATHINFO_EXTENSION);
            $fileName = 'result_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
            if (move_uploaded_file($_FILES['result_file']['tmp_name'], $uploadDir . '/' . $fileName)) {
                $filePath = 'assets/uploads/results/' . $fileName;
            } else {
                $errors[] = 'Failed to upload result file.';
            }
        }
    }

    if (empty($errors) && $filePath) {
        try {
            $stmt = $pdo->prepare('INSERT INTO results (exam_title_en, exam_title_bn, batch_id, class_level, file_path, published_date) VALUES (:ten, :tbn, :bid, :cl, :fp, :pdate)');
            $stmt->execute([
                ':ten' => $examTitleEn,
                ':tbn' => $examTitleBn,
                ':bid' => $batchId,
                ':cl' => $classLevel,
                ':fp' => $filePath,
                ':pdate' => $publishedDate,
            ]);

            set_flash('success', "Exam result '{$examTitleEn}' uploaded successfully.");
            header('Location: ' . base_url('admin/results/'));
            exit;
        } catch (Throwable $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="<?= e(base_url('admin/results/')) ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Results
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
                        <label class="form-label small fw-bold text-secondary">Exam Title (English) <span class="text-danger">*</span></label>
                        <input type="text" name="exam_title_en" class="form-control" value="<?= e($examTitleEn) ?>" required placeholder="e.g. SSC Higher Math Chapter 3 Test">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Exam Title (Bangla / বাংলা)</label>
                        <input type="text" name="exam_title_bn" class="form-control" value="<?= e($examTitleBn) ?>" placeholder="যেমন: এসএসসি উচ্চতর গণিত অধ্যায় ৩ পরীক্ষা">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Class Level <span class="text-danger">*</span></label>
                        <select name="class_level" class="form-select" required>
                            <option value="Class 6-8" <?= $classLevel === 'Class 6-8' ? 'selected' : '' ?>>Class 6–8</option>
                            <option value="Class 9-10 (SSC)" <?= $classLevel === 'Class 9-10 (SSC)' ? 'selected' : '' ?>>Class 9-10 (SSC)</option>
                            <option value="HSC 1st/2nd" <?= $classLevel === 'HSC 1st/2nd' ? 'selected' : '' ?>>HSC 1st / 2nd Year</option>
                            <option value="Admission" <?= $classLevel === 'Admission' ? 'selected' : '' ?>>University / Engineering Admission</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Batch (Optional)</label>
                        <select name="batch_id" class="form-select">
                            <option value="">-- All Batches / General --</option>
                            <?php foreach ($batches as $b): ?>
                                <option value="<?= $b['id'] ?>" <?= $batchId === (int)$b['id'] ? 'selected' : '' ?>><?= e($b['batch_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Published Date <span class="text-danger">*</span></label>
                        <input type="date" name="published_date" class="form-control" value="<?= e($publishedDate) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Result PDF / Image File <span class="text-danger">*</span></label>
                        <input type="file" name="result_file" class="form-control" accept=".pdf,image/*" required>
                    </div>

                    <div class="col-12 mt-4 text-end">
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-upload me-1"></i> Upload & Publish Result
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
