<?php

/**
 * admin/fee-ledger/index.php — Monthly Fee Ledger & Collection Directory
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

$adminPageTitle = 'Fee Ledger & Payments — Admin Panel';
$adminPageHeading = 'Monthly Fee Ledger & Collection';
$adminPageSubheading = 'Track student monthly dues, record Cash/bKash payments & print official money receipts';
$activeModule = 'fee_ledger';

$pdo = db();

$selectedMonth = clean_input($_GET['month'] ?? date('Y-m'));
$statusFilter = clean_input($_GET['status'] ?? '');
$search = clean_input($_GET['q'] ?? '');

$sql = 'SELECT s.id as student_id, s.student_id_code, s.name as student_name, s.guardian_phone, s.class_level,
               b.batch_name,
               sf.id as fee_id, sf.fee_month, sf.fee_amount, sf.paid_amount, sf.due_amount, sf.status as fee_status, sf.last_paid_date,
               (SELECT receipt_no FROM fee_records fr WHERE fr.student_id = s.id ORDER BY fr.id DESC LIMIT 1) as latest_receipt_no
        FROM students s
        LEFT JOIN enrollments e ON s.id = e.student_id AND e.status = "active"
        LEFT JOIN batches b ON e.batch_id = b.id
        LEFT JOIN student_fees sf ON s.id = sf.student_id AND sf.fee_month = :m
        WHERE s.is_deleted = 0 AND s.status = "active"';
$params = [':m' => $selectedMonth];

if (!empty($statusFilter)) {
    if ($statusFilter === 'due') {
        $sql .= ' AND (sf.status = "due" OR sf.status IS NULL OR sf.status = "partial")';
    } else {
        $sql .= ' AND sf.status = :st';
        $params[':st'] = $statusFilter;
    }
}
if (!empty($search)) {
    $sql .= ' AND (s.name LIKE :q OR s.student_id_code LIKE :q OR s.guardian_phone LIKE :q)';
    $params[':q'] = '%' . $search . '%';
}

$sql .= ' ORDER BY s.student_id_code ASC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

// Month summary totals
$totalPaid = 0.0;
$totalDue = 0.0;
foreach ($rows as $r) {
    $totalPaid += (float)($r['paid_amount'] ?? 0);
    $totalDue += (float)($r['due_amount'] ?? ($r['fee_amount'] ?? 2500));
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Filter & Search Bar -->
<div class="card card-custom p-3 mb-4">
    <form method="GET" action="" class="row g-2 align-items-center">
        <div class="col-md-3">
            <label class="form-label small fw-bold text-secondary mb-1">Fee Month</label>
            <input type="month" name="month" class="form-control form-control-sm" value="<?= e($selectedMonth) ?>" onchange="this.form.submit()">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-bold text-secondary mb-1">Payment Status</label>
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Students (Paid & Due)</option>
                <option value="paid" <?= $statusFilter === 'paid' ? 'selected' : '' ?>>Paid Only</option>
                <option value="due" <?= $statusFilter === 'due' ? 'selected' : '' ?>>Due / Unpaid / Partial</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-bold text-secondary mb-1">Search Student</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                <input type="text" name="q" class="form-control" value="<?= e($search) ?>" placeholder="Student Name, ID or Phone...">
            </div>
        </div>
        <div class="col-md-2 text-end align-self-end">
            <a href="<?= e(base_url('admin/fee-ledger/')) ?>" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
        </div>
    </form>
</div>

<!-- Ledger Summary Metrics -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-4">
        <div class="card card-custom p-3 border-start border-success border-4">
            <small class="text-muted fw-semibold">Total Fee Collected (<?= date('F Y', strtotime($selectedMonth . '-01')) ?>)</small>
            <h3 class="fw-bold text-success mb-0 mt-1">৳ <?= number_format($totalPaid, 0) ?></h3>
        </div>
    </div>
    <div class="col-sm-6 col-xl-4">
        <div class="card card-custom p-3 border-start border-danger border-4">
            <small class="text-muted fw-semibold">Total Outstanding Due</small>
            <h3 class="fw-bold text-danger mb-0 mt-1">৳ <?= number_format($totalDue, 0) ?></h3>
        </div>
    </div>
    <div class="col-sm-12 col-xl-4 d-flex align-items-center justify-content-end gap-2 flex-wrap">
        <div class="dropdown">
            <button class="btn btn-success dropdown-toggle px-3 py-2 fw-bold" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-file-earmark-excel me-1"></i> Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li>
                    <a class="dropdown-item" href="<?= e(base_url('api/export-payments.php?type=ledger&month=' . $selectedMonth . ($batchId ? '&batch_id='.$batchId : '') . ($statusFilter ? '&status='.urlencode($statusFilter) : ''))) ?>">
                        <i class="bi bi-table me-2 text-success"></i>Monthly Ledger CSV
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="<?= e(base_url('api/export-payments.php?type=transactions&month=' . $selectedMonth . ($batchId ? '&batch_id='.$batchId : ''))) ?>">
                        <i class="bi bi-receipt me-2 text-primary"></i>Payment Transactions CSV
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="<?= e(base_url('api/export-payments.php?type=transactions')) ?>">
                        <i class="bi bi-download me-2 text-secondary"></i>All Transactions (Full)
                    </a>
                </li>
            </ul>
        </div>
        <a href="<?= e(base_url('admin/fee-ledger/collect.php?month=' . $selectedMonth)) ?>" class="btn btn-primary px-4 py-2 fw-bold">
            <i class="bi bi-plus-circle-fill me-1"></i> Receive Fee Payment
        </a>
    </div>
</div>

<div class="card card-custom">
    <div class="table-responsive">
        <table class="table table-custom mb-0">
            <thead>
                <tr>
                    <th>Student ID & Name</th>
                    <th>Class & Batch</th>
                    <th>Guardian Phone</th>
                    <th>Fee (৳)</th>
                    <th>Paid (৳)</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No student fee records found for the selected criteria.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rows as $r): 
                        $status = $r['fee_status'] ?? 'due';
                        $feeAmt = (float)($r['fee_amount'] ?? 2500);
                        $paidAmt = (float)($r['paid_amount'] ?? 0);
                        $dueAmt = max(0, $feeAmt - $paidAmt);
                    ?>
                        <tr>
                            <td>
                                <span class="badge bg-dark font-monospace me-1"><?= e($r['student_id_code']) ?></span>
                                <strong class="text-dark"><?= e($r['student_name']) ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= e($r['class_level']) ?></span>
                                <div class="small text-muted mt-1"><?= e($r['batch_name'] ?: 'No Batch') ?></div>
                            </td>
                            <td>
                                <a href="tel:<?= e($r['guardian_phone']) ?>" class="small text-primary text-decoration-none">
                                    <i class="bi bi-telephone me-1"></i><?= e($r['guardian_phone']) ?>
                                </a>
                            </td>
                            <td class="fw-bold text-dark">৳ <?= number_format($feeAmt, 0) ?></td>
                            <td class="fw-bold text-success">৳ <?= number_format($paidAmt, 0) ?></td>
                            <td>
                                <?php if ($status === 'paid'): ?>
                                    <span class="badge badge-subtle-success"><i class="bi bi-check-circle-fill me-1"></i> Paid</span>
                                <?php elseif ($status === 'partial'): ?>
                                    <span class="badge badge-subtle-warning">Partial (Due: ৳ <?= number_format($dueAmt, 0) ?>)</span>
                                <?php else: ?>
                                    <span class="badge badge-subtle-danger">Due</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if ($status !== 'paid'): ?>
                                    <a href="<?= e(base_url('admin/fee-ledger/collect.php?student_id=' . $r['student_id'] . '&month=' . $selectedMonth)) ?>" class="btn btn-sm btn-outline-success py-0 px-2 me-1" title="Collect Payment">
                                        <i class="bi bi-cash"></i> Collect
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($r['latest_receipt_no'])): ?>
                                    <a href="<?= e(base_url('admin/fee-ledger/receipt.php?receipt_no=' . urlencode($r['latest_receipt_no']))) ?>" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2" title="Print Money Receipt">
                                        <i class="bi bi-receipt"></i> Receipt
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
