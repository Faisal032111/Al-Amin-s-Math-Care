<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/i18n.php';
require_once __DIR__ . '/includes/helpers.php';

$pageTitle = page_title($locale === 'bn' ? 'স্টুডেন্ট আইডি ভেরিফিকেশন' : 'Student ID Card Verification');

$token = clean_input($_GET['token'] ?? ($_GET['id'] ?? ''));
$student = null;
$verified = false;

if (!empty($token)) {
    try {
        $pdo = db();
        $stmt = $pdo->prepare('
            SELECT s.*, b.batch_name, b.batch_name_bn, c.title_en as course_title_en, c.title_bn as course_title_bn
            FROM students s
            LEFT JOIN enrollments e ON s.id = e.student_id AND e.status = "active"
            LEFT JOIN batches b ON e.batch_id = b.id
            LEFT JOIN courses c ON b.course_id = c.id
            WHERE (s.qr_code_token = :tok OR s.student_id_code = :code)
              AND s.is_deleted = 0
            LIMIT 1
        ');
        $stmt->execute([':tok' => $token, ':code' => $token]);
        $student = $stmt->fetch();
        if ($student) {
            $verified = true;
        }
    } catch (Throwable $e) {
        // Fallback
    }
}

require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container text-center">
        <h1><?= $locale === 'bn' ? 'ডিজিটাল আইডি কার্ড ভ্যালিডেশন' : 'Digital Student ID Verification' ?></h1>
        <p class="text-muted mb-0 lead">
            <?= $locale === 'bn' ? 'আল আমিনস ম্যাথ কেয়ারের অফিসিয়াল শিক্ষার্থী যাচাইকরণ ব্যবস্থা' : 'Official credential and QR code authentication system' ?>
        </p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <?php if ($verified && $student): 
                    $batchName = $locale === 'bn' && !empty($student['batch_name_bn']) ? $student['batch_name_bn'] : ($student['batch_name'] ?? 'Regular Batch');
                    $courseName = $locale === 'bn' && !empty($student['course_title_bn']) ? $student['course_title_bn'] : ($student['course_title_en'] ?? 'Mathematics');
                ?>
                <!-- Verified ID Card Display -->
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="bg-success text-white p-4 text-center">
                        <span class="fs-1">🛡️</span>
                        <h2 class="h4 fw-bold mt-2 mb-1">
                            <?= $locale === 'bn' ? 'যাচাইকৃত শিক্ষার্থী' : 'Verified Authentic Student' ?>
                        </h2>
                        <small class="opacity-90">
                            <?= e($config['site_name']) ?> • Farmgate Campus
                        </small>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <div class="teacher-avatar mx-auto mb-2" style="width: 100px; height: 100px; font-size: 2.2rem;">
                                <?= strtoupper(substr($student['name'], 0, 1)) ?>
                            </div>
                            <h3 class="h4 fw-bold mb-1"><?= e($student['name']) ?></h3>
                            <span class="badge bg-primary px-3 py-2 rounded-pill fs-6">
                                ID: <?= e($student['student_id_code']) ?>
                            </span>
                        </div>

                        <ul class="list-unstyled mb-4 small border-top border-bottom py-3">
                            <li class="d-flex justify-content-between py-2">
                                <span class="text-muted"><?= e(__('label_class')) ?>:</span>
                                <span class="fw-bold"><?= e($student['class_level']) ?></span>
                            </li>
                            <li class="d-flex justify-content-between py-2">
                                <span class="text-muted"><?= $locale === 'bn' ? 'ব্যাচ ও কোর্স' : 'Batch' ?>:</span>
                                <span class="fw-bold"><?= e($batchName) ?></span>
                            </li>
                            <li class="d-flex justify-content-between py-2">
                                <span class="text-muted"><?= $locale === 'bn' ? 'ক্যাম্পাস' : 'Campus' ?>:</span>
                                <span class="fw-semibold">Farmgate Main Campus, Dhaka</span>
                            </li>
                            <li class="d-flex justify-content-between py-2">
                                <span class="text-muted"><?= $locale === 'bn' ? 'শিক্ষাবর্ষ' : 'Session' ?>:</span>
                                <span class="fw-semibold">2026–2027</span>
                            </li>
                            <li class="d-flex justify-content-between py-2">
                                <span class="text-muted"><?= $locale === 'bn' ? 'স্ট্যাটাস' : 'Status' ?>:</span>
                                <span class="badge bg-success-subtle text-success fw-bold"><?= strtoupper($student['status'] ?? 'ACTIVE') ?></span>
                            </li>
                        </ul>

                        <div class="p-3 bg-light rounded-3 text-center small text-muted">
                            🔒 <?= $locale === 'bn' ? 'এই ডিজিটাল সনদটি আল আমিনস ম্যাথ কেয়ারের কেন্দ্রীয় সার্ভার দ্বারা পরীক্ষিত ও সুরক্ষিত।' : 'This digital credential is authenticated in real-time by Al Amin\'s Math Care central database.' ?>
                        </div>
                    </div>
                </div>

                <?php else: ?>
                <!-- Search or Manual Token Box -->
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 text-center">
                    <?php if (!empty($token)): ?>
                    <div class="alert alert-warning mb-4">
                        ⚠️ <?= $locale === 'bn' ? 'প্রদত্ত আইডি বা কিউআর কোড টোকেনটি সঠিক নয়।' : 'Invalid or expired student ID / QR token.' ?>
                    </div>
                    <?php endif; ?>

                    <span class="fs-1">🔍</span>
                    <h2 class="h4 fw-bold mt-2 mb-2"><?= $locale === 'bn' ? 'আইডি কার্ড যাচাই করুন' : 'Verify Student ID' ?></h2>
                    <p class="text-muted small mb-4">
                        <?= $locale === 'bn' ? 'আইডি কার্ডের কিউআর কোড স্ক্যান করুন অথবা নিচে স্টুডেন্ট আইডি লিখুন।' : 'Scan the QR code on the ID card or enter the Student ID Code below.' ?>
                    </p>

                    <form method="GET" action="<?= e(base_url('verify-id.php')) ?>">
                        <div class="mb-3">
                            <input type="text" name="token" class="form-control form-control-lg text-center" 
                                   placeholder="e.g. AMC-2026-001" value="<?= e($token) ?>" required autofocus>
                        </div>
                        <button type="submit" class="btn btn-primary-custom btn-lg w-100 fw-bold">
                            <?= $locale === 'bn' ? 'যাচাই করুন' : 'Verify ID' ?>
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
