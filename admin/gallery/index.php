<?php

/**
 * admin/gallery/index.php — Photo Gallery Management
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';

$adminPageTitle    = 'Gallery — Admin Panel';
$adminPageHeading  = 'Photo Gallery Manager';
$adminPageSubheading = 'Upload and manage classroom, event, and achievement photos';
$activeModule      = 'gallery';

$pdo    = db();
$photos = $pdo->query('SELECT * FROM gallery WHERE is_deleted = 0 ORDER BY id DESC')->fetchAll();

// Group by category for display
$grouped = [];
foreach ($photos as $p) {
    $grouped[$p['category']][] = $p;
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="text-muted small">Total <strong><?= count($photos) ?></strong> photos in gallery.</div>
    <a href="<?= e(base_url('admin/gallery/upload.php')) ?>" class="btn btn-sm btn-primary">
        <i class="bi bi-cloud-upload-fill me-1"></i> Upload Photos
    </a>
</div>

<?php if (empty($photos)): ?>
    <div class="card card-custom p-5 text-center text-muted">
        <i class="bi bi-images fs-1 mb-2"></i>
        <p class="mb-0">No photos yet. Click "Upload Photos" to add images to the gallery.</p>
    </div>
<?php else: ?>
    <?php foreach (['classroom' => 'Classroom', 'events' => 'Events', 'achievements' => 'Achievements'] as $cat => $label): ?>
        <?php if (!empty($grouped[$cat])): ?>
            <div class="mb-4">
                <h6 class="text-secondary fw-bold text-uppercase mb-3" style="font-size:.75rem; letter-spacing:.08em;">
                    <i class="bi bi-folder-fill me-1"></i> <?= $label ?> (<?= count($grouped[$cat]) ?>)
                </h6>
                <div class="row g-3">
                    <?php foreach ($grouped[$cat] as $photo): ?>
                        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                            <div class="card card-custom p-0 overflow-hidden position-relative gallery-card">
                                <img src="<?= e(base_url($photo['image_path'])) ?>"
                                     alt="<?= e($photo['caption_en'] ?? 'Gallery photo') ?>"
                                     class="img-fluid w-100 gallery-thumb"
                                     style="height:120px; object-fit:cover; display:block;">
                                <?php if (!empty($photo['caption_en'])): ?>
                                    <div class="px-2 py-1 small text-truncate text-muted" style="font-size:.72rem">
                                        <?= e($photo['caption_en']) ?>
                                    </div>
                                <?php endif; ?>
                                <div class="gallery-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center gap-2">
                                    <button type="button" class="btn btn-sm btn-danger"
                                            onclick="confirmDelete('<?= e(base_url('admin/gallery/delete.php')) ?>', <?= $photo['id'] ?>, 'Delete this photo permanently?')"
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>

<style>
.gallery-card:hover .gallery-overlay { opacity: 1; }
.gallery-overlay { opacity: 0; transition: opacity .2s ease; background: rgba(0,0,0,.55); border-radius: .375rem; }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
