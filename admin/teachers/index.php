<?php

/**
 * admin/teachers/index.php — Teachers & Faculty Management
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

$adminPageTitle = 'Teachers Faculty — Admin Panel';
$adminPageHeading = 'Mathematics Faculty Management';
$adminPageSubheading = 'Manage Head Teacher Al Amin Sir & teaching mentor profiles, bios & photo portraits';
$activeModule = 'teachers';

$pdo = db();
$teachers = $pdo->query('SELECT * FROM teachers WHERE is_deleted = 0 ORDER BY is_head_teacher DESC, sort_order ASC, id ASC')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="text-muted small">Total <strong><?= count($teachers) ?></strong> mathematics instructors registered.</div>
    <a href="<?= e(base_url('admin/teachers/create.php')) ?>" class="btn btn-sm btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Add New Teacher
    </a>
</div>

<div class="card card-custom">
    <div class="table-responsive">
        <table class="table table-custom mb-0">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Teacher Name (EN / BN)</th>
                    <th>Designation</th>
                    <th>Qualifications & Experience</th>
                    <th>Head Teacher</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($teachers)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No teachers found. Click "Add New Teacher" to create one.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($teachers as $t): ?>
                        <tr>
                            <td style="width: 60px;">
                                <?php if (!empty($t['photo']) && file_exists(__DIR__ . '/../../' . $t['photo'])): ?>
                                    <img src="<?= e(base_url($t['photo'])) ?>" alt="<?= e($t['name_en']) ?>" class="rounded-circle object-fit-cover shadow-sm" style="width: 44px; height: 44px;">
                                <?php else: ?>
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 44px; height: 44px; font-size: 1rem;">
                                        <?= strtoupper(substr($t['name_en'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong class="text-dark d-block"><?= e($t['name_en']) ?></strong>
                                <small class="text-muted"><?= e($t['name_bn'] ?? '') ?></small>
                                <div class="mt-1">
                                    <code class="small text-secondary">/teachers/<?= e($t['slug']) ?></code>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= e($t['designation_en']) ?></div>
                                <small class="text-muted"><?= e($t['designation_bn'] ?? '') ?></small>
                            </td>
                            <td>
                                <div class="small text-dark"><?= e($t['qualification']) ?></div>
                                <small class="text-muted"><?= $t['experience_years'] ?>+ Years Teaching Experience</small>
                            </td>
                            <td>
                                <?php if ($t['is_head_teacher']): ?>
                                    <span class="badge badge-subtle-warning"><i class="bi bi-star-fill text-warning me-1"></i> Head Teacher</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-secondary border">Instructor</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $t['is_active'] ? 'badge-subtle-success' : 'badge-subtle-danger' ?>">
                                    <?= $t['is_active'] ? 'Active' : 'Hidden' ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="<?= e(base_url('admin/teachers/edit.php?id=' . $t['id'])) ?>" class="btn btn-sm btn-outline-primary py-0 px-2 me-1" title="Edit Profile">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if (!$t['is_head_teacher']): ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" title="Delete Profile" onclick="confirmDelete('<?= e(base_url('admin/teachers/delete.php')) ?>', <?= $t['id'] ?>, 'Are you sure you want to remove teacher \'<?= addslashes($t['name_en']) ?>\'?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
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
