<?php

/**
 * admin/results/index.php — Exam Results & Merit Scorecard Management
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

$adminPageTitle = 'Exam Results — Admin Panel';
$adminPageHeading = 'Exam Results & Merit Scorecards';
$adminPageSubheading = 'Upload weekly chapter test scorecards, model test merit lists & PDF result sheets';
$activeModule = 'results';

$pdo = db();
$results = $pdo->query('
    SELECT r.*, b.batch_name
    FROM results r
    LEFT JOIN batches b ON r.batch_id = b.id
    WHERE r.is_deleted = 0
    ORDER BY r.published_date DESC, r.id DESC
')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="text-muted small">Total <strong><?= count($results) ?></strong> published exam result sheets.</div>
    <a href="<?= e(base_url('admin/results/create.php')) ?>" class="btn btn-sm btn-primary">
        <i class="bi bi-trophy-fill me-1"></i> Upload New Result
    </a>
</div>

<div class="card card-custom">
    <div class="table-responsive">
        <table class="table table-custom mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Exam Title (EN / BN)</th>
                    <th>Class Level & Batch</th>
                    <th>Published Date</th>
                    <th>Result File</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($results)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No result sheets found. Click "Upload New Result" to publish one.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($results as $res): ?>
                        <tr>
                            <td><?= $res['id'] ?></td>
                            <td>
                                <strong class="text-dark d-block"><?= e($res['exam_title_en']) ?></strong>
                                <small class="text-muted"><?= e($res['exam_title_bn'] ?? '') ?></small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= e($res['class_level']) ?></span>
                                <?php if (!empty($res['batch_name'])): ?>
                                    <div class="small text-muted mt-1"><?= e($res['batch_name']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="small text-muted"><?= date('d M, Y', strtotime($res['published_date'])) ?></span>
                            </td>
                            <td>
                                <?php if (!empty($res['file_path'])): ?>
                                    <a href="<?= e(base_url($res['file_path'])) ?>" target="_blank" class="btn btn-sm btn-outline-info py-0 px-2 small">
                                        <i class="bi bi-file-earmark-pdf"></i> View PDF
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">No File</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= e(base_url('admin/results/edit.php?id=' . $res['id'])) ?>" class="btn btn-sm btn-outline-primary py-0 px-2 me-1" title="Edit Result">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" title="Delete Result" onclick="confirmDelete('<?= e(base_url('admin/results/delete.php')) ?>', <?= $res['id'] ?>, 'Are you sure you want to delete result \'<?= addslashes($res['exam_title_en']) ?>\'?')">
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
