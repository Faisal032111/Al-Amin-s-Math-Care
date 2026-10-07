<?php

/**
 * admin/fee-ledger/collect.php — Receive Student Monthly Fee Payment
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

$adminPageTitle = 'Collect Fee Payment — Admin Panel';
$adminPageHeading = 'Receive Student Monthly Fee';
$adminPageSubheading = 'Record fee transaction, update monthly ledger & generate printable receipt';
$activeModule = 'fee_ledger';

$pdo = db();

$preStudentId = (int)($_GET['student_id'] ?? 0);
$month = clean_input($_GET['month'] ?? date('Y-m'));

$students = $pdo->query('
    SELECT s.id, s.student_id_code, s.name, s.class_level, b.batch_name, c.fee as course_fee
    FROM students s
    LEFT JOIN enrollments e ON s.id = e.student_id AND e.status = "active"
    LEFT JOIN batches b ON e.batch_id = b.id
    LEFT JOIN courses c ON b.course_id = c.id
    WHERE s.is_deleted = 0 AND s.status = "active"
    ORDER BY s.student_id_code ASC
')->fetchAll();

$errors = [];
$selectedStudentId = $preStudentId;
$feeAmount = 2500.0;
$paidAmount = 2500.0;
$paymentMethod = 'cash';
$transactionRef = '';
$remarks = 'Monthly Mathematics Tuition Fee';

if ($preStudentId > 0) {
    foreach ($students as $stu) {
        if ((int)$stu['id'] === $preStudentId && !empty($stu['course_fee'])) {
            $feeAmount = (float)$stu['course_fee'];
            $paidAmount = (float)$stu['course_fee'];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $selectedStudentId = (int)($_POST['student_id'] ?? 0);
    $month = clean_input($_POST['fee_month'] ?? date('Y-m'));
    $feeAmount = (float)($_POST['fee_amount'] ?? 0);
    $paidAmount = (float)($_POST['paid_amount'] ?? 0);
    $paymentMethod = clean_input($_POST['payment_method'] ?? 'cash');
    $transactionRef = clean_input($_POST['transaction_reference'] ?? '');
    $remarks = clean_input($_POST['remarks'] ?? '');

    if ($selectedStudentId <= 0) {
        $errors[] = 'Please select a student.';
    }
    if ($paidAmount <= 0) {
        $errors[] = 'Payment amount must be greater than zero.';
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // 1. Find active enrollment ID
            $enrStmt = $pdo->prepare('SELECT id FROM enrollments WHERE student_id = :sid AND status = "active" LIMIT 1');
            $enrStmt->execute([':sid' => $selectedStudentId]);
            $enrollmentId = (int)$enrStmt->fetchColumn();

            // If no active enrollment exists, create a default one
            if ($enrollmentId <= 0) {
                $pdo->prepare('INSERT INTO enrollments (student_id, batch_id, enrollment_date, total_fee, status) VALUES (:sid, 1, CURDATE(), :fee, "active")')->execute([
                    ':sid' => $selectedStudentId,
                    ':fee' => $feeAmount
                ]);
                $enrollmentId = (int)$pdo->lastInsertId();
            }

            // 2. Generate unique Receipt Number
            $receiptNo = 'REC-' . date('Y') . '-' . str_pad((string)$selectedStudentId, 3, '0', STR_PAD_LEFT) . '-' . rand(100, 999);

            // 3. Insert into fee_records (with payment_date & fee_month)
            $recStmt = $pdo->prepare('INSERT INTO fee_records (enrollment_id, student_id, payment_date, fee_month, amount_paid, payment_method, transaction_reference, receipt_no, received_by, remarks) VALUES (:eid, :sid, CURDATE(), :fmonth, :amt, :method, :tref, :rec, :uid, :rem)');
            $recStmt->execute([
                ':eid'    => $enrollmentId,
                ':sid'    => $selectedStudentId,
                ':fmonth' => $month,
                ':amt'    => $paidAmount,
                ':method' => $paymentMethod,
                ':tref'   => $transactionRef,
                ':rec'    => $receiptNo,
                ':uid'    => $_SESSION['admin_user_id'] ?? 1,
                ':rem'    => $remarks,
            ]);

            // 4. Update or insert into student_fees ledger
            $status = $paidAmount >= $feeAmount ? 'paid' : 'partial';
            $sfStmt = $pdo->prepare('
                INSERT INTO student_fees (student_id, fee_month, fee_amount, paid_amount, status, last_paid_date, notes)
                VALUES (:sid, :m, :famt, :pamt, :st, CURDATE(), :notes)
                ON DUPLICATE KEY UPDATE paid_amount = paid_amount + VALUES(paid_amount), status = IF(paid_amount >= fee_amount, "paid", "partial"), last_paid_date = CURDATE()
            ');
            $sfStmt->execute([
                ':sid' => $selectedStudentId,
                ':m' => $month,
                ':famt' => $feeAmount,
                ':pamt' => $paidAmount,
                ':st' => $status,
                ':notes' => $remarks
            ]);

            $pdo->commit();

            set_flash('success', "Payment of ৳ " . number_format($paidAmount, 0) . " recorded. Receipt No: {$receiptNo}.");
            header('Location: ' . base_url('admin/fee-ledger/receipt.php?receipt_no=' . urlencode($receiptNo)));
            exit;
        } catch (Throwable $e) {
            $pdo->rollBack();
            $errors[] = 'Failed to record payment: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="<?= e(base_url('admin/fee-ledger/')) ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Ledger
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
                    <div class="col-12">
                        <label class="form-label small fw-bold text-secondary">Select Student <span class="text-danger">*</span></label>
                        <select name="student_id" class="form-select" required onchange="updateFeeAmount(this)">
                            <option value="">-- Choose Student (ID / Name) --</option>
                            <?php foreach ($students as $s): ?>
                                <option value="<?= $s['id'] ?>" data-fee="<?= (float)($s['course_fee'] ?? 2500) ?>" <?= $selectedStudentId === (int)$s['id'] ? 'selected' : '' ?>>
                                    [<?= e($s['student_id_code']) ?>] <?= e($s['name']) ?> — <?= e($s['class_level']) ?> (<?= e($s['batch_name'] ?: 'No Batch') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Fee Month <span class="text-danger">*</span></label>
                        <input type="month" name="fee_month" class="form-control" value="<?= e($month) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Monthly Fee Due Amount (BDT)</label>
                        <input type="number" step="0.01" name="fee_amount" id="feeAmount" class="form-control" value="<?= e((string)$feeAmount) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Amount Being Paid (BDT) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="paid_amount" id="paidAmount" class="form-control" value="<?= e((string)$paidAmount) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select" required>
                            <option value="cash" <?= $paymentMethod === 'cash' ? 'selected' : '' ?>>Cash Counter</option>
                            <option value="bkash" <?= $paymentMethod === 'bkash' ? 'selected' : '' ?>>bKash (01520102248)</option>
                            <option value="nagad" <?= $paymentMethod === 'nagad' ? 'selected' : '' ?>>Nagad (01520102248)</option>
                            <option value="bank" <?= $paymentMethod === 'bank' ? 'selected' : '' ?>>Bank Deposit</option>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label small fw-bold text-secondary">Transaction / Reference ID (For bKash/Nagad/Bank)</label>
                        <input type="text" name="transaction_reference" class="form-control" value="<?= e($transactionRef) ?>" placeholder="e.g. 9J82KL091X or Cash Deposit Slip">
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold text-secondary">Remarks / Receipt Note</label>
                        <input type="text" name="remarks" class="form-control" value="<?= e($remarks) ?>" placeholder="e.g. Full Monthly Fee Received">
                    </div>

                    <div class="col-12 mt-4 text-end">
                        <button type="submit" class="btn btn-success px-4 fw-bold">
                            <i class="bi bi-check2-circle me-1"></i> Record Payment & Print Receipt
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateFeeAmount(select) {
    const selected = select.options[select.selectedIndex];
    const fee = selected.getAttribute('data-fee');
    if (fee) {
        document.getElementById('feeAmount').value = fee;
        document.getElementById('paidAmount').value = fee;
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
