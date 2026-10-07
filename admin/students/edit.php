<?php

/**
 * admin/students/edit.php — Edit Student Profile
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

$adminPageTitle = 'Edit Student — Admin Panel';
$adminPageHeading = 'Edit Student Profile';
$adminPageSubheading = 'Modify student credentials, guardian contacts & enrollment status';
$activeModule = 'students';

$pdo = db();
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT s.*, e.batch_id FROM students s LEFT JOIN enrollments e ON s.id = e.student_id AND e.status = "active" WHERE s.id = :id AND s.is_deleted = 0 LIMIT 1');
$stmt->execute([':id' => $id]);
$student = $stmt->fetch();

if (!$student) {
    set_flash('error', 'Student record not found.');
    header('Location: ' . base_url('admin/students/'));
    exit;
}

$batches = $pdo->query('SELECT id, batch_name, class_days, available_seats FROM batches WHERE is_deleted = 0 ORDER BY id ASC')->fetchAll();

$errors = [];
$name = $student['name'];
$studentIdCode = $student['student_id_code'];
$guardianName = $student['guardian_name'] ?? '';
$guardianPhone = $student['guardian_phone'];
$classLevel = $student['class_level'];
$schoolCollege = $student['school_college'] ?? '';
$status = $student['status'];
$currentBatchId = (int)($student['batch_id'] ?? 0);
$photoPath = $student['photo'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $name = clean_input($_POST['name'] ?? '');
    $guardianName = clean_input($_POST['guardian_name'] ?? '');
    $guardianPhone = clean_input($_POST['guardian_phone'] ?? '');
    $classLevel = clean_input($_POST['class_level'] ?? '');
    $schoolCollege = clean_input($_POST['school_college'] ?? '');
    $status = clean_input($_POST['status'] ?? 'active');
    $newBatchId = (int)($_POST['batch_id'] ?? 0);

    if (empty($name)) {
        $errors[] = 'Student Name is required.';
    }
    if (empty($guardianPhone) || !is_valid_bd_phone($guardianPhone)) {
        $errors[] = 'Valid 11-digit guardian phone number is required.';
    }

    // Photo Upload Handling
    if (!empty($_FILES['photo']['name'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $fileType = mime_content_type($_FILES['photo']['tmp_name']);
        if (!in_array($fileType, $allowedTypes, true)) {
            $errors[] = 'Invalid photo format. Only JPG, PNG, WebP allowed.';
        } elseif ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Photo size must not exceed 2MB.';
        } else {
            $uploadDir = __DIR__ . '/../../assets/uploads/students';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $fileName = 'student_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . '/' . $fileName)) {
                if ($photoPath && file_exists(__DIR__ . '/../../' . $photoPath)) {
                    @unlink(__DIR__ . '/../../' . $photoPath);
                }
                $photoPath = 'assets/uploads/students/' . $fileName;
            }
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $update = $pdo->prepare('UPDATE students SET name = :name, guardian_name = :gname, guardian_phone = :gphone, class_level = :cl, school_college = :sc, photo = :photo, status = :status WHERE id = :id');
            $update->execute([
                ':name' => $name,
                ':gname' => $guardianName,
                ':gphone' => $guardianPhone,
                ':cl' => $classLevel,
                ':sc' => $schoolCollege,
                ':photo' => $photoPath,
                ':status' => $status,
                ':id' => $id,
            ]);

            // Handle Batch Change if modified
            if ($newBatchId > 0 && $newBatchId !== $currentBatchId) {
                // Deactivate old enrollment
                $pdo->prepare('UPDATE enrollments SET status = "transferred" WHERE student_id = :sid AND status = "active"')->execute([':sid' => $id]);

                // Create new enrollment
                $pdo->prepare('INSERT INTO enrollments (student_id, batch_id, enrollment_date, status) VALUES (:sid, :bid, CURDATE(), "active")')->execute([
                    ':sid' => $id,
                    ':bid' => $newBatchId,
                ]);

                // Adjust seat numbers
                if ($currentBatchId > 0) {
                    $pdo->prepare('UPDATE batches SET available_seats = available_seats + 1, admission_status = "open" WHERE id = :bid')->execute([':bid' => $currentBatchId]);
                }
                $pdo->prepare('UPDATE batches SET available_seats = GREATEST(0, available_seats - 1) WHERE id = :bid')->execute([':bid' => $newBatchId]);
            }

            $pdo->commit();

            set_flash('success', "Student profile for '{$name}' updated successfully.");
            header('Location: ' . base_url('admin/students/'));
            exit;
        } catch (Throwable $e) {
            $pdo->rollBack();
            $errors[] = 'Update failed: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="<?= e(base_url('admin/students/')) ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Students
            </a>
            <span class="badge bg-dark font-monospace"><?= e($studentIdCode) ?></span>
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
                        <label class="form-label small fw-bold text-secondary">Student ID Code</label>
                        <input type="text" class="form-control font-monospace bg-light" value="<?= e($studentIdCode) ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Student Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?= e($name) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Class Level <span class="text-danger">*</span></label>
                        <select name="class_level" class="form-select" required>
                            <option value="Class 6-8" <?= $classLevel === 'Class 6-8' ? 'selected' : '' ?>>Class 6–8</option>
                            <option value="Class 9-10 (SSC)" <?= $classLevel === 'Class 9-10 (SSC)' ? 'selected' : '' ?>>Class 9-10 (SSC)</option>
                            <option value="HSC 1st/2nd" <?= $classLevel === 'HSC 1st/2nd' ? 'selected' : '' ?>>HSC 1st / 2nd Year</option>
                            <option value="Admission" <?= $classLevel === 'Admission' ? 'selected' : '' ?>>Engineering & Admission Special</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">School / College Name</label>
                        <input type="text" name="school_college" class="form-control" value="<?= e($schoolCollege) ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Guardian Full Name</label>
                        <input type="text" name="guardian_name" class="form-control" value="<?= e($guardianName) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Guardian Mobile Number <span class="text-danger">*</span></label>
                        <input type="text" name="guardian_phone" class="form-control" value="<?= e($guardianPhone) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Active Enrolled Batch</label>
                        <select name="batch_id" class="form-select">
                            <option value="0">-- No Batch Assigned --</option>
                            <?php foreach ($batches as $b): ?>
                                <option value="<?= $b['id'] ?>" <?= $currentBatchId === (int)$b['id'] ? 'selected' : '' ?>>
                                    <?= e($b['batch_name']) ?> (<?= e($b['class_days']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Student Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="passed_out" <?= $status === 'passed_out' ? 'selected' : '' ?>>Passed Out / Course Completed</option>
                            <option value="dropped" <?= $status === 'dropped' ? 'selected' : '' ?>>Dropped Out</option>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label small fw-bold text-secondary">Update Student Photo (Optional)</label>
                        <input type="file" name="photo" class="form-control" accept="image/*">
                        <?php if ($photoPath): ?>
                            <small class="text-success d-block mt-1">Current photo saved on server.</small>
                        <?php endif; ?>
                    </div>

                    <div class="col-12 mt-4 text-end">
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-save me-1"></i> Update Student Profile
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
