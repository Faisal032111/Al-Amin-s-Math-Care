<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/i18n.php';
require_once __DIR__ . '/includes/helpers.php';

$pageTitle = page_title(__('nav_results'));

try {
    $pdo = db();
    $results = $pdo->query('
        SELECT r.*, b.batch_name, b.batch_name_bn 
        FROM results r 
        LEFT JOIN batches b ON r.batch_id = b.id 
        WHERE r.is_deleted = 0 
        ORDER BY r.published_date DESC
    ')->fetchAll();
} catch (Throwable $e) {
    $results = [];
}

require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container text-center">
        <h1><?= e(__('nav_results')) ?></h1>
        <p class="text-muted mb-0 lead">
            <?= $locale === 'bn' ? 'সাপ্তাহিক ও মাসিক পরীক্ষার ফলাফল এবং বোর্ড স্ট্যান্ডার্ড মূল্যায়ন শিট' : 'Exam scorecards, weekly chapter test ranks, and board model test merit lists' ?>
        </p>
    </div>
</section>

<section class="section">
    <div class="container">
        <!-- Direct Scorecard Search Callout -->
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-5 bg-primary text-white text-center">
            <div class="max-w-600 mx-auto" style="max-width: 650px;">
                <span class="fs-1">🎯</span>
                <h2 class="h3 fw-bold mt-2 mb-2"><?= e(__('results_lookup_title')) ?></h2>
                <p class="opacity-90 mb-4">
                    <?= $locale === 'bn' ? 'আপনার রোল নম্বর ও অভিভাবকের মোবাইল নম্বরের শেষ ৪ ডিজিট দিয়ে তাৎক্ষণিক ব্যক্তিগত মার্কশিট দেখুন।' : 'Enter your Roll Number and last 4 digits of guardian phone to view your individual scorecard.' ?>
                </p>
                <a href="<?= e(base_url('results-lookup.php')) ?>" class="btn btn-cta btn-lg px-4 fw-bold">
                    🔍 <?= e(__('results_search_btn')) ?>
                </a>
            </div>
        </div>

        <!-- Published Merit Lists & Sheets -->
        <div class="section-header">
            <h2><?= $locale === 'bn' ? 'প্রকাশিত মেধা তালিকা ও রেজাল্ট শিট' : 'Published Exam Results & Merit Lists' ?></h2>
            <p><?= $locale === 'bn' ? 'ক্লাস ও ব্যাচ অনুযায়ী পিডিএফ রেজাল্ট শিট ডাউনলোড করুন' : 'Download class-wise and batch-wise PDF score sheets' ?></p>
        </div>

        <div class="row g-3 justify-content-center">
            <?php if (empty($results)): ?>
            <div class="col-12 text-center py-5">
                <p class="text-muted fs-5"><?= $locale === 'bn' ? 'বর্তমানে কোনো ফলাফল শিট প্রকাশিত হয়নি।' : 'No published result sheets available at the moment.' ?></p>
            </div>
            <?php else: ?>
            <?php foreach ($results as $res): 
                $resTitle = content($res, 'exam_title');
                $bName = $locale === 'bn' && !empty($res['batch_name_bn']) ? $res['batch_name_bn'] : ($res['batch_name'] ?? '');
            ?>
            <div class="col-md-8">
                <div class="result-card">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-primary-subtle text-primary fw-semibold small"><?= e($res['class_level']) ?></span>
                            <?php if (!empty($bName)): ?>
                                <span class="badge bg-secondary-subtle text-secondary small"><?= e($bName) ?></span>
                            <?php endif; ?>
                        </div>
                        <h3 class="h5 fw-bold mb-1"><?= e($resTitle) ?></h3>
                        <span class="text-muted small">📅 <?= $locale === 'bn' ? 'প্রকাশের তারিখ' : 'Published' ?>: <?= e(format_date($res['published_date'])) ?></span>
                    </div>
                    <div>
                        <a class="btn btn-outline-custom btn-sm" href="<?= e(base_url($res['file_path'])) ?>" target="_blank" rel="noopener">
                            📄 <?= e(__('btn_download')) ?> PDF
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
