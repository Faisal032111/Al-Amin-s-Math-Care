<?php

/**
 * admin/index.php — Master Admin Dashboard
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth_guard.php';

$adminPageTitle = 'Dashboard — Al Amin\'s Math Care';
$adminPageHeading = 'Executive Dashboard';
$adminPageSubheading = 'Real-time overview of coaching operations, leads, admissions & finances';
$activeModule = 'dashboard';

$pdo = db();

// Fetch dashboard counters
$stats = [
    'pending_leads' => 0,
    'total_students' => 0,
    'active_courses' => 0,
    'running_batches' => 0,
    'total_seats' => 0,
    'available_seats' => 0,
    'today_attendance_present' => 0,
    'today_attendance_absent' => 0,
    'monthly_fees_collected' => 0.0,
    'monthly_fees_due' => 0.0,
    'pending_batch_requests' => 0,
];

try {
    $stats['pending_leads'] = (int)$pdo->query('SELECT COUNT(*) FROM leads WHERE status IN ("new", "contacted") AND is_deleted = 0')->fetchColumn();
    $stats['total_students'] = (int)$pdo->query('SELECT COUNT(*) FROM students WHERE is_deleted = 0 AND status = "active"')->fetchColumn();
    $stats['active_courses'] = (int)$pdo->query('SELECT COUNT(*) FROM courses WHERE is_deleted = 0 AND is_active = 1')->fetchColumn();
    $stats['running_batches'] = (int)$pdo->query('SELECT COUNT(*) FROM batches WHERE is_deleted = 0')->fetchColumn();
    
    $seats = $pdo->query('SELECT SUM(total_seats) as total, SUM(available_seats) as avail FROM batches WHERE is_deleted = 0')->fetch();
    $stats['total_seats'] = (int)($seats['total'] ?? 0);
    $stats['available_seats'] = (int)($seats['avail'] ?? 0);
    $stats['filled_seats'] = max(0, $stats['total_seats'] - $stats['available_seats']);

    // Today's attendance
    $today = date('Y-m-d');
    $att = $pdo->prepare('SELECT status, COUNT(*) as count FROM attendances WHERE attendance_date = :td GROUP BY status');
    $att->execute([':td' => $today]);
    while ($row = $att->fetch()) {
        if ($row['status'] === 'present') $stats['today_attendance_present'] = (int)$row['count'];
        if ($row['status'] === 'absent') $stats['today_attendance_absent'] = (int)$row['count'];
    }

    // Fee ledger summary
    $currentMonth = date('Y-m');
    $feeSummary = $pdo->prepare('SELECT SUM(paid_amount) as paid, SUM(due_amount) as due FROM student_fees WHERE fee_month = :m');
    $feeSummary->execute([':m' => $currentMonth]);
    $feeRow = $feeSummary->fetch();
    $stats['monthly_fees_collected'] = (float)($feeRow['paid'] ?? 0.0);
    $stats['monthly_fees_due'] = (float)($feeRow['due'] ?? 0.0);

    // Pending batch switches
    $stats['pending_batch_requests'] = (int)$pdo->query('SELECT COUNT(*) FROM batch_change_requests WHERE status = "pending"')->fetchColumn();

    // Recent 5 leads
    $recentLeads = $pdo->query('SELECT l.*, c.title_en as course_title FROM leads l LEFT JOIN courses c ON l.course_id = c.id WHERE l.is_deleted = 0 ORDER BY l.created_at DESC LIMIT 6')->fetchAll();

    // Running batches list
    $batchesList = $pdo->query('SELECT b.*, c.title_en as course_title, t.name_en as teacher_name FROM batches b JOIN courses c ON b.course_id = c.id JOIN teachers t ON b.teacher_id = t.id WHERE b.is_deleted = 0 ORDER BY b.start_date DESC LIMIT 5')->fetchAll();

} catch (Throwable $e) {
    error_log('Dashboard error: ' . $e->getMessage());
    $recentLeads = [];
    $batchesList = [];
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Quick Action Shortcuts -->
<div class="row g-2 mb-4">
    <div class="col-12">
        <div class="card card-custom p-3 bg-white">
            <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                <span class="fw-bold text-secondary small"><i class="bi bi-lightning-charge-fill text-warning me-1"></i> Quick Actions:</span>
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= e(base_url('admin/students/create.php')) ?>" class="btn btn-sm btn-primary">
                        <i class="bi bi-person-plus me-1"></i> New Student Admission
                    </a>
                    <a href="<?= e(base_url('admin/attendance/take.php')) ?>" class="btn btn-sm btn-success">
                        <i class="bi bi-check2-square me-1"></i> Take Attendance
                    </a>
                    <a href="<?= e(base_url('admin/fee-ledger/collect.php')) ?>" class="btn btn-sm btn-warning text-dark">
                        <i class="bi bi-credit-card me-1"></i> Collect Fee
                    </a>
                    <a href="<?= e(base_url('admin/batches/create.php')) ?>" class="btn btn-sm btn-info text-white">
                        <i class="bi bi-calendar-plus me-1"></i> Create Batch
                    </a>
                    <a href="<?= e(base_url('admin/notices/create.php')) ?>" class="btn btn-sm btn-secondary">
                        <i class="bi bi-megaphone me-1"></i> Publish Notice
                    </a>
                    <a href="<?= e(base_url('api/export-students.php?type=students')) ?>" class="btn btn-sm btn-outline-dark">
                        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Excel
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Primary Stats Grid -->
<div class="row g-3 mb-4">
    <!-- Leads & Inquiries -->
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 border-start border-primary border-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold">Pending Leads</span>
                <span class="badge bg-primary rounded-circle p-2"><i class="bi bi-inbox-fill text-white"></i></span>
            </div>
            <h3 class="fw-bold mb-1 text-dark"><?= $stats['pending_leads'] ?></h3>
            <div class="d-flex align-items-center justify-content-between small">
                <span class="text-muted">Admission & Demos</span>
                <a href="<?= e(base_url('admin/leads/')) ?>" class="text-primary fw-semibold text-decoration-none">Manage →</a>
            </div>
        </div>
    </div>

    <!-- Active Students -->
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 border-start border-success border-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold">Enrolled Students</span>
                <span class="badge bg-success rounded-circle p-2"><i class="bi bi-people-fill text-white"></i></span>
            </div>
            <h3 class="fw-bold mb-1 text-dark"><?= $stats['total_students'] ?></h3>
            <div class="d-flex align-items-center justify-content-between small">
                <span class="text-muted">Active in batches</span>
                <a href="<?= e(base_url('admin/students/')) ?>" class="text-success fw-semibold text-decoration-none">View All →</a>
            </div>
        </div>
    </div>

    <!-- Monthly Fee Collection -->
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 border-start border-warning border-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold">Fee Collection (<?= date('M') ?>)</span>
                <span class="badge bg-warning rounded-circle p-2"><i class="bi bi-cash-stack text-dark"></i></span>
            </div>
            <h3 class="fw-bold mb-1 text-dark">৳ <?= number_format($stats['monthly_fees_collected'], 0) ?></h3>
            <div class="d-flex align-items-center justify-content-between small">
                <span class="text-danger fw-semibold">Due: ৳ <?= number_format($stats['monthly_fees_due'], 0) ?></span>
                <a href="<?= e(base_url('admin/fee-ledger/')) ?>" class="text-warning text-dark fw-semibold text-decoration-none">Ledger →</a>
            </div>
        </div>
    </div>

    <!-- Seat Occupancy -->
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 border-start border-info border-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold">Seat Occupancy</span>
                <span class="badge bg-info rounded-circle p-2"><i class="bi bi-pie-chart-fill text-white"></i></span>
            </div>
            <h3 class="fw-bold mb-1 text-dark"><?= $stats['total_seats'] > 0 ? round(($stats['filled_seats'] / $stats['total_seats']) * 100) : 0 ?>%</h3>
            <div class="d-flex align-items-center justify-content-between small">
                <span class="text-muted"><?= $stats['available_seats'] ?> seats left</span>
                <a href="<?= e(base_url('admin/batches/')) ?>" class="text-info fw-semibold text-decoration-none">Batches →</a>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Notification Banners if pending items -->
<?php if ($stats['pending_batch_requests'] > 0): ?>
    <div class="alert alert-warning d-flex align-items-center justify-content-between p-3 mb-4 rounded-3 border-warning">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-circle-fill fs-5"></i>
            <div>
                <strong><?= $stats['pending_batch_requests'] ?> Pending Batch Switch Request(s)</strong> require review and seat adjustment.
            </div>
        </div>
        <a href="<?= e(base_url('admin/batch-switch-requests/')) ?>" class="btn btn-sm btn-dark">Review Requests</a>
    </div>
<?php endif; ?>

<!-- Main Sections Grid -->
<div class="row g-4 mb-4">
    <!-- Left: Incoming Leads -->
    <div class="col-lg-7">
        <div class="card card-custom h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-inbox text-primary me-2"></i> Recent Admission & Demo Inquiries</h6>
                <a href="<?= e(base_url('admin/leads/')) ?>" class="btn btn-sm btn-outline-primary">View All Leads</a>
            </div>
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead>
                        <tr>
                            <th>Student & Phone</th>
                            <th>Class / Course</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentLeads)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No recent inquiries recorded yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentLeads as $lead): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark"><?= e($lead['student_name']) ?></div>
                                        <a href="tel:<?= e($lead['guardian_phone']) ?>" class="small text-muted text-decoration-none">
                                            <i class="bi bi-telephone me-1"></i><?= e($lead['guardian_phone']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= e($lead['class_level']) ?></span>
                                        <?php if (!empty($lead['course_title'])): ?>
                                            <div class="small text-muted mt-1"><?= e($lead['course_title']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $lead['type'] === 'demo_request' ? 'badge-subtle-info' : 'badge-subtle-primary' ?>">
                                            <?= e(ucwords(str_replace('_', ' ', $lead['type']))) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $lead['status'] === 'new' ? 'bg-danger' : ($lead['status'] === 'admitted' ? 'bg-success' : 'bg-secondary') ?>">
                                            <?= e(ucfirst($lead['status'])) ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= e(base_url('admin/leads/edit.php?id=' . $lead['id'])) ?>" class="btn btn-sm btn-outline-secondary py-0 px-2" title="Review Lead">
                                            <i class="bi bi-pencil"></i>
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

    <!-- Right: Running Batches & Capacity -->
    <div class="col-lg-5">
        <div class="card card-custom h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history text-warning me-2"></i> Active Batches & Seats</h6>
                <a href="<?= e(base_url('admin/batches/')) ?>" class="btn btn-sm btn-outline-primary">Manage</a>
            </div>
            <div class="card-body p-3">
                <?php if (empty($batchesList)): ?>
                    <p class="text-muted text-center py-4">No active batches created.</p>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($batchesList as $b): 
                            $filled = max(0, $b['total_seats'] - $b['available_seats']);
                            $pct = $b['total_seats'] > 0 ? round(($filled / $b['total_seats']) * 100) : 0;
                            $barColor = $pct >= 90 ? 'bg-danger' : ($pct >= 70 ? 'bg-warning' : 'bg-success');
                        ?>
                            <div class="border rounded-3 p-2 bg-light">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <div>
                                        <strong class="text-dark small"><?= e($b['batch_name']) ?></strong>
                                        <div class="text-muted" style="font-size: 0.75rem;"><?= e($b['class_days']) ?> (<?= date('h:i A', strtotime($b['start_time'])) ?>)</div>
                                    </div>
                                    <span class="badge <?= $b['admission_status'] === 'open' ? 'badge-subtle-success' : 'badge-subtle-danger' ?> small">
                                        <?= e(strtoupper($b['admission_status'])) ?>
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center small text-muted mb-1" style="font-size: 0.78rem;">
                                    <span><?= $filled ?> / <?= $b['total_seats'] ?> Enrolled</span>
                                    <span><?= $b['available_seats'] ?> Available</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar <?= $barColor ?>" role="progressbar" style="width: <?= $pct ?>%;" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
