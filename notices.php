<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/i18n.php';
require_once __DIR__ . '/includes/helpers.php';

$pageTitle = page_title(__('nav_notices'));
$selectedCategory = clean_input($_GET['cat'] ?? 'all');

try {
    $pdo = db();
    if ($selectedCategory !== 'all' && in_array($selectedCategory, ['admission', 'exam', 'routine', 'holiday', 'urgent'], true)) {
        $stmt = $pdo->prepare('SELECT * FROM notices WHERE is_deleted = 0 AND is_published = 1 AND category = :cat ORDER BY is_pinned DESC, id DESC');
        $stmt->execute([':cat' => $selectedCategory]);
        $notices = $stmt->fetchAll();
    } else {
        $notices = $pdo->query('SELECT * FROM notices WHERE is_deleted = 0 AND is_published = 1 ORDER BY is_pinned DESC, id DESC')->fetchAll();
    }
} catch (Throwable $e) {
    $notices = [];
}

$categories = [
    'all'       => $locale === 'bn' ? 'সকল নোটিশ' : 'All Notices',
    'admission' => $locale === 'bn' ? 'ভর্তি বিজ্ঞপ্তি' : 'Admissions',
    'exam'      => $locale === 'bn' ? 'পরীক্ষা ও ফলাফল' : 'Exams',
    'routine'   => $locale === 'bn' ? 'ক্লাস রুটিন' : 'Routine',
    'holiday'   => $locale === 'bn' ? 'ছুটির নোটিশ' : 'Holidays',
    'urgent'    => $locale === 'bn' ? 'জরুরি নোটিশ' : 'Urgent',
];

require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container text-center">
        <h1><?= e(__('nav_notices')) ?></h1>
        <p class="text-muted mb-0 lead">
            <?= $locale === 'bn' ? 'ভর্তি, নতুন ব্যাচ, পরীক্ষার সময়সূচী ও অ্যাকাডেমিক ঘোষণা' : 'Latest announcements, exam schedules, and admissions notices from Al Amin\'s Math Care' ?>
        </p>
    </div>
</section>

<section class="section">
    <div class="container">
        <!-- Category Filter Pills -->
        <div class="d-flex flex-wrap justify-content-center gap-2 mb-4">
            <?php foreach ($categories as $catKey => $catLabel): 
                $isActive = ($selectedCategory === $catKey) || ($catKey === 'all' && $selectedCategory === 'all');
            ?>
            <a href="<?= e(base_url('notices.php' . ($catKey === 'all' ? '' : '?cat=' . urlencode($catKey)))) ?>" 
               class="btn <?= $isActive ? 'btn-primary-custom' : 'btn-outline-custom' ?> btn-sm px-3">
                <?= e($catLabel) ?>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="row g-4">
            <?php if (empty($notices)): ?>
            <div class="col-12 text-center py-5">
                <p class="text-muted fs-5"><?= e(__('notice_no_notices')) ?></p>
            </div>
            <?php else: ?>
            <?php foreach ($notices as $notice): 
                $title = content($notice, 'title');
                $desc = content($notice, 'description');
                $isPinned = (bool) ($notice['is_pinned'] ?? false);
                $cat = $notice['category'] ?? 'general';
                $catLabel = $categories[$cat] ?? ucfirst($cat);
            ?>
            <div class="col-md-6">
                <div class="notice-card <?= $isPinned ? 'pinned' : '' ?> d-flex flex-column h-100">
                    <div class="notice-date">
                        <span>📅 <?= e(format_date($notice['created_at'])) ?></span>
                        <span class="badge bg-light text-secondary border small ms-2"><?= e($catLabel) ?></span>
                        <?php if ($isPinned): ?>
                            <span class="badge bg-warning text-dark small ms-auto">📌 <?= $locale === 'bn' ? 'জরুরি' : 'Pinned' ?></span>
                        <?php endif; ?>
                    </div>
                    <h2 class="h5 fw-bold mb-2"><?= e($title) ?></h2>
                    <p class="text-muted small mb-4 flex-grow-1" style="line-height: 1.6;"><?= nl2br(e($desc)) ?></p>

                    <?php if (!empty($notice['attachment_file'])): ?>
                    <div class="mt-auto pt-2 border-top">
                        <a href="<?= e(base_url($notice['attachment_file'])) ?>" class="btn btn-outline-custom btn-sm" target="_blank" rel="noopener">
                            📎 <?= $locale === 'bn' ? 'সংযুক্ত ফাইল ডাউনলোড' : 'Download Attachment' ?>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
