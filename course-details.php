<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/i18n.php';
require_once __DIR__ . '/includes/helpers.php';

$slug = clean_input($_GET['slug'] ?? '');
if (empty($slug)) {
    header('Location: ' . base_url('courses.php'));
    exit;
}

$course = null;
$batches = [];
$teacher = null;

try {
    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM courses WHERE slug = :slug AND is_deleted = 0 AND is_active = 1 LIMIT 1');
    $stmt->execute([':slug' => $slug]);
    $course = $stmt->fetch();

    if ($course) {
        $bStmt = $pdo->prepare('SELECT * FROM batches WHERE course_id = :cid AND is_deleted = 0 ORDER BY id ASC');
        $bStmt->execute([':cid' => $course['id']]);
        $batches = $bStmt->fetchAll();

        $tStmt = $pdo->prepare('SELECT * FROM teachers WHERE id = :tid AND is_deleted = 0 LIMIT 1');
        $tStmt->execute([':tid' => $course['teacher_id'] ?: 1]);
        $teacher = $tStmt->fetch();
    }
} catch (Throwable $e) {
    // If DB fails, fallback
}

if (!$course) {
    http_response_code(404);
    $pageTitle = 'Course Not Found — ' . app_config()['site_name'];
    require __DIR__ . '/includes/header.php';
    echo '<div class="container py-5 text-center"><h2>Course not found</h2><p><a href="' . e(base_url('courses.php')) . '" class="btn btn-primary-custom mt-3">Back to Courses</a></p></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$title = content($course, 'title');
$desc = content($course, 'description');
$feeDisplay = content($course, 'fee_display');
$classLevel = $course['class_level'];
$duration = $course['duration'] ?? '1 Year';
$pageTitle = page_title($title);
$waMsg = __('whatsapp_prefill', ['course' => $title]);

require __DIR__ . '/includes/header.php';
?>

<!-- Breadcrumbs & Course Hero -->
<section class="page-hero">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2 small">
                <li class="breadcrumb-item"><a href="<?= e(base_url('index.php')) ?>"><?= e(__('nav_home')) ?></a></li>
                <li class="breadcrumb-item"><a href="<?= e(base_url('courses.php')) ?>"><?= e(__('nav_courses')) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= e($title) ?></li>
            </ol>
        </nav>
        <span class="badge-class"><?= e($classLevel) ?></span>
        <h1 class="mt-2 mb-3"><?= e($title) ?></h1>
        <p class="text-muted lead mb-0"><?= e($desc) ?></p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-4">
            <!-- Left Main Column -->
            <div class="col-lg-8">
                <!-- Course Overview Card -->
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <h2 class="h4 fw-bold text-primary mb-3">
                        <?= $locale === 'bn' ? 'কোর্সের মূল বিষয়বস্তু ও পাঠদান পরিকল্পনা' : 'Course Overview & Curriculum Highlights' ?>
                    </h2>
                    <p class="text-muted mb-4"><?= e($desc) ?></p>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <h3 class="h6 fw-bold mb-2">🎯 <?= $locale === 'bn' ? 'সৃজনশীল (CQ) সমাধান' : 'Creative Question (CQ) Mastery' ?></h3>
                                <p class="small text-muted mb-0">
                                    <?= $locale === 'bn' ? 'প্রতিটি অধ্যায়ের গুরুত্বপূর্ণ বোর্ড প্রশ্ন ও টেস্ট পেপার প্রশ্ন নিখুঁতভাবে সমাধান।' : 'In-depth step-by-step solutions for Board exam creative questions and top school test papers.' ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <h3 class="h6 fw-bold mb-2">⚡ <?= $locale === 'bn' ? 'MCQ শর্টকাট টেকনিক' : 'High-Speed MCQ Tricks' ?></h3>
                                <p class="small text-muted mb-0">
                                    <?= $locale === 'bn' ? 'ক্যালকুলেটর হ্যাক ও গণিতের জ্যামিতিক টেকনিক ব্যবহার করে ৩০ সেকেন্ডে MCQ নির্ভুল সমাধান।' : 'Calculator techniques and algebraic shortcuts to solve multiple choice questions within 30 seconds.' ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <h3 class="h6 fw-bold mb-2">📝 <?= $locale === 'bn' ? 'সাপ্তাহিক OMR ও ক্লাস টেস্ট' : 'Weekly OMR & Written Exams' ?></h3>
                                <p class="small text-muted mb-0">
                                    <?= $locale === 'bn' ? 'প্রতি সপ্তাহে পরীক্ষা নিয়ে মেধা তালিকা প্রকাশ ও অভিভাবকদের ফোনে সরাসরি SMS আপডেট প্রেরণ।' : 'Weekly exams with instant SMS scorecard alerts to parents and online rank tracking.' ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <h3 class="h6 fw-bold mb-2">👨‍🏫 <?= $locale === 'bn' ? 'আল আমিন স্যারের সরাসরি তত্ত্বাবধান' : 'Mentored by Al Amin Sir' ?></h3>
                                <p class="small text-muted mb-0">
                                    <?= $locale === 'bn' ? 'কোনো শিক্ষানবিস বা সহকারী নয়, স্যারের প্রত্যক্ষ উপস্থিতিতে প্রতিটি ক্লাসে গণিতের ভয় দূরীকরণ।' : 'Taught exclusively by Head Teacher Al Amin Sir with maximum 30 students per batch.' ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Available Batches for this Course -->
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <h2 class="h4 fw-bold text-primary mb-3">
                        <?= $locale === 'bn' ? 'এই কোর্সের সক্রিয় ব্যাচসমূহ' : 'Available Batches for This Course' ?>
                    </h2>
                    <?php if (empty($batches)): ?>
                    <p class="text-muted"><?= $locale === 'bn' ? 'বর্তমানে নতুন ব্যাচের সময়সূচী নির্ধারণাধীন। বিস্তারিত জানতে কল করুন।' : 'New batch schedule is currently being finalized. Please contact the front desk.' ?></p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><?= e(__('batch_status')) ?></th>
                                    <th><?= $locale === 'bn' ? 'ব্যাচের নাম' : 'Batch Name' ?></th>
                                    <th><?= e(__('batch_days')) ?></th>
                                    <th><?= e(__('batch_time')) ?></th>
                                    <th><?= e(__('batch_available_seats')) ?></th>
                                    <th><?= e(__('btn_apply')) ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($batches as $b): 
                                    $bName = $locale === 'bn' && !empty($b['batch_name_bn']) ? $b['batch_name_bn'] : $b['batch_name'];
                                    $seats = (int) $b['available_seats'];
                                ?>
                                <tr>
                                    <td>
                                        <span class="badge <?= $seats > 0 ? 'bg-success' : 'bg-danger' ?>">
                                            <?= $seats > 0 ? e(__('seats_available')) : e(__('seats_full')) ?>
                                        </span>
                                    </td>
                                    <td class="fw-bold"><?= e($bName) ?></td>
                                    <td><?= e($b['class_days']) ?></td>
                                    <td><?= date('h:i A', strtotime($b['start_time'])) ?> - <?= date('h:i A', strtotime($b['end_time'])) ?></td>
                                    <td><span class="fw-bold text-primary"><?= $seats ?></span> / <?= $b['total_seats'] ?></td>
                                    <td>
                                        <a href="<?= e(base_url('admission.php?batch=' . urlencode($bName) . '&course=' . urlencode($title))) ?>" class="btn btn-primary-custom btn-sm">
                                            <?= e(__('btn_apply')) ?>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Sidebar Column -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 90px;">
                    <div class="mb-3 text-center">
                        <span class="text-muted small uppercase"><?= e(__('course_fee')) ?></span>
                        <div class="display-6 fw-bold text-primary"><?= e($feeDisplay) ?></div>
                    </div>

                    <ul class="list-unstyled border-top pt-3 mb-4">
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted"><?= e(__('course_duration')) ?></span>
                            <span class="fw-semibold"><?= e($duration) ?></span>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted"><?= e(__('course_subject')) ?></span>
                            <span class="fw-semibold"><?= e(__('mathematics')) ?></span>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted"><?= e(__('course_teacher')) ?></span>
                            <span class="fw-semibold"><?= e($config['head_teacher']) ?></span>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted"><?= $locale === 'bn' ? 'ব্যাচ ধারণক্ষমতা' : 'Batch Size' ?></span>
                            <span class="fw-semibold"><?= $locale === 'bn' ? 'সর্বোচ্চ ৩০ জন' : 'Max 30 Students' ?></span>
                        </li>
                        <li class="d-flex justify-content-between py-2">
                            <span class="text-muted"><?= $locale === 'bn' ? 'ক্যাম্পাস' : 'Campus' ?></span>
                            <span class="fw-semibold">Farmgate (Main)</span>
                        </li>
                    </ul>

                    <div class="d-grid gap-2">
                        <a href="<?= e(base_url('admission.php?course=' . urlencode($title))) ?>" class="btn btn-primary-custom btn-lg">
                            <?= e(__('btn_apply')) ?>
                        </a>
                        <a href="<?= e(base_url('admission.php?demo=1&course=' . urlencode($title))) ?>" class="btn btn-outline-custom btn-lg">
                            <?= e(__('btn_free_demo')) ?>
                        </a>
                        <a href="<?= e(whatsapp_link($config['whatsapp'], $waMsg)) ?>" target="_blank" rel="noopener" class="btn btn-whatsapp btn-lg">
                            <?= e(__('btn_whatsapp')) ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
