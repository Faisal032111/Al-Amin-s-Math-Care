<?php

/**
 * admin/batch-switch-requests/process.php — Process Batch Switch Application
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

$id = (int)($_GET['id'] ?? 0);

$pdo = db();
$stmt = $pdo->prepare('
    SELECT r.*, s.student_id_code, s.name as student_name,
           b1.batch_name as current_batch_name, b1.class_days as current_days,
           b2.batch_name as requested_batch_name, b2.class_days as requested_days, b2.available_seats as requested_seats_left
    FROM batch_change_requests r
    JOIN students s ON r.student_id = s.id
    JOIN batches b1 ON r.current_batch_id = b1.id
    JOIN batches b2 ON r.requested_batch_id = b2.id
    WHERE r.id = :id LIMIT 1
');
$stmt->execute([':id' => $id]);
$req = $stmt->fetch();

if (!$req) {
    set_flash('error', 'Batch switch request not found.');
    header('Location: ' . base_url('admin/batch-switch-requests/'));
    exit;
}

$errors = [];
$adminPageTitle = 'Process Batch Switch — Admin Panel';
$adminPageHeading = 'Process Batch Switch Application';
$adminPageSubheading = 'Review request reason, verify target batch capacity & apply transfer';
$activeModule = 'batch_switch';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $action = clean_input($_POST['decision'] ?? '');
    $adminNote = clean_input($_POST['admin_note'] ?? '');
    $reviewerId = $_SESSION['admin_user_id'] ?? 1;

    if (!in_array($action, ['approved', 'rejected'], true)) {
        $errors[] = 'Invalid decision selected.';
    } else {
        try {
            $pdo->beginTransaction();

            if ($action === 'approved') {
                // 1. Lock requested batch row to check seats
                $lockStmt = $pdo->prepare('SELECT available_seats FROM batches WHERE id = :id FOR UPDATE');
                $lockStmt->execute([':id' => $req['requested_batch_id']]);
                $avail = (int)$lockStmt->fetchColumn();

                if ($avail <= 0) {
                    throw new Exception('The requested target batch has no available seats remaining.');
                }

                // 2. Mark old active enrollment as transferred
                $pdo->prepare('UPDATE enrollments SET status = "transferred" WHERE student_id = :sid AND batch_id = :bid AND status = "active"')->execute([
                    ':sid' => $req['student_id'],
                    ':bid' => $req['current_batch_id']
                ]);

                // 3. Create new enrollment
                $pdo->prepare('INSERT INTO enrollments (student_id, batch_id, enrollment_date, status) VALUES (:sid, :bid, CURDATE(), "active")')->execute([
                    ':sid' => $req['student_id'],
                    ':bid' => $req['requested_batch_id']
                ]);

                // 4. Adjust batch available seats atomically
                $pdo->prepare('UPDATE batches SET available_seats = available_seats + 1, admission_status = "open" WHERE id = :bid')->execute([':bid' => $req['current_batch_id']]);
                $pdo->prepare('UPDATE batches SET available_seats = available_seats - 1 WHERE id = :bid')->execute([':bid' => $req['requested_batch_id']]);
            }

            // 5. Update request status
            $pdo->prepare('UPDATE batch_change_requests SET status = :st, admin_note = :note, reviewed_by = :uid WHERE id = :id')->execute([
                ':st' => $action,
                ':note' => $adminNote,
                ':uid' => $reviewerId,
                ':id' => $id,
            ]);

            $pdo->commit();

            set_flash('success', "Batch switch request has been {$action}.");
            header('Location: ' . base_url('admin/batch-switch-requests/'));
            exit;
        } catch (Throwable $e) {
            $pdo->rollBack();
            $errors[] = 'Failed to process request: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="<?= e(base_url('admin/batch-switch-requests/')) ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Requests
            </a>
            <span class="badge bg-light text-secondary border">Request #<?= $id ?></span>
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

        <!-- Application Summary Card -->
        <div class="card card-custom p-4 mb-4">
            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Student & Batch Switch Summary</h6>
            <div class="row g-3">
                <div class="col-sm-6">
                    <small class="text-muted d-block">Student Name</small>
                    <strong class="text-dark fs-6"><?= e($req['student_name']) ?></strong>
                    <div class="font-monospace text-muted small">[<?= e($req['student_id_code']) ?>]</div>
                </div>
                <div class="col-sm-6">
                    <small class="text-muted d-block">Guardian Phone</small>
                    <a href="tel:<?= e($req['guardian_phone']) ?>" class="text-primary fw-semibold text-decoration-none">
                        <i class="bi bi-telephone me-1"></i><?= e($req['guardian_phone']) ?>
                    </a>
                </div>
                <div class="col-sm-6">
                    <small class="text-muted d-block">Current Enrolled Batch</small>
                    <span class="badge bg-light text-dark border fs-6"><?= e($req['current_batch_name']) ?></span>
                    <div class="small text-muted mt-1"><?= e($req['current_days']) ?></div>
                </div>
                <div class="col-sm-6">
                    <small class="text-muted d-block">Target Requested Batch</small>
                    <span class="badge bg-primary fs-6"><?= e($req['requested_batch_name']) ?></span>
                    <div class="small text-muted mt-1"><?= e($req['requested_days']) ?> (<strong><?= $req['requested_seats_left'] ?></strong> seats left)</div>
                </div>
                <div class="col-12">
                    <small class="text-muted d-block">Reason for Switch</small>
                    <div class="p-3 bg-light rounded-3 text-dark small border mt-1">
                        <?= nl2br(e($req['reason'])) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approval / Rejection Form -->
        <div class="card card-custom p-4">
            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Admin Decision</h6>
            <form method="POST" action="">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Decision <span class="text-danger">*</span></label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="decision" id="decApprove" value="approved" checked>
                            <label class="form-check-label fw-semibold text-success" for="decApprove">
                                <i class="bi bi-check-circle me-1"></i> Approve & Transfer Student
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="decision" id="decReject" value="rejected">
                            <label class="form-check-label fw-semibold text-danger" for="decReject">
                                <i class="bi bi-x-circle me-1"></i> Reject Application
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-secondary">Admin Review Note (Optional)</label>
                    <textarea name="admin_note" rows="3" class="form-control" placeholder="e.g. Approved as per guardian phone call request..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary px-4 fw-bold">
                    <i class="bi bi-send-check me-1"></i> Submit Decision
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
