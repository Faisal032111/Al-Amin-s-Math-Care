<?php

/**
 * admin/students/index.php — Students & Guardians Registry
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

$adminPageTitle = 'Students & Guardians — Admin Panel';
$adminPageHeading = 'Enrolled Students & Guardian Directory';
$adminPageSubheading = 'Manage student IDs, batch enrollments, guardian contacts & export data';
$activeModule = 'students';

$pdo = db();

$batchFilter = (int)($_GET['batch_id'] ?? 0);
$classFilter = clean_input($_GET['class_level'] ?? '');
$search = clean_input($_GET['q'] ?? '');

$sql = 'SELECT s.*, e.batch_id, b.batch_name, b.class_days, c.title_en as course_title 
        FROM students s
        LEFT JOIN enrollments e ON s.id = e.student_id AND e.status = "active"
        LEFT JOIN batches b ON e.batch_id = b.id
        LEFT JOIN courses c ON b.course_id = c.id
        WHERE s.is_deleted = 0';
$params = [];

if ($batchFilter > 0) {
    $sql .= ' AND e.batch_id = :bid';
    $params[':bid'] = $batchFilter;
}
if (!empty($classFilter)) {
    $sql .= ' AND s.class_level = :cl';
    $params[':cl'] = $classFilter;
}
if (!empty($search)) {
    $sql .= ' AND (s.name LIKE :q OR s.student_id_code LIKE :q OR s.guardian_phone LIKE :q OR s.guardian_name LIKE :q)';
    $params[':q'] = '%' . $search . '%';
}

$sql .= ' ORDER BY s.id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();

$batches = $pdo->query('SELECT id, batch_name FROM batches WHERE is_deleted = 0 ORDER BY id ASC')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Filter & Search Bar -->
<div class="card card-custom p-3 mb-4">
    <form method="GET" action="" class="row g-2 align-items-center">
        <div class="col-md-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                <input type="text" name="q" class="form-control" value="<?= e($search) ?>" placeholder="Search student name, ID, phone...">
            </div>
        </div>
        <div class="col-md-3">
            <select name="batch_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Batches</option>
                <?php foreach ($batches as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= $batchFilter === (int)$b['id'] ? 'selected' : '' ?>><?= e($b['batch_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <select name="class_level" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Classes</option>
                <option value="Class 6-8" <?= $classFilter === 'Class 6-8' ? 'selected' : '' ?>>Class 6–8</option>
                <option value="Class 9-10 (SSC)" <?= $classFilter === 'Class 9-10 (SSC)' ? 'selected' : '' ?>>Class 9-10 (SSC)</option>
                <option value="HSC 1st/2nd" <?= $classFilter === 'HSC 1st/2nd' ? 'selected' : '' ?>>HSC 1st / 2nd</option>
                <option value="Admission" <?= $classFilter === 'Admission' ? 'selected' : '' ?>>Admission</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <a href="<?= e(base_url('admin/students/')) ?>" class="btn btn-sm btn-outline-secondary w-50">Reset</a>
            <div class="dropdown w-50">
                <button class="btn btn-sm btn-success dropdown-toggle w-100" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-file-earmark-excel me-1"></i> Export
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li><a class="dropdown-item" href="<?= e(base_url('api/export-students.php?type=students' . ($batchFilter ? '&batch_id='.$batchFilter : '') . ($classFilter ? '&class_level='.urlencode($classFilter) : ''))) ?>">Export Students CSV</a></li>
                    <li><a class="dropdown-item" href="<?= e(base_url('api/export-students.php?type=guardians' . ($batchFilter ? '&batch_id='.$batchFilter : '') . ($classFilter ? '&class_level='.urlencode($classFilter) : ''))) ?>">Export Guardians CSV</a></li>
                </ul>
            </div>
        </div>
    </form>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="text-muted small">Showing <strong><?= count($students) ?></strong> enrolled students.</div>
    <a href="<?= e(base_url('admin/students/create.php')) ?>" class="btn btn-sm btn-primary">
        <i class="bi bi-person-plus-fill me-1"></i> Admit New Student
    </a>
</div>

<div class="card card-custom">
    <div class="table-responsive">
        <table class="table table-custom mb-0">
            <thead>
                <tr>
                    <th>Student ID & Photo</th>
                    <th>Full Name & School/College</th>
                    <th>Guardian & Contact</th>
                    <th>Class & Enrolled Batch</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No students found. Click "Admit New Student" to enroll.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($students as $s): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if (!empty($s['photo']) && file_exists(__DIR__ . '/../../' . $s['photo'])): ?>
                                        <img src="<?= e(base_url($s['photo'])) ?>" alt="<?= e($s['name']) ?>" class="rounded-circle object-fit-cover shadow-sm" style="width: 40px; height: 40px;">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px; font-size: 0.9rem;">
                                            <?= strtoupper(substr($s['name'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <span class="badge bg-dark font-monospace"><?= e($s['student_id_code']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <strong class="text-dark d-block"><?= e($s['name']) ?></strong>
                                <small class="text-muted"><?= e($s['school_college'] ?: 'N/A') ?></small>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= e($s['guardian_name'] ?: 'Guardian') ?></div>
                                <a href="tel:<?= e($s['guardian_phone']) ?>" class="small text-primary text-decoration-none d-inline-block mt-1">
                                    <i class="bi bi-telephone-fill me-1"></i><?= e($s['guardian_phone']) ?>
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= e($s['class_level']) ?></span>
                                <?php if (!empty($s['batch_name'])): ?>
                                    <div class="small text-primary fw-semibold mt-1">
                                        <i class="bi bi-clock me-1"></i><?= e($s['batch_name']) ?>
                                    </div>
                                <?php else: ?>
                                    <div class="small text-muted mt-1">No active batch</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $s['status'] === 'active' ? 'badge-subtle-success' : 'badge-subtle-secondary' ?>">
                                    <?= ucfirst($s['status']) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="<?= e(base_url('admin/student-cards/view.php?id=' . $s['id'])) ?>" class="btn btn-sm btn-outline-info py-0 px-2 me-1" title="Digital ID Card">
                                    <i class="bi bi-person-badge"></i>
                                </a>
                                <a href="<?= e(base_url('admin/students/edit.php?id=' . $s['id'])) ?>" class="btn btn-sm btn-outline-primary py-0 px-2 me-1" title="Edit Student">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" title="Delete Student" onclick="confirmDelete('<?= e(base_url('admin/students/delete.php')) ?>', <?= $s['id'] ?>, 'Are you sure you want to remove student \'<?= addslashes($s['name']) ?>\' (ID: <?= e($s['student_id_code']) ?>)?')">
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
