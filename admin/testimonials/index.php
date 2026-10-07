<?php

/**
 * admin/testimonials/index.php — Testimonial Management
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

$adminPageTitle    = 'Testimonials — Admin Panel';
$adminPageHeading  = 'Student Testimonials & Reviews';
$adminPageSubheading = 'Manage success stories and star ratings shown on the homepage';
$activeModule      = 'testimonials';

$pdo          = db();
$testimonials = $pdo->query('SELECT * FROM testimonials WHERE is_deleted = 0 ORDER BY id DESC')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="text-muted small">Total <strong><?= count($testimonials) ?></strong> testimonials.</div>
    <a href="<?= e(base_url('admin/testimonials/create.php')) ?>" class="btn btn-sm btn-primary">
        <i class="bi bi-star-fill me-1"></i> Add Testimonial
    </a>
</div>

<div class="card card-custom">
    <div class="table-responsive">
        <table class="table table-custom mb-0">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Rating</th>
                    <th>Quote Preview</th>
                    <th>Photo</th>
                    <th>Featured</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($testimonials)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No testimonials yet. Click "Add Testimonial" to create one.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($testimonials as $t): ?>
                        <tr>
                            <td>
                                <strong class="text-dark d-block"><?= e($t['student_name']) ?></strong>
                                <small class="text-muted"><?= e($t['student_role'] ?? '') ?></small>
                            </td>
                            <td>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi bi-star<?= $i <= (int)$t['rating'] ? '-fill text-warning' : ' text-muted' ?>" style="font-size:0.75rem"></i>
                                <?php endfor; ?>
                                <small class="text-muted ms-1"><?= (int)$t['rating'] ?>/5</small>
                            </td>
                            <td class="text-muted small" style="max-width:280px">
                                <?= e(mb_substr($t['quote_en'], 0, 100)) ?>…
                            </td>
                            <td>
                                <?php if (!empty($t['photo'])): ?>
                                    <img src="<?= e(base_url($t['photo'])) ?>" alt="Photo" width="36" height="36"
                                         class="rounded-circle object-fit-cover border">
                                <?php else: ?>
                                    <span class="text-muted small">None</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $t['is_featured'] ? 'badge-subtle-success' : 'badge-subtle-secondary' ?>">
                                    <?= $t['is_featured'] ? 'Featured' : 'Hidden' ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="<?= e(base_url('admin/testimonials/edit.php?id=' . $t['id'])) ?>"
                                   class="btn btn-sm btn-outline-primary py-0 px-2 me-1" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" title="Delete"
                                        onclick="confirmDelete('<?= e(base_url('admin/testimonials/delete.php')) ?>', <?= $t['id'] ?>, 'Delete testimonial from <?= addslashes($t['student_name']) ?>?')">
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
