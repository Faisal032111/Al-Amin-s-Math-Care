<?php

/**
 * admin/faqs/index.php — FAQ Management
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

$adminPageTitle    = 'FAQ Management — Admin Panel';
$adminPageHeading  = 'Frequently Asked Questions';
$adminPageSubheading = 'Manage bilingual FAQ entries displayed on the public website';
$activeModule      = 'faqs';

$pdo  = db();
$faqs = $pdo->query('SELECT * FROM faqs WHERE is_deleted = 0 ORDER BY sort_order ASC, id ASC')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="text-muted small">Total <strong><?= count($faqs) ?></strong> FAQ entries.</div>
    <a href="<?= e(base_url('admin/faqs/create.php')) ?>" class="btn btn-sm btn-primary">
        <i class="bi bi-patch-question-fill me-1"></i> Add New FAQ
    </a>
</div>

<div class="card card-custom">
    <div class="table-responsive">
        <table class="table table-custom mb-0">
            <thead>
                <tr>
                    <th style="width:50px">Order</th>
                    <th>Question (EN / BN)</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($faqs)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No FAQs found. Click "Add New FAQ" to create one.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($faqs as $faq): ?>
                        <tr>
                            <td class="text-center fw-bold text-muted"><?= (int)$faq['sort_order'] ?></td>
                            <td>
                                <strong class="text-dark d-block"><?= e($faq['question_en']) ?></strong>
                                <?php if (!empty($faq['question_bn'])): ?>
                                    <small class="text-muted"><?= e($faq['question_bn']) ?></small>
                                <?php endif; ?>
                                <div class="text-muted small mt-1" style="font-size:0.73rem;">
                                    <?= nl2br(e(mb_substr($faq['answer_en'], 0, 100))) ?>…
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= e(ucfirst($faq['category'])) ?></span>
                            </td>
                            <td>
                                <span class="badge <?= $faq['is_active'] ? 'badge-subtle-success' : 'badge-subtle-secondary' ?>">
                                    <?= $faq['is_active'] ? 'Active' : 'Hidden' ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="<?= e(base_url('admin/faqs/edit.php?id=' . $faq['id'])) ?>"
                                   class="btn btn-sm btn-outline-primary py-0 px-2 me-1" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" title="Delete"
                                        onclick="confirmDelete('<?= e(base_url('admin/faqs/delete.php')) ?>', <?= $faq['id'] ?>, 'Are you sure you want to delete this FAQ?')">
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
