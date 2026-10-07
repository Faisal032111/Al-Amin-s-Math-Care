<?php

/**
 * admin/batch-switch-requests/index.php — Batch Change Request Processing
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

$adminPageTitle = 'Batch Switch Requests — Admin Panel';
$adminPageHeading = 'Batch Change / Transfer Requests';
$adminPageSubheading = 'Review student batch switch applications, check seat capacity & approve transfers';
$activeModule = 'batch_switch';

$pdo = db();

$statusFilter = clean_input($_GET['status'] ?? '');

$sql = 'SELECT r.*, s.student_id_code, s.name as student_name,
               b1.batch_name as current_batch_name, b1.class_days as current_days,
               b2.batch_name as requested_batch_name, b2.class_days as requested_days, b2.available_seats as requested_seats_left,
               u.name as reviewer_name
        FROM batch_change_requests r
        JOIN students s ON r.student_id = s.id
        JOIN batches b1 ON r.current_batch_id = b1.id
        JOIN batches b2 ON r.requested_batch_id = b2.id
        LEFT JOIN users u ON r.reviewed_by = u.id
        WHERE 1=1';
$params = [];

if (!empty($statusFilter)) {
    $sql .= ' AND r.status = :st';
    $params[':st'] = $statusFilter;
}

$sql .= ' ORDER BY r.status = "pending" DESC, r.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card card-custom p-3 mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="btn-group btn-group-sm" role="group">
            <a href="<?= e(base_url('admin/batch-switch-requests/')) ?>" class="btn <?= empty($statusFilter) ? 'btn-primary' : 'btn-outline-primary' ?>">All Requests</a>
            <a href="<?= e(base_url('admin/batch-switch-requests/?status=pending')) ?>" class="btn <?= $statusFilter === 'pending' ? 'btn-warning text-dark' : 'btn-outline-warning' ?>">Pending Only</a>
            <a href="<?= e(base_url('admin/batch-switch-requests/?status=approved')) ?>" class="btn <?= $statusFilter === 'approved' ? 'btn-success' : 'btn-outline-success' ?>">Approved</a>
            <a href="<?= e(base_url('admin/batch-switch-requests/?status=rejected')) ?>" class="btn <?= $statusFilter === 'rejected' ? 'btn-danger' : 'btn-outline-danger' ?>">Rejected</a>
        </div>
        <div class="text-muted small">Total <strong><?= count($requests) ?></strong> batch change applications.</div>
    </div>
</div>

<div class="card card-custom">
    <div class="table-responsive">
        <table class="table table-custom mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Student & ID</th>
                    <th>Current Batch</th>
                    <th>Requested Batch</th>
                    <th>Reason / Details</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($requests)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No batch change requests found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($requests as $r): ?>
                        <tr>
                            <td>
                                <div class="small fw-semibold text-dark"><?= date('d M, Y', strtotime($r['created_at'])) ?></div>
                            </td>
                            <td>
                                <span class="badge bg-dark font-monospace"><?= e($r['student_id_code']) ?></span>
                                <strong class="text-dark d-block"><?= e($r['student_name']) ?></strong>
                                <small class="text-muted"><i class="bi bi-telephone me-1"></i><?= e($r['guardian_phone']) ?></small>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border"><?= e($r['current_batch_name']) ?></span>
                                <div class="small text-muted mt-1"><?= e($r['current_days']) ?></div>
                            </td>
                            <td>
                                <span class="badge bg-light text-primary border"><?= e($r['requested_batch_name']) ?></span>
                                <div class="small text-muted mt-1"><?= e($r['requested_days']) ?> (<?= $r['requested_seats_left'] ?> seats left)</div>
                            </td>
                            <td style="max-width: 250px;">
                                <div class="small text-muted mb-1 text-truncate" title="<?= e($r['reason']) ?>">
                                    "<?= e($r['reason']) ?>"
                                </div>
                                <?php if (!empty($r['admin_note'])): ?>
                                    <div class="small text-secondary bg-light p-1 rounded border">
                                        Note: <?= e($r['admin_note']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($r['status'] === 'pending'): ?>
                                    <span class="badge badge-subtle-warning">Pending Review</span>
                                <?php elseif ($r['status'] === 'approved'): ?>
                                    <span class="badge badge-subtle-success"><i class="bi bi-check-circle me-1"></i> Approved</span>
                                <?php else: ?>
                                    <span class="badge badge-subtle-danger">Rejected</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if ($r['status'] === 'pending'): ?>
                                    <a href="<?= e(base_url('admin/batch-switch-requests/process.php?id=' . $r['id'])) ?>" class="btn btn-sm btn-primary py-0 px-2">
                                        Review & Decide
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">Reviewed</span>
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
