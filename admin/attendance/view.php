<?php

/**
 * admin/attendance/view.php — View Batch Attendance Session Details
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

$batchId = (int)($_GET['batch_id'] ?? 0);
$date = clean_input($_GET['date'] ?? date('Y-m-d'));

$pdo = db();

$stmt = $pdo->prepare('SELECT b.*, c.title_en as course_title, t.name_en as teacher_name FROM batches b JOIN courses c ON b.course_id = c.id JOIN teachers t ON b.teacher_id = t.id WHERE b.id = :id AND b.is_deleted = 0 LIMIT 1');
$stmt->execute([':id' => $batchId]);
$batch = $stmt->fetch();

if (!$batch) {
    set_flash('error', 'Batch not found.');
    header('Location: ' . base_url('admin/attendance/'));
    exit;
}

$attStmt = $pdo->prepare('
    SELECT s.student_id_code, s.name, s.guardian_phone,
           a.status, a.sms_sent_status, a.sms_response, a.created_at as recorded_time
    FROM attendances a
    JOIN students s ON a.student_id = s.id
    WHERE a.batch_id = :bid AND a.attendance_date = :dt
    ORDER BY s.student_id_code ASC
');
$attStmt->execute([':bid' => $batchId, ':dt' => $date]);
$records = $attStmt->fetchAll();

$presentCount = 0;
$absentCount = 0;
$lateCount = 0;

foreach ($records as $r) {
    if ($r['status'] === 'present') $presentCount++;
    if ($r['status'] === 'absent') $absentCount++;
    if ($r['status'] === 'late') $lateCount++;
}

$adminPageTitle = 'Attendance Record — ' . $batch['batch_name'];
$adminPageHeading = 'Attendance Details: ' . $batch['batch_name'];
$adminPageSubheading = 'Session Date: ' . date('d F, Y', strtotime($date));
$activeModule = 'attendance';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="<?= e(base_url('admin/attendance/')) ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Register
            </a>
            <div class="d-flex gap-2">
                <a href="<?= e(base_url('api/export-attendance.php?batch_id=' . $batchId . '&date=' . $date)) ?>"
                   class="btn btn-sm btn-success">
                    <i class="bi bi-file-earmark-excel me-1"></i> Export CSV
                </a>
                <a href="<?= e(base_url('admin/attendance/take.php?batch_id=' . $batchId . '&date=' . $date)) ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-pencil me-1"></i> Edit Attendance
                </a>
            </div>
        </div>

        <!-- Session Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-sm-4">
                <div class="card card-custom p-3 border-start border-success border-4 text-center">
                    <small class="text-muted fw-semibold">Present Students</small>
                    <h3 class="fw-bold text-success mb-0"><?= $presentCount ?></h3>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card card-custom p-3 border-start border-danger border-4 text-center">
                    <small class="text-muted fw-semibold">Absent Students</small>
                    <h3 class="fw-bold text-danger mb-0"><?= $absentCount ?></h3>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card card-custom p-3 border-start border-warning border-4 text-center">
                    <small class="text-muted fw-semibold">Late Students</small>
                    <h3 class="fw-bold text-warning mb-0"><?= $lateCount ?></h3>
                </div>
            </div>
        </div>

        <div class="card card-custom">
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead>
                        <tr>
                            <th>Student ID & Name</th>
                            <th>Guardian Phone</th>
                            <th>Attendance Status</th>
                            <th>SMS Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($records)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No attendance recorded for this date.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($records as $rec): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-dark font-monospace me-1"><?= e($rec['student_id_code']) ?></span>
                                        <strong class="text-dark"><?= e($rec['name']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="small text-secondary"><?= e($rec['guardian_phone']) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $rec['status'] === 'present' ? 'badge-subtle-success' : ($rec['status'] === 'absent' ? 'badge-subtle-danger' : 'badge-subtle-warning') ?>">
                                            <?= ucfirst($rec['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($rec['sms_sent_status'] === 'sent'): ?>
                                            <span class="badge badge-subtle-success"><i class="bi bi-check-all me-1"></i> SMS Sent</span>
                                        <?php elseif ($rec['sms_sent_status'] === 'failed'): ?>
                                            <span class="badge badge-subtle-danger" title="<?= e($rec['sms_response'] ?? '') ?>">SMS Failed</span>
                                        <?php else: ?>
                                            <span class="text-muted small">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
