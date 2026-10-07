<?php

/**
 * admin/batches/index.php — Batches & Seat Management
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

$adminPageTitle = 'Batches & Seat Management — Admin Panel';
$adminPageHeading = 'Batches & Live Seat Management';
$adminPageSubheading = 'Track class schedules, seat capacities, room allocations & admissions status';
$activeModule = 'batches';

$pdo = db();
$batches = $pdo->query('
    SELECT b.*, c.title_en as course_title, c.class_level, t.name_en as teacher_name, br.name as branch_name
    FROM batches b
    JOIN courses c ON b.course_id = c.id
    JOIN teachers t ON b.teacher_id = t.id
    LEFT JOIN branches br ON b.branch_id = br.id
    WHERE b.is_deleted = 0
    ORDER BY b.start_date DESC, b.id DESC
')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="text-muted small">Total <strong><?= count($batches) ?></strong> academic batches registered.</div>
    <a href="<?= e(base_url('admin/batches/create.php')) ?>" class="btn btn-sm btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Create New Batch
    </a>
</div>

<div class="card card-custom">
    <div class="table-responsive">
        <table class="table table-custom mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Batch Name & Course</th>
                    <th>Class Schedule</th>
                    <th>Start Date</th>
                    <th>Seat Capacity & Occupancy</th>
                    <th>Admission Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($batches)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No batches found. Click "Create New Batch" to add one.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($batches as $b): 
                        $enrolled = max(0, $b['total_seats'] - $b['available_seats']);
                        $pct = $b['total_seats'] > 0 ? round(($enrolled / $b['total_seats']) * 100) : 0;
                    ?>
                        <tr>
                            <td><?= $b['id'] ?></td>
                            <td>
                                <strong class="text-dark d-block"><?= e($b['batch_name']) ?></strong>
                                <small class="text-muted"><?= e($b['batch_name_bn'] ?? '') ?></small>
                                <div class="small text-primary mt-1">
                                    <i class="bi bi-journal-bookmark me-1"></i><?= e($b['course_title']) ?> (<?= e($b['class_level']) ?>)
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= e($b['class_days']) ?></div>
                                <small class="text-muted">
                                    <i class="bi bi-clock me-1"></i><?= date('h:i A', strtotime($b['start_time'])) ?> – <?= date('h:i A', strtotime($b['end_time'])) ?>
                                </small>
                                <div class="small text-secondary mt-1">
                                    <i class="bi bi-person me-1"></i><?= e($b['teacher_name']) ?>
                                </div>
                            </td>
                            <td>
                                <span class="small text-muted"><?= date('d M, Y', strtotime($b['start_date'])) ?></span>
                                <div>
                                    <span class="badge <?= $b['status'] === 'running' ? 'badge-subtle-success' : ($b['status'] === 'upcoming' ? 'badge-subtle-info' : 'badge-subtle-secondary') ?>">
                                        <?= ucfirst($b['status']) ?>
                                    </span>
                                </div>
                            </td>
                            <td style="min-width: 170px;">
                                <div class="d-flex justify-content-between align-items-center small mb-1">
                                    <span class="fw-bold text-dark"><?= $enrolled ?> / <?= $b['total_seats'] ?></span>
                                    <span class="text-muted"><?= $b['available_seats'] ?> left</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar <?= $pct >= 90 ? 'bg-danger' : ($pct >= 70 ? 'bg-warning' : 'bg-success') ?>" role="progressbar" style="width: <?= $pct ?>%;"></div>
                                </div>
                            </td>
                            <td>
                                <span class="badge <?= $b['admission_status'] === 'open' ? 'badge-subtle-success' : ($b['admission_status'] === 'full' ? 'badge-subtle-danger' : 'badge-subtle-secondary') ?>">
                                    <?= strtoupper($b['admission_status']) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="<?= e(base_url('admin/batches/edit.php?id=' . $b['id'])) ?>" class="btn btn-sm btn-outline-primary py-0 px-2 me-1" title="Edit Batch">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" title="Delete Batch" onclick="confirmDelete('<?= e(base_url('admin/batches/delete.php')) ?>', <?= $b['id'] ?>, 'Are you sure you want to remove batch \'<?= addslashes($b['batch_name']) ?>\'?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
