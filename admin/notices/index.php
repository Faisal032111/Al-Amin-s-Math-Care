<?php

/**
 * admin/notices/index.php — Notice Board Management
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

$adminPageTitle = 'Notice Board — Admin Panel';
$adminPageHeading = 'Official Notice Board Management';
$adminPageSubheading = 'Publish exam dates, holiday notices, demo schedules & PDF announcements';
$activeModule = 'notices';

$pdo = db();
$notices = $pdo->query('SELECT * FROM notices WHERE is_deleted = 0 ORDER BY is_pinned DESC, id DESC')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="text-muted small">Total <strong><?= count($notices) ?></strong> announcements published.</div>
    <a href="<?= e(base_url('admin/notices/create.php')) ?>" class="btn btn-sm btn-primary">
        <i class="bi bi-megaphone-fill me-1"></i> Publish New Notice
    </a>
</div>

<div class="card card-custom">
    <div class="table-responsive">
        <table class="table table-custom mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Notice Title (EN / BN)</th>
                    <th>Category</th>
                    <th>Attachment</th>
                    <th>Pinned</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($notices)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No notices found. Click "Publish New Notice" to add one.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($notices as $n): ?>
                        <tr>
                            <td><?= $n['id'] ?></td>
                            <td>
                                <strong class="text-dark d-block"><?= e($n['title_en']) ?></strong>
                                <small class="text-muted"><?= e($n['title_bn'] ?? '') ?></small>
                                <div class="text-muted small mt-1" style="font-size: 0.75rem;">
                                    Published: <?= date('d M, Y', strtotime($n['created_at'])) ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?= e(ucfirst($n['category'])) ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($n['attachment_file'])): ?>
                                    <a href="<?= e(base_url($n['attachment_file'])) ?>" target="_blank" class="btn btn-sm btn-outline-info py-0 px-2 small">
                                        <i class="bi bi-paperclip"></i> View File
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">None</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($n['is_pinned']): ?>
                                    <span class="badge badge-subtle-warning"><i class="bi bi-pin-angle-fill me-1"></i> Pinned</span>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $n['is_published'] ? 'badge-subtle-success' : 'badge-subtle-secondary' ?>">
                                    <?= $n['is_published'] ? 'Published' : 'Draft' ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="<?= e(base_url('admin/notices/edit.php?id=' . $n['id'])) ?>" class="btn btn-sm btn-outline-primary py-0 px-2 me-1" title="Edit Notice">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" title="Delete Notice" onclick="confirmDelete('<?= e(base_url('admin/notices/delete.php')) ?>', <?= $n['id'] ?>, 'Are you sure you want to remove notice \'<?= addslashes($n['title_en']) ?>\'?')">
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
