<?php

/**
 * admin/results/edit.php — Edit Published Result Sheet
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

$adminPageTitle = 'Edit Result — Admin Panel';
$adminPageHeading = 'Edit Result Sheet';
$adminPageSubheading = 'Modify exam title, batch allocation or replace scorecard PDF file';
$activeModule = 'results';

$pdo = db();
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM results WHERE id = :id AND is_deleted = 0 LIMIT 1');
$stmt->execute([':id' => $id]);
$result = $stmt->fetch();

if (!$result) {
    set_flash('error', 'Result sheet not found.');
    header('Location: ' . base_url('admin/results/'));
    exit;
}

$batches = $pdo->query('SELECT id, batch_name, class_days FROM batches WHERE is_deleted = 0 ORDER BY id ASC')->fetchAll();

$errors = [];
$examTitleEn = $result['exam_title_en'];
$examTitleBn = $result['exam_title_bn'] ?? '';
$batchId = (int)($result['batch_id'] ?? 0);
$classLevel = $result['class_level'];
$publishedDate = $result['published_date'];
$filePath = $result['file_path'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $examTitleEn = clean_input($_POST['exam_title_en'] ?? '');
    $examTitleBn = clean_input($_POST['exam_title_bn'] ?? '');
    $batchId = !empty($_POST['batch_id']) ? (int)$_POST['batch_id'] : null;
    $classLevel = clean_input($_POST['class_level'] ?? '');
    $publishedDate = clean_input($_POST['published_date'] ?? date('Y-m-d'));

    if (empty($examTitleEn)) {
        $errors[] = 'Exam Title (English) is required.';
    }

    if (!empty($_FILES['result_file']['name'])) {
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
                if ($filePath && file_exists(__DIR__ . '/../../' . $filePath)) {
                    @unlink(__DIR__ . '/../../' . $filePath);
                }
                $filePath = 'assets/uploads/results/' . $fileName;
            }
        }
    }

    if (empty($errors)) {
        try {
            $update = $pdo->prepare('UPDATE results SET exam_title_en = :ten, exam_title_bn = :tbn, batch_id = :bid, class_level = :cl, file_path = :fp, published_date = :pdate WHERE id = :id');
            $update->execute([
                ':ten' => $examTitleEn,
                ':tbn' => $examTitleBn,
                ':bid' => $batchId,
                ':cl' => $classLevel,
                ':fp' => $filePath,
                ':pdate' => $publishedDate,
                ':id' => $id,
            ]);

            set_flash('success', "Exam result '{$examTitleEn}' updated successfully.");
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
            <span class="badge bg-light text-secondary border">Result ID #<?= $id ?></span>
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
                        <input type="text" name="exam_title_en" class="form-control" value="<?= e($examTitleEn) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Exam Title (Bangla / বাংলা)</label>
                        <input type="text" name="exam_title_bn" class="form-control" value="<?= e($examTitleBn) ?>">
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
                        <label class="form-label small fw-bold text-secondary">Result PDF / Image File</label>
                        <input type="file" name="result_file" class="form-control" accept=".pdf,image/*">
                        <?php if ($filePath): ?>
                            <small class="text-success d-block mt-1">Current file: <?= e($filePath) ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="col-12 mt-4 text-end">
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-save me-1"></i> Update Result
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
