<?php

/**
 * admin/courses/index.php — Mathematics Courses Management
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

$adminPageTitle = 'Mathematics Courses — Admin Panel';
$adminPageHeading = 'Mathematics Courses';
$adminPageSubheading = 'Manage specialized general, higher math & admission care programs';
$activeModule = 'courses';

$pdo = db();
$courses = $pdo->query('SELECT c.*, t.name_en as teacher_name FROM courses c LEFT JOIN teachers t ON c.teacher_id = t.id WHERE c.is_deleted = 0 ORDER BY c.id ASC')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="text-muted small">Total <strong><?= count($courses) ?></strong> mathematics course programs available.</div>
    <a href="<?= e(base_url('admin/courses/create.php')) ?>" class="btn btn-sm btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Add New Math Course
    </a>
</div>

<div class="card card-custom">
    <div class="table-responsive">
        <table class="table table-custom mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Course Title (EN / BN)</th>
                    <th>Class Level & Category</th>
                    <th>Fee & Duration</th>
                    <th>Teacher</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($courses)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No courses found. Click "Add New Math Course" to create one.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($courses as $c): ?>
                        <tr>
                            <td><?= $c['id'] ?></td>
                            <td>
                                <strong class="text-dark d-block"><?= e($c['title_en']) ?></strong>
                                <small class="text-muted"><?= e($c['title_bn'] ?? '') ?></small>
                                <div class="mt-1">
                                    <code class="small text-secondary">/courses/<?= e($c['slug']) ?></code>
                                    <?php if ($c['is_popular']): ?>
                                        <span class="badge badge-subtle-warning ms-1">★ Featured</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= e($c['class_level']) ?></span>
                                <div class="small text-muted mt-1"><?= e(ucwords(str_replace('_', ' ', $c['math_category']))) ?></div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= e($c['fee_display_en'] ?: '৳ ' . number_format((float)$c['fee'], 0)) ?></div>
                                <small class="text-muted"><?= e($c['duration'] ?: 'Regular') ?></small>
                            </td>
                            <td>
                                <span class="small text-secondary"><?= e($c['teacher_name'] ?? 'Al Amin Sir') ?></span>
                            </td>
                            <td>
                                <span class="badge <?= $c['is_active'] ? 'badge-subtle-success' : 'badge-subtle-danger' ?>">
                                    <?= $c['is_active'] ? 'Active' : 'Disabled' ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="<?= e(base_url('admin/courses/edit.php?id=' . $c['id'])) ?>" class="btn btn-sm btn-outline-primary py-0 px-2 me-1" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" title="Delete" onclick="confirmDelete('<?= e(base_url('admin/courses/delete.php')) ?>', <?= $c['id'] ?>, 'Are you sure you want to remove the course \'<?= addslashes($c['title_en']) ?>\'?')">
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
