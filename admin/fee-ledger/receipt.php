<?php

/**
 * admin/fee-ledger/receipt.php — Official Money Receipt
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/i18n.php';
require_once __DIR__ . '/../../includes/helpers.php';

$isAdmin = !empty($_SESSION['admin_logged_in']);
$isPortalStudent = !empty($_SESSION['portal_student_id']);

if (!$isAdmin && !$isPortalStudent) {
    $_SESSION['flash_error'] = 'Please log in to view money receipt.';
    header('Location: ' . base_url('portal/login.php'));
    exit;
}

$receiptNo = clean_input($_GET['receipt_no'] ?? '');

$pdo = db();
$stmt = $pdo->prepare('
    SELECT fr.*, s.student_id_code, s.name as student_name, s.guardian_name, s.guardian_phone, s.class_level,
           b.batch_name, c.title_en as course_title, u.name as receiver_name
    FROM fee_records fr
    JOIN students s ON fr.student_id = s.id
    LEFT JOIN enrollments e ON fr.enrollment_id = e.id
    LEFT JOIN batches b ON e.batch_id = b.id
    LEFT JOIN courses c ON b.course_id = c.id
    LEFT JOIN users u ON fr.received_by = u.id
    WHERE fr.receipt_no = :rec LIMIT 1
');
$stmt->execute([':rec' => $receiptNo]);
$receipt = $stmt->fetch();

if (!$receipt) {
    if ($isAdmin) {
        $_SESSION['flash_error'] = 'Money Receipt not found.';
        header('Location: ' . base_url('admin/fee-ledger/'));
    } else {
        header('Location: ' . base_url('portal/dashboard.php'));
    }
    exit;
}

// If portal student, prevent IDOR (only allow viewing own receipt)
if (!$isAdmin && $isPortalStudent && (int)$receipt['student_id'] !== (int)$_SESSION['portal_student_id']) {
    http_response_code(403);
    exit('Access Denied: You cannot view receipts belonging to another student.');
}

$backUrl = $isAdmin ? base_url('admin/fee-ledger/') : base_url('portal/dashboard.php');
$backLabel = $isAdmin ? 'Back to Fee Ledger' : 'Back to My Dashboard';
$pageTitle = 'Money Receipt — ' . $receipt['receipt_no'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Bengali:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #e2e8f0;
            font-family: 'Inter', 'Noto Sans Bengali', sans-serif;
            padding: 2rem 1rem;
            color: #1e293b;
        }
        .receipt-card {
            max-width: 680px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 2.5rem;
            border: 2px solid #cbd5e1;
            position: relative;
        }
        .receipt-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 8rem;
            font-weight: 800;
            color: rgba(21, 42, 69, 0.04);
            pointer-events: none;
            user-select: none;
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .receipt-card {
                border: 1px solid #333;
                box-shadow: none;
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>

<div class="no-print text-center mb-4">
    <a href="<?= e($backUrl) ?>" class="btn btn-sm btn-outline-secondary me-2">
        <i class="bi bi-arrow-left me-1"></i> <?= e($backLabel) ?>
    </a>
    <button onclick="window.print()" class="btn btn-sm btn-primary px-4 fw-bold">
        <i class="bi bi-printer-fill me-1"></i> Print Receipt
    </button>
</div>

<div class="receipt-card">
    <div class="receipt-watermark">PAID</div>

    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="fw-bold text-warning fs-3" style="background: #112239; width: 36px; height: 36px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center;">∑</span>
                <h4 class="fw-bold text-dark mb-0">Al Amin's Math Care</h4>
            </div>
            <div class="small text-muted">Specialized Mathematics Coaching for SSC, HSC & Admission</div>
            <div class="small text-muted">46/1, Britter Goli, Opposite Holy Cross College, Farmgate, Dhaka 1216</div>
            <div class="small text-muted fw-semibold"><i class="bi bi-telephone me-1"></i>01520102248, 01521255850</div>
        </div>
        <div class="text-end">
            <span class="badge bg-success fs-6 px-3 py-1 mb-1">MONEY RECEIPT</span>
            <div class="font-monospace fw-bold text-dark small">No: <?= e($receipt['receipt_no']) ?></div>
            <div class="text-muted small">Date: <?= date('d M, Y', strtotime(!empty($receipt['payment_date']) ? $receipt['payment_date'] : $receipt['created_at'])) ?></div>
            <?php if (!empty($receipt['fee_month'])): ?>
            <div class="text-muted small">Fee Month: <strong><?= date('F Y', strtotime($receipt['fee_month'] . '-01')) ?></strong></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Student & Details Grid -->
    <div class="bg-light p-3 rounded-3 mb-3 border">
        <div class="row g-2">
            <div class="col-sm-6">
                <small class="text-muted d-block">Student Name</small>
                <strong class="text-dark"><?= e($receipt['student_name']) ?></strong>
            </div>
            <div class="col-sm-6">
                <small class="text-muted d-block">Student ID</small>
                <span class="badge bg-dark font-monospace"><?= e($receipt['student_id_code']) ?></span>
            </div>
            <div class="col-sm-6">
                <small class="text-muted d-block">Class & Batch</small>
                <span class="text-dark"><?= e($receipt['class_level']) ?> (<?= e($receipt['batch_name'] ?: 'General Math') ?>)</span>
            </div>
            <div class="col-sm-6">
                <small class="text-muted d-block">Guardian Contact</small>
                <span class="text-dark"><?= e($receipt['guardian_phone']) ?></span>
            </div>
        </div>
    </div>

    <!-- Payment Breakdown Table -->
    <table class="table table-bordered mb-3">
        <thead class="table-light">
            <tr>
                <th>Description / Purpose</th>
                <th>Payment Method</th>
                <th class="text-end">Amount Paid</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong><?= e($receipt['remarks'] ?: 'Monthly Mathematics Tuition Fee') ?></strong>
                    <?php if (!empty($receipt['course_title'])): ?>
                        <div class="small text-muted"><?= e($receipt['course_title']) ?></div>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="badge bg-light text-dark border"><?= strtoupper(e($receipt['payment_method'])) ?></span>
                    <?php if (!empty($receipt['transaction_reference'])): ?>
                        <div class="small text-muted font-monospace mt-1">Ref: <?= e($receipt['transaction_reference']) ?></div>
                    <?php endif; ?>
                </td>
                <td class="text-end fw-bold fs-6 text-dark">
                    ৳ <?= number_format((float)$receipt['amount_paid'], 2) ?>
                </td>
            </tr>
            <tr class="table-light fw-bold">
                <td colspan="2" class="text-end">Total Amount Received:</td>
                <td class="text-end text-success fs-5">৳ <?= number_format((float)$receipt['amount_paid'], 2) ?></td>
            </tr>
        </tbody>
    </table>

    <!-- Signature & Footer -->
    <div class="row mt-5 pt-3">
        <div class="col-6 text-start">
            <div class="border-top pt-1 text-muted small" style="width: 160px;">Student / Guardian</div>
        </div>
        <div class="col-6 text-end">
            <div class="border-top pt-1 text-muted small ms-auto" style="width: 160px;">
                Authorized Officer<br>
                <small class="text-secondary"><?= e($receipt['receiver_name'] ?? 'Al Amin Sir') ?></small>
            </div>
        </div>
    </div>
</div>

</body>
</html>
