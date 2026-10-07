<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/i18n.php';
require_once __DIR__ . '/includes/helpers.php';

$pageTitle = page_title(__('page_teachers_title'));

try {
    $pdo = db();
    $teachers = $pdo->query('SELECT * FROM teachers WHERE is_deleted = 0 AND is_active = 1 ORDER BY is_head_teacher DESC, sort_order ASC, id ASC')->fetchAll();
} catch (Throwable $e) {
    $teachers = [];
}

require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container text-center">
        <h1><?= e(__('page_teachers_title')) ?></h1>
        <p class="text-muted mb-0 lead">
            <?= $locale === 'bn' ? 'ফার্মগেটে অভিজ্ঞ ও আন্তরিক শিক্ষক প্যানেলের সাথে গণিতের সর্বোচ্চ প্রস্তুতি' : 'Learn from experienced mathematics mentors dedicated to building rock-solid conceptual clarity' ?>
        </p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-4 justify-content-center">
            <?php if (empty($teachers)): ?>
            <div class="col-12 text-center py-5">
                <p class="text-muted fs-5"><?= $locale === 'bn' ? 'কোনো শিক্ষকের তথ্য পাওয়া যায়নি।' : 'No teacher profiles available.' ?></p>
            </div>
            <?php else: ?>
            <?php foreach ($teachers as $t): 
                $name = content($t, 'name');
                $designation = content($t, 'designation');
                $bio = content($t, 'bio');
                $exp = (int) ($t['experience_years'] ?? 10);
            ?>
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
                    <div class="row align-items-center g-4">
                        <div class="col-md-4 text-center">
                            <div class="teacher-avatar mb-3" style="width: 140px; height: 140px; font-size: 3rem;">
                                ∑
                            </div>
                            <span class="badge bg-primary-subtle text-primary px-3 py-2 fw-semibold rounded-pill">
                                🎓 <?= e($t['qualification'] ?? 'B.Sc (Hons), M.Sc in Mathematics') ?>
                            </span>
                            <div class="mt-2 text-muted small">
                                ⏳ <?= $exp ?>+ <?= $locale === 'bn' ? 'বছরের শিক্ষকতা' : 'Years Experience' ?>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <span class="badge bg-warning-subtle text-dark fw-semibold small mb-2"><?= $locale === 'bn' ? 'প্রধান শিক্ষক ও প্রতিষ্ঠাতা' : 'Head Teacher & Founder' ?></span>
                            <h2 class="h3 fw-bold mb-1"><?= e($name) ?></h2>
                            <p class="text-primary fw-semibold fs-5 mb-3"><?= e($designation) ?></p>
                            <p class="text-muted mb-4" style="line-height: 1.7;"><?= e($bio) ?></p>

                            <div class="d-flex flex-wrap gap-2">
                                <a class="btn btn-primary-custom" href="<?= e(base_url('teacher-profile.php?slug=' . urlencode($t['slug']))) ?>">
                                    <?= $locale === 'bn' ? 'স্যারের পূর্ণ পরিচিতি ও দর্শন' : 'View Full Profile & Bio' ?>
                                </a>
                                <a class="btn btn-outline-custom" href="<?= e(base_url('admission.php?demo=1')) ?>">
                                    <?= e(__('btn_free_demo')) ?>
                                </a>
                                <a class="btn btn-whatsapp" href="<?= e(whatsapp_link($config['whatsapp'], __('whatsapp_prefill', ['course' => __('mathematics')]))) ?>" target="_blank" rel="noopener">
                                    <?= e(__('btn_whatsapp')) ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
