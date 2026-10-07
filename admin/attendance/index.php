<?php

/**
 * admin/attendance/index.php — Daily Attendance Management & Attendance Register
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

$adminPageTitle = 'Attendance Management — Admin Panel';
$adminPageHeading = 'Daily Class Attendance Register';
$adminPageSubheading = 'Record batch-wise student presence, review absence rates & trigger automated SMS';
$activeModule = 'attendance';

$pdo = db();

$batches = $pdo->query('
    SELECT b.id, b.batch_name, b.class_days, c.title_en as course_title,
           (SELECT COUNT(*) FROM enrollments e WHERE e.batch_id = b.id AND e.status = "active") as student_count
    FROM batches b
    JOIN courses c ON b.course_id = c.id
    WHERE b.is_deleted = 0
    ORDER BY b.id ASC
')->fetchAll();

// Fetch recent 10 attendance dates recorded
$recentDates = $pdo->query('
    SELECT a.attendance_date, a.batch_id, b.batch_name,
           SUM(CASE WHEN a.status = "present" THEN 1 ELSE 0 END) as present_count,
           SUM(CASE WHEN a.status = "absent" THEN 1 ELSE 0 END) as absent_count,
           SUM(CASE WHEN a.status = "late" THEN 1 ELSE 0 END) as late_count,
           COUNT(*) as total_recorded
    FROM attendances a
    JOIN batches b ON a.batch_id = b.id
    GROUP BY a.attendance_date, a.batch_id
    ORDER BY a.attendance_date DESC, a.batch_id ASC
    LIMIT 15
')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row g-4 mb-4">
    <!-- Quick Batch Selection for Attendance Entry -->
    <div class="col-lg-5">
        <div class="card card-custom h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-calendar-check text-success me-2"></i> Take Class Attendance</h6>
            </div>
            <div class="card-body p-4">
                <p class="text-muted small">Select a running batch and date to take today's class attendance. Automated SMS alerts can be sent to absent students' guardians.</p>
                
                <form method="GET" action="<?= e(base_url('admin/attendance/take.php')) ?>">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Select Academic Batch <span class="text-danger">*</span></label>
                        <select name="batch_id" class="form-select" required>
                            <option value="">-- Choose Batch --</option>
                            <?php foreach ($batches as $b): ?>
                                <option value="<?= $b['id'] ?>">
                                    <?= e($b['batch_name']) ?> (<?= e($b['class_days']) ?> — <?= $b['student_count'] ?> Enrolled)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-secondary">Attendance Date <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-2 fw-bold">
                        <i class="bi bi-pencil-square me-1"></i> Open Attendance Sheet →
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Attendance Performance Overview -->
    <div class="col-lg-7">
        <div class="card card-custom h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history text-primary me-2"></i> Recent Attendance Sessions</h6>
                <div class="dropdown">
                    <button class="btn btn-sm btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-file-earmark-excel me-1"></i> Export
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <?php foreach ($batches as $b): ?>
                        <li>
                            <a class="dropdown-item small" href="<?= e(base_url('api/export-attendance.php?batch_id=' . $b['id'])) ?>">
                                <i class="bi bi-journals me-2 text-primary"></i><?= e($b['batch_name']) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                        <?php if (!empty($batches)): ?><li><hr class="dropdown-divider"></li><?php endif; ?>
                        <li>
                            <a class="dropdown-item small" href="<?= e(base_url('api/export-attendance.php')) ?>">
                                <i class="bi bi-download me-2 text-secondary"></i>All Batches (Full Export)
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Batch</th>
                            <th>Present</th>
                            <th>Absent</th>
                            <th>Late</th>
                            <th class="text-end">Sheet</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentDates)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No attendance sessions recorded yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentDates as $r): ?>
                                <tr>
                                    <td>
                                        <strong class="text-dark"><?= date('d M, Y', strtotime($r['attendance_date'])) ?></strong>
                                    </td>
                                    <td>
                                        <span class="small fw-semibold text-secondary"><?= e($r['batch_name']) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge badge-subtle-success"><?= $r['present_count'] ?></span>
                                    </td>
                                    <td>
                                        <span class="badge badge-subtle-danger"><?= $r['absent_count'] ?></span>
                                    </td>
                                    <td>
                                        <span class="badge badge-subtle-warning"><?= $r['late_count'] ?></span>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= e(base_url('admin/attendance/view.php?batch_id=' . $r['batch_id'] . '&date=' . $r['attendance_date'])) ?>" class="btn btn-sm btn-outline-primary py-0 px-2" title="View Full Attendance Details">
                                            <i class="bi bi-eye"></i> View
                                        </a>
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
