<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/i18n.php';
require_once __DIR__ . '/includes/helpers.php';

$pageTitle = page_title(__('nav_batches'));

try {
    $pdo = db();
    $batches = $pdo->query('
        SELECT b.*, c.title_en as course_title_en, c.title_bn as course_title_bn, c.class_level 
        FROM batches b 
        LEFT JOIN courses c ON b.course_id = c.id 
        WHERE b.is_deleted = 0 
        ORDER BY b.id ASC
    ')->fetchAll();
} catch (Throwable $e) {
    $batches = [];
}

require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container text-center">
        <h1><?= e(__('nav_batches')) ?></h1>
        <p class="text-muted mb-0 lead">
            <?= $locale === 'bn' ? 'ফার্মগেট ক্যাম্পাসে গণিত ব্যাচসমূহের সময়সূচী ও লাইভ সিট স্ট্যাটাস' : 'Weekly batch schedule and real-time seat availability at Farmgate Campus' ?>
        </p>
    </div>
</section>

<section class="section">
    <div class="container">
        <!-- Routine Quick Link Banner -->
        <div class="alert alert-info border-0 rounded-4 p-3 d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <span class="fs-3">🗓️</span>
                <div>
                    <strong><?= $locale === 'bn' ? 'সাপ্তাহিক ক্লাসের পূর্ণাঙ্গ রুটিন দেখতে চান?' : 'Looking for the complete weekly class routine?' ?></strong>
                    <p class="mb-0 small text-muted"><?= $locale === 'bn' ? 'বারভিত্তিক ক্লাসের সময় ও রুম নম্বর রুটিন পেজে রয়েছে।' : 'Check classroom numbers, daily timings, and test schedules.' ?></p>
                </div>
            </div>
            <a href="<?= e(base_url('routine.php')) ?>" class="btn btn-primary-custom btn-sm mt-2 mt-md-0">
                <?= $locale === 'bn' ? 'রুটিন দেখুন' : 'View Routine' ?>
            </a>
        </div>

        <div class="row g-4">
            <?php if (empty($batches)): ?>
            <div class="col-12 text-center py-5">
                <p class="text-muted fs-5"><?= $locale === 'bn' ? 'কোনো ব্যাচ পাওয়া যায়নি।' : 'No batches found.' ?></p>
            </div>
            <?php else: ?>
            <?php foreach ($batches as $b): 
                $bName = $locale === 'bn' && !empty($b['batch_name_bn']) ? $b['batch_name_bn'] : $b['batch_name'];
                $cName = $locale === 'bn' && !empty($b['course_title_bn']) ? $b['course_title_bn'] : ($b['course_title_en'] ?? '');
                $avail = (int) $b['available_seats'];
                $total = (int) $b['total_seats'];
                $fillPercent = $total > 0 ? round((($total - $avail) / $total) * 100) : 0;
                $badgeClass = $avail <= 5 ? 'badge-seats-limited' : ($avail === 0 ? 'badge-seats-full' : 'badge-seats-open');
                $badgeText = $avail === 0 ? __('seats_full') : ($avail <= 5 ? __('seats_limited') . " ({$avail})" : "{$avail} " . __('course_seats') . " " . __('seats_available'));
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="batch-card">
                    <div class="batch-header">
                        <div>
                            <span class="badge bg-secondary-subtle text-secondary small mb-1"><?= e($b['class_level'] ?? 'Math') ?></span>
                            <h2 class="h5 fw-bold mb-1"><?= e($bName) ?></h2>
                            <p class="text-muted small mb-0"><?= e($cName) ?></p>
                        </div>
                        <span class="badge-seats <?= $badgeClass ?>"><?= e($badgeText) ?></span>
                    </div>

                    <!-- Seat fill progress bar -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span><?= $locale === 'bn' ? 'আসন পূরণ' : 'Seats Filled' ?></span>
                            <span class="fw-bold"><?= $fillPercent ?>%</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar <?= $avail <= 5 ? 'bg-warning' : 'bg-primary' ?>" role="progressbar" style="width: <?= $fillPercent ?>%" aria-valuenow="<?= $fillPercent ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                    <ul class="batch-meta">
                        <li><span><?= e(__('batch_days')) ?></span><span class="fw-semibold text-primary"><?= e($b['class_days']) ?></span></li>
                        <li><span><?= e(__('batch_time')) ?></span><span><?= date('h:i A', strtotime($b['start_time'])) ?> - <?= date('h:i A', strtotime($b['end_time'])) ?></span></li>
                        <li><span><?= e(__('batch_teacher')) ?></span><span><?= e($config['head_teacher']) ?></span></li>
                        <li><span><?= $locale === 'bn' ? 'শুরুর সম্ভাব্য তারিখ' : 'Session Starts' ?></span><span><?= format_date($b['start_date']) ?></span></li>
                        <li><span><?= $locale === 'bn' ? 'ক্যাম্পাস' : 'Campus' ?></span><span>Farmgate (Main)</span></li>
                    </ul>

                    <?php if ($avail > 0): ?>
                    <a class="btn btn-primary-custom btn-sm w-100 mt-auto" href="<?= e(base_url('admission.php?batch=' . urlencode($bName) . '&course=' . urlencode($cName))) ?>">
                        <?= $locale === 'bn' ? 'আসন বুক করুন' : 'Book Seat Now' ?>
                    </a>
                    <?php else: ?>
                    <button class="btn btn-secondary btn-sm w-100 mt-auto" disabled>
                        <?= e(__('seats_full')) ?>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
