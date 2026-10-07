<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/i18n.php';
require_once __DIR__ . '/includes/helpers.php';

$pageTitle = page_title(__('page_courses_title'));

$selectedClass = clean_input($_GET['class'] ?? 'all');

try {
    $pdo = db();
    if ($selectedClass !== 'all' && $selectedClass !== '') {
        $stmt = $pdo->prepare('SELECT * FROM courses WHERE is_deleted = 0 AND is_active = 1 AND (class_level LIKE :cl OR slug LIKE :sl) ORDER BY id ASC');
        $stmt->execute([
            ':cl' => "%{$selectedClass}%",
            ':sl' => "%{$selectedClass}%"
        ]);
        $courses = $stmt->fetchAll();
    } else {
        $courses = $pdo->query('SELECT * FROM courses WHERE is_deleted = 0 AND is_active = 1 ORDER BY id ASC')->fetchAll();
    }
} catch (Throwable $e) {
    $courses = courses_data();
}

$filters = [
    'all'       => $locale === 'bn' ? 'সকল কোর্স' : 'All Courses',
    'Class 6-8' => $locale === 'bn' ? 'ক্লাস ৬–৮' : 'Class 6–8',
    'SSC'       => $locale === 'bn' ? 'এসএসসি (সাধারণ ও উচ্চতর)' : 'SSC General & Higher',
    'HSC'       => $locale === 'bn' ? 'এইচএসসি (১ম ও ২য় পত্র)' : 'HSC Higher Math',
    'Admission' => $locale === 'bn' ? 'ভর্তি পরীক্ষা স্পেশাল' : 'University & Engineering Admission',
];

require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container text-center">
        <h1><?= e(__('page_courses_title')) ?></h1>
        <p class="text-muted mb-0 lead"><?= e(__('page_courses_lead')) ?></p>
    </div>
</section>

<section class="section">
    <div class="container">
        <!-- Class Filter Navigation -->
        <div class="d-flex flex-wrap justify-content-center gap-2 mb-5">
            <?php foreach ($filters as $key => $label): 
                $isActive = ($selectedClass === $key) || ($key === 'all' && $selectedClass === 'all');
            ?>
            <a href="<?= e(base_url('courses.php' . ($key === 'all' ? '' : '?class=' . urlencode($key)))) ?>" 
               class="btn <?= $isActive ? 'btn-primary-custom' : 'btn-outline-custom' ?> btn-sm px-3 py-2">
                <?= e($label) ?>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Course Cards Grid -->
        <div class="row g-4">
            <?php if (empty($courses)): ?>
            <div class="col-12 text-center py-5">
                <p class="text-muted fs-5"><?= $locale === 'bn' ? 'কোনো কোর্স পাওয়া যায়নি।' : 'No courses found in this category.' ?></p>
                <a href="<?= e(base_url('courses.php')) ?>" class="btn btn-outline-custom mt-2"><?= e(__('btn_view_all')) ?></a>
            </div>
            <?php else: ?>
            <?php foreach ($courses as $course): 
                $title = isset($course['title_en']) ? content($course, 'title') : ($course['subject'] ?? '');
                $desc = isset($course['description_en']) ? content($course, 'description') : '';
                $feeDisplay = isset($course['fee_display_en']) ? content($course, 'fee_display') : ($course['fee'] ?? 'Contact us');
                $classLevel = $course['class_level'] ?? ($course['class'] ?? 'Mathematics');
                $duration = $course['duration'] ?? 'Full Syllabus';
                $slug = $course['slug'] ?? 'course';
                $waMsg = __('whatsapp_prefill', ['course' => $title]);
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="course-card d-flex flex-column h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge-class"><?= e($classLevel) ?></span>
                        <span class="badge bg-success-subtle text-success fw-semibold small"><?= e(__('seats_available')) ?></span>
                    </div>
                    <h2 class="h5 fw-bold mt-1 mb-2">
                        <a href="<?= e(base_url('course-details.php?slug=' . urlencode($slug))) ?>" class="text-dark">
                            <?= e($title) ?>
                        </a>
                    </h2>
                    <?php if (!empty($desc)): ?>
                    <p class="text-muted small mb-3"><?= e($desc) ?></p>
                    <?php endif; ?>
                    <ul class="course-meta flex-grow-1">
                        <li><span><?= e(__('course_subject')) ?></span><span><?= e(__('mathematics')) ?></span></li>
                        <li><span><?= e(__('course_teacher')) ?></span><span><?= e($config['head_teacher']) ?></span></li>
                        <li><span><?= e(__('course_duration')) ?></span><span><?= e($duration) ?></span></li>
                        <li><span><?= e(__('course_fee')) ?></span><span class="fw-bold text-primary"><?= e($feeDisplay) ?></span></li>
                    </ul>
                    <div class="d-flex gap-2 flex-wrap pt-3 border-top">
                        <a class="btn btn-outline-custom btn-sm flex-fill text-center" href="<?= e(base_url('course-details.php?slug=' . urlencode($slug))) ?>">
                            <?= e(__('btn_view_details')) ?>
                        </a>
                        <a class="btn btn-primary-custom btn-sm flex-fill text-center" href="<?= e(base_url('admission.php?course=' . urlencode($title))) ?>">
                            <?= e(__('btn_apply')) ?>
                        </a>
                        <a class="btn btn-whatsapp btn-sm px-3" href="<?= e(whatsapp_link($config['whatsapp'], $waMsg)) ?>" target="_blank" rel="noopener" title="WhatsApp">
                            💬
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Bottom CTA Band -->
<section class="section bg-white pb-5">
    <div class="container">
        <div class="cta-band">
            <h2><?= $locale === 'bn' ? 'সঠিক কোর্স বেছে নিতে দ্বিধাগ্রস্ত?' : 'Not sure which course is right for you?' ?></h2>
            <p><?= $locale === 'bn' ? 'আল আমিন স্যারের সাথে সরাসরি ফ্রি ডেমো ক্লাস করে সিদ্ধান্ত নিন।' : 'Attend a free demo class with Al Amin Sir and experience the methodology firsthand.' ?></p>
            <div class="d-flex flex-wrap justify-content-center gap-2">
                <a class="btn btn-cta btn-lg" href="<?= e(base_url('admission.php?demo=1')) ?>"><?= e(__('btn_free_demo')) ?></a>
                <a class="btn btn-light btn-lg fw-semibold" href="<?= e(phone_link($config['phone_primary'])) ?>"><?= e(__('btn_call')) ?></a>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
