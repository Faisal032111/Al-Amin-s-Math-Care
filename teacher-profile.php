<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/i18n.php';
require_once __DIR__ . '/includes/helpers.php';

$slug = clean_input($_GET['slug'] ?? 'al-amin-sir');

$teacher = null;
$courses = [];

try {
    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM teachers WHERE slug = :slug AND is_deleted = 0 AND is_active = 1 LIMIT 1');
    $stmt->execute([':slug' => $slug]);
    $teacher = $stmt->fetch();

    if ($teacher) {
        $cStmt = $pdo->prepare('SELECT * FROM courses WHERE teacher_id = :tid AND is_deleted = 0 AND is_active = 1 ORDER BY id ASC');
        $cStmt->execute([':tid' => $teacher['id']]);
        $courses = $cStmt->fetchAll();
    }
} catch (Throwable $e) {
    // Graceful fallback
}

if (!$teacher) {
    header('Location: ' . base_url('teachers.php'));
    exit;
}

$name = content($teacher, 'name');
$designation = content($teacher, 'designation');
$bio = content($teacher, 'bio');
$pageTitle = page_title($name);

require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2 small">
                <li class="breadcrumb-item"><a href="<?= e(base_url('index.php')) ?>"><?= e(__('nav_home')) ?></a></li>
                <li class="breadcrumb-item"><a href="<?= e(base_url('teachers.php')) ?>"><?= e(__('nav_teachers')) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= e($name) ?></li>
            </ol>
        </nav>
        <h1 class="mt-2 mb-1"><?= e($name) ?></h1>
        <p class="text-primary lead mb-0 fw-semibold"><?= e($designation) ?></p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-4">
            <!-- Left Bio Column -->
            <div class="col-lg-8">
                <!-- Academic Philosophy -->
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <h2 class="h4 fw-bold text-primary mb-3">
                        <?= $locale === 'bn' ? 'শিক্ষক পরিচিতি ও শিক্ষাদর্শন' : 'Academic Profile & Teaching Philosophy' ?>
                    </h2>
                    <p class="text-muted mb-4" style="line-height: 1.8; font-size: 1.05rem;">
                        <?= e($bio) ?>
                    </p>

                    <h3 class="h5 fw-bold mb-3">
                        <?= $locale === 'bn' ? 'পাঠদানের বিশেষ ৪টি মূলনীতি' : 'Core Teaching Pillars' ?>
                    </h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <h4 class="h6 fw-bold mb-2">1. <?= $locale === 'bn' ? 'মুখস্থ নয়, ধারণাগত স্পষ্টতা' : 'Conceptual Clarity, No Rote Learning' ?></h4>
                                <p class="small text-muted mb-0">
                                    <?= $locale === 'bn' ? 'প্রতিটি সূত্রের জ্যামিতিক ও বীজগাণিতিক প্রমাণ ক্লাসে সরাসরি উপস্থাপন করা হয় যাতে ছাত্রছাত্রীরা সূত্রের পেছনের যুক্তি বুঝতে পারে।' : 'Formulas are derived from core principles rather than memorized blindly, giving students deep intuition.' ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <h4 class="h6 fw-bold mb-2">2. <?= $locale === 'bn' ? 'টাইপভিত্তিক সমস্যা সমাধান' : 'Type-Wise Problem Classification' ?></h4>
                                <p class="small text-muted mb-0">
                                    <?= $locale === 'bn' ? 'প্রতিটি অধ্যায়ের গাণিতিক সমস্যাগুলোকে ৩-৪টি টাইপে ভাগ করে শেখানো হয়, যাতে পরীক্ষায় প্রশ্ন ঘুরিয়ে আসলেও চেনা লাগে।' : 'Problems are categorized into clear archetype patterns so students recognize exam questions instantly.' ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <h4 class="h6 fw-bold mb-2">3. <?= $locale === 'bn' ? 'টাইম ম্যানেজমেন্ট ও শর্টকাট' : 'Exam Speed & Time Management' ?></h4>
                                <p class="small text-muted mb-0">
                                    <?= $locale === 'bn' ? 'বোর্ড ও ভর্তি পরীক্ষার ক্ষেত্রে সময় বাঁচানোর বিশেষ শর্টকাট টেকনিক ও ক্যালকুলেটরের নিখুঁত ব্যবহার প্রশিক্ষণ।' : 'Students master rapid calculator methods and mental-math shortcuts to finish exams well before time.' ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <h4 class="h6 fw-bold mb-2">4. <?= $locale === 'bn' ? 'সাপ্তাহিক মূল্যায়ন ও ফিডব্যাক' : 'Weekly Assessment & Parent Feedback' ?></h4>
                                <p class="small text-muted mb-0">
                                    <?= $locale === 'bn' ? 'প্রতি সপ্তাহের পরীক্ষা শেষে ভুল হওয়া অংকগুলো আবার সমাধান করে দেওয়া হয় এবং ফলাফল এসএমএস পাঠানো হয়।' : 'Every weekly test is reviewed thoroughly to resolve doubts, and progress updates reach parents regularly.' ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Courses Guided by Al Amin Sir -->
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <h2 class="h4 fw-bold text-primary mb-3">
                        <?= $locale === 'bn' ? 'স্যারের তত্ত্বাবধানে পরিচালিত কোর্সসমূহ' : 'Courses Mentored by Al Amin Sir' ?>
                    </h2>
                    <div class="row g-3">
                        <?php foreach ($courses as $c): 
                            $cTitle = content($c, 'title');
                            $cLevel = $c['class_level'];
                            $cFee = content($c, 'fee_display');
                        ?>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100 d-flex flex-column">
                                <span class="badge-class mb-2 align-self-start"><?= e($cLevel) ?></span>
                                <h3 class="h6 fw-bold mb-1"><?= e($cTitle) ?></h3>
                                <p class="text-primary fw-semibold small mb-2"><?= e($cFee) ?></p>
                                <a href="<?= e(base_url('course-details.php?slug=' . urlencode($c['slug']))) ?>" class="btn btn-outline-custom btn-sm mt-auto">
                                    <?= e(__('btn_view_details')) ?>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Right Profile Info Column -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top text-center" style="top: 90px;">
                    <div class="teacher-avatar mb-3 mx-auto" style="width: 130px; height: 130px; font-size: 2.8rem;">
                        ∑
                    </div>
                    <h3 class="h5 fw-bold mb-1"><?= e($name) ?></h3>
                    <p class="text-primary fw-semibold mb-3"><?= e($designation) ?></p>

                    <ul class="list-unstyled text-start border-top pt-3 mb-4 small">
                        <li class="py-2 border-bottom d-flex justify-content-between">
                            <span class="text-muted"><?= $locale === 'bn' ? 'শিক্ষাগত যোগ্যতা' : 'Qualifications' ?>:</span>
                            <span class="fw-semibold"><?= e($teacher['qualification'] ?? 'B.Sc (Hons), M.Sc') ?></span>
                        </li>
                        <li class="py-2 border-bottom d-flex justify-content-between">
                            <span class="text-muted"><?= $locale === 'bn' ? 'অভিজ্ঞতা' : 'Experience' ?>:</span>
                            <span class="fw-semibold">10+ <?= $locale === 'bn' ? 'বছর' : 'Years' ?></span>
                        </li>
                        <li class="py-2 border-bottom d-flex justify-content-between">
                            <span class="text-muted"><?= $locale === 'bn' ? 'স্পেশালাইজেশন' : 'Specialization' ?>:</span>
                            <span class="fw-semibold"><?= e(__('mathematics')) ?></span>
                        </li>
                        <li class="py-2 d-flex justify-content-between">
                            <span class="text-muted"><?= $locale === 'bn' ? 'ক্যাম্পাস' : 'Campus' ?>:</span>
                            <span class="fw-semibold">Farmgate, Dhaka</span>
                        </li>
                    </ul>

                    <div class="d-grid gap-2">
                        <a href="<?= e(base_url('admission.php?demo=1')) ?>" class="btn btn-primary-custom">
                            <?= e(__('btn_free_demo')) ?>
                        </a>
                        <a href="<?= e(phone_link($config['phone_primary'])) ?>" class="btn btn-outline-custom">
                            <?= e(__('btn_call')) ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
