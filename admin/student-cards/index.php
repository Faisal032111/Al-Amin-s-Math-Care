<?php

/**
 * admin/student-cards/index.php — Digital Student ID Cards & QR Directory
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

$adminPageTitle = 'Student ID Cards — Admin Panel';
$adminPageHeading = 'Digital Student ID Cards & QR Generator';
$adminPageSubheading = 'Generate, preview & print official student identity badges with digital QR verification';
$activeModule = 'student_cards';

$pdo = db();

$batchFilter = (int)($_GET['batch_id'] ?? 0);
$search = clean_input($_GET['q'] ?? '');

$sql = 'SELECT s.*, b.batch_name, c.title_en as course_title 
        FROM students s
        LEFT JOIN enrollments e ON s.id = e.student_id AND e.status = "active"
        LEFT JOIN batches b ON e.batch_id = b.id
        LEFT JOIN courses c ON b.course_id = c.id
        WHERE s.is_deleted = 0 AND s.status = "active"';
$params = [];

if ($batchFilter > 0) {
    $sql .= ' AND e.batch_id = :bid';
    $params[':bid'] = $batchFilter;
}
if (!empty($search)) {
    $sql .= ' AND (s.name LIKE :q OR s.student_id_code LIKE :q OR s.guardian_phone LIKE :q)';
    $params[':q'] = '%' . $search . '%';
}

$sql .= ' ORDER BY s.student_id_code ASC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();

$batches = $pdo->query('SELECT id, batch_name FROM batches WHERE is_deleted = 0 ORDER BY id ASC')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card card-custom p-3 mb-4">
    <form method="GET" action="" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                <input type="text" name="q" class="form-control" value="<?= e($search) ?>" placeholder="Search student name, ID...">
            </div>
        </div>
        <div class="col-md-4">
            <select name="batch_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Batches</option>
                <?php foreach ($batches as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= $batchFilter === (int)$b['id'] ? 'selected' : '' ?>><?= e($b['batch_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 text-end">
            <a href="<?= e(base_url('admin/student-cards/')) ?>" class="btn btn-sm btn-outline-secondary w-100">Reset Filters</a>
        </div>
    </form>
</div>

<div class="card card-custom">
    <div class="table-responsive">
        <table class="table table-custom mb-0">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Photo & Full Name</th>
                    <th>Class Level</th>
                    <th>Enrolled Batch</th>
                    <th>QR Token</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No students found for ID card generation.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($students as $s): ?>
                        <tr>
                            <td>
                                <span class="badge bg-dark font-monospace"><?= e($s['student_id_code']) ?></span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if (!empty($s['photo']) && file_exists(__DIR__ . '/../../' . $s['photo'])): ?>
                                        <img src="<?= e(base_url($s['photo'])) ?>" alt="" class="rounded-circle object-fit-cover shadow-sm" style="width: 36px; height: 36px;">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center small fw-bold" style="width: 36px; height: 36px;">
                                            <?= strtoupper(substr($s['name'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <strong class="text-dark d-block"><?= e($s['name']) ?></strong>
                                        <small class="text-muted"><?= e($s['school_college'] ?: 'N/A') ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= e($s['class_level']) ?></span>
                            </td>
                            <td>
                                <span class="small fw-semibold text-secondary"><?= e($s['batch_name'] ?: 'Not Assigned') ?></span>
                            </td>
                            <td>
                                <?php if (!empty($s['qr_code_token'])): ?>
                                    <span class="badge badge-subtle-success"><i class="bi bi-qr-code me-1"></i> Active</span>
                                <?php else: ?>
                                    <span class="badge badge-subtle-warning">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= e(base_url('admin/student-cards/view.php?id=' . $s['id'])) ?>" class="btn btn-sm btn-primary py-0 px-2" title="Generate / Print ID Card">
                                    <i class="bi bi-person-badge-fill me-1"></i> Print Card
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
