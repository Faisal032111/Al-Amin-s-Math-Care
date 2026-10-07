<?php

/**
 * admin/students/create.php — Direct Student Admission & Enrollment
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

$adminPageTitle = 'Admit Student — Admin Panel';
$adminPageHeading = 'New Student Admission';
$adminPageSubheading = 'Generate unique Student ID, allocate batch with atomic seat-lock & register guardian';
$activeModule = 'students';

$pdo = db();

$courses = $pdo->query('SELECT id, title_en, class_level, fee FROM courses WHERE is_deleted = 0 AND is_active = 1 ORDER BY id ASC')->fetchAll();
$batches = $pdo->query('SELECT b.id, b.batch_name, b.class_days, b.available_seats, c.title_en, c.fee FROM batches b JOIN courses c ON b.course_id = c.id WHERE b.is_deleted = 0 AND b.available_seats > 0 ORDER BY b.id ASC')->fetchAll();

// Pre-fill from GET parameters if converted from lead
$leadId = (int)($_GET['lead_id'] ?? 0);
$name = clean_input($_GET['name'] ?? '');
$guardianPhone = clean_input($_GET['phone'] ?? '');
$classLevel = clean_input($_GET['class'] ?? 'Class 9-10 (SSC)');

$errors = [];
$guardianName = '';
$schoolCollege = '';
$batchId = 0;
$admissionFee = 0.0;
$paidAmount = 0.0;
$paymentMethod = 'cash';

// Auto suggest student ID
$year = date('Y');
$countSql = $pdo->query('SELECT COUNT(*) FROM students')->fetchColumn();
$nextNum = str_pad((string)((int)$countSql + 1), 3, '0', STR_PAD_LEFT);
$suggestedId = "AMC-{$year}-{$nextNum}";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $name = clean_input($_POST['name'] ?? '');
    $studentIdCode = clean_input($_POST['student_id_code'] ?? $suggestedId);
    $guardianName = clean_input($_POST['guardian_name'] ?? '');
    $guardianPhone = clean_input($_POST['guardian_phone'] ?? '');
    $classLevel = clean_input($_POST['class_level'] ?? '');
    $schoolCollege = clean_input($_POST['school_college'] ?? '');
    $batchId = (int)($_POST['batch_id'] ?? 0);
    $admissionFee = (float)($_POST['admission_fee'] ?? 0.0);
    $paidAmount = (float)($_POST['paid_amount'] ?? 0.0);
    $paymentMethod = clean_input($_POST['payment_method'] ?? 'cash');

    if (empty($name)) {
        $errors[] = 'Student Full Name is required.';
    }
    if (empty($guardianPhone) || !is_valid_bd_phone($guardianPhone)) {
        $errors[] = 'A valid 11-digit Bangladeshi mobile number is required (01XXXXXXXXX).';
    }
    if ($batchId <= 0) {
        $errors[] = 'Please select a batch to enroll the student.';
    }

    $photoPath = null;
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
                $photoPath = 'assets/uploads/students/' . $fileName;
            }
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // 1. Atomic Seat Lock on Selected Batch
            $lockStmt = $pdo->prepare('SELECT available_seats, total_seats FROM batches WHERE id = :id FOR UPDATE');
            $lockStmt->execute([':id' => $batchId]);
            $batchRow = $lockStmt->fetch();

            if (!$batchRow || (int)$batchRow['available_seats'] <= 0) {
                throw new Exception('The selected batch is already full. Please choose another batch.');
            }

            // 2. Generate QR token
            $qrToken = bin2hex(random_bytes(24));

            // 3. Insert Student Record
            $sStmt = $pdo->prepare('INSERT INTO students (student_id_code, name, guardian_name, guardian_phone, class_level, school_college, photo, qr_code_token, status) VALUES (:code, :name, :gname, :gphone, :cl, :school, :photo, :qr, "active")');
            $sStmt->execute([
                ':code' => $studentIdCode,
                ':name' => $name,
                ':gname' => $guardianName,
                ':gphone' => $guardianPhone,
                ':cl' => $classLevel,
                ':school' => $schoolCollege,
                ':photo' => $photoPath,
                ':qr' => $qrToken,
            ]);
            $studentId = (int)$pdo->lastInsertId();

            // 4. Create Active Enrollment
            $paymentStatus = $paidAmount >= $admissionFee && $admissionFee > 0 ? 'paid' : ($paidAmount > 0 ? 'partially_paid' : 'unpaid');
            $eStmt = $pdo->prepare('INSERT INTO enrollments (student_id, batch_id, enrollment_date, total_fee, paid_amount, payment_status, status) VALUES (:sid, :bid, CURDATE(), :fee, :paid, :pstat, "active")');
            $eStmt->execute([
                ':sid' => $studentId,
                ':bid' => $batchId,
                ':fee' => $admissionFee,
                ':paid' => $paidAmount,
                ':pstat' => $paymentStatus,
            ]);
            $enrollmentId = (int)$pdo->lastInsertId();

            // 5. If fee was paid at admission, record in fee_records & student_fees
            if ($paidAmount > 0) {
                $receiptNo = 'REC-' . date('Y') . '-' . str_pad((string)$studentId, 4, '0', STR_PAD_LEFT);
                $recStmt = $pdo->prepare('INSERT INTO fee_records (enrollment_id, student_id, payment_date, fee_month, amount_paid, payment_method, receipt_no, received_by, remarks) VALUES (:eid, :sid, CURDATE(), :fmonth, :amt, :method, :rec, :uid, "Admission & Course Fee")');
                $recStmt->execute([
                    ':eid'    => $enrollmentId,
                    ':sid'    => $studentId,
                    ':fmonth' => date('Y-m'),
                    ':amt'    => $paidAmount,
                    ':method' => $paymentMethod,
                    ':rec'    => $receiptNo,
                    ':uid'    => $_SESSION['admin_user_id'] ?? 1,
                ]);

                // Current month fee ledger entry
                $monthKey = date('Y-m');
                $feeLedgerStmt = $pdo->prepare('INSERT INTO student_fees (student_id, fee_month, fee_amount, paid_amount, status, last_paid_date, notes) VALUES (:sid, :m, :famt, :pamt, :st, CURDATE(), "Admission payment") ON DUPLICATE KEY UPDATE paid_amount = paid_amount + :pamt, status = "paid"');
                $feeLedgerStmt->execute([
                    ':sid' => $studentId,
                    ':m' => $monthKey,
                    ':famt' => $admissionFee,
                    ':pamt' => $paidAmount,
                    ':st' => $paymentStatus === 'paid' ? 'paid' : 'partial'
                ]);
            }

            // 6. Decrement available seats atomically
            $newAvailable = max(0, (int)$batchRow['available_seats'] - 1);
            $newAdmissionStatus = $newAvailable === 0 ? 'full' : 'open';
            $updBatch = $pdo->prepare('UPDATE batches SET available_seats = :as, admission_status = :adms WHERE id = :id');
            $updBatch->execute([
                ':as' => $newAvailable,
                ':adms' => $newAdmissionStatus,
                ':id' => $batchId,
            ]);

            // 7. Update lead status if this came from a lead
            if ($leadId > 0) {
                $pdo->prepare('UPDATE leads SET status = "admitted", admin_notes = CONCAT(IFNULL(admin_notes, ""), " | Admitted as ID: ' . $studentIdCode . '") WHERE id = :lid')->execute([':lid' => $leadId]);
            }

            $pdo->commit();

            set_flash('success', "Student '{$name}' admitted successfully with ID: {$studentIdCode}.");
            header('Location: ' . base_url('admin/students/'));
            exit;
        } catch (Throwable $e) {
            $pdo->rollBack();
            $errors[] = 'Admission failed: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="<?= e(base_url('admin/students/')) ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Student List
            </a>
            <?php if ($leadId > 0): ?>
                <span class="badge bg-info text-dark">Converting from Lead #<?= $leadId ?></span>
            <?php endif; ?>
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

                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">1. Student Profile & Information</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Student ID Code <span class="text-danger">*</span></label>
                        <input type="text" name="student_id_code" class="form-control font-monospace" value="<?= e($suggestedId) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Student Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?= e($name) ?>" required placeholder="e.g. Tanvir Ahmed">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Class Level <span class="text-danger">*</span></label>
                        <select name="class_level" class="form-select" required>
                            <option value="Class 6-8" <?= $classLevel === 'Class 6-8' ? 'selected' : '' ?>>Class 6–8 (Junior Foundation)</option>
                            <option value="Class 9-10 (SSC)" <?= $classLevel === 'Class 9-10 (SSC)' ? 'selected' : '' ?>>Class 9-10 (SSC Math)</option>
                            <option value="HSC 1st/2nd" <?= $classLevel === 'HSC 1st/2nd' ? 'selected' : '' ?>>HSC 1st / 2nd Year (Higher Math)</option>
                            <option value="Admission" <?= $classLevel === 'Admission' ? 'selected' : '' ?>>Engineering & Admission Special</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">School / College Name</label>
                        <input type="text" name="school_college" class="form-control" value="<?= e($schoolCollege) ?>" placeholder="e.g. Notre Dame College / Holy Cross">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Guardian Full Name</label>
                        <input type="text" name="guardian_name" class="form-control" value="<?= e($guardianName) ?>" placeholder="e.g. Md. Rafiqul Islam">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Guardian Mobile Number <span class="text-danger">*</span></label>
                        <input type="text" name="guardian_phone" class="form-control" value="<?= e($guardianPhone) ?>" required placeholder="017XXXXXXXX">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label small fw-bold text-secondary">Student Passport/ID Photo (Optional)</label>
                        <input type="file" name="photo" class="form-control" accept="image/*">
                    </div>
                </div>

                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">2. Batch Enrollment & Fee Payment</h6>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label small fw-bold text-secondary">Select Academic Batch <span class="text-danger">*</span></label>
                        <select name="batch_id" class="form-select" required>
                            <option value="">-- Choose Batch --</option>
                            <?php foreach ($batches as $b): ?>
                                <option value="<?= $b['id'] ?>" <?= $batchId === (int)$b['id'] ? 'selected' : '' ?>>
                                    <?= e($b['batch_name']) ?> — <?= e($b['title_en']) ?> (<?= e($b['class_days']) ?>) [<?= $b['available_seats'] ?> seats remaining]
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary">Admission / Monthly Fee (BDT)</label>
                        <input type="number" step="0.01" name="admission_fee" class="form-control" value="<?= e((string)$admissionFee) ?>" placeholder="2500">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary">Initial Amount Paid (BDT)</label>
                        <input type="number" step="0.01" name="paid_amount" class="form-control" value="<?= e((string)$paidAmount) ?>" placeholder="2500">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary">Payment Method</label>
                        <select name="payment_method" class="form-select">
                            <option value="cash" <?= $paymentMethod === 'cash' ? 'selected' : '' ?>>Cash Counter</option>
                            <option value="bkash" <?= $paymentMethod === 'bkash' ? 'selected' : '' ?>>bKash Mobile Banking</option>
                            <option value="nagad" <?= $paymentMethod === 'nagad' ? 'selected' : '' ?>>Nagad Mobile Banking</option>
                            <option value="bank" <?= $paymentMethod === 'bank' ? 'selected' : '' ?>>Bank Transfer</option>
                        </select>
                    </div>

                    <div class="col-12 mt-4 text-end">
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-check-circle me-1"></i> Complete Admission & Enroll
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
