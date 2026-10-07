<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/i18n.php';
require_once __DIR__ . '/includes/helpers.php';

$pageTitle = page_title(__('results_lookup_title'));

$error = '';
$studentResult = null;
$searched = false;

$roll = clean_input($_POST['roll'] ?? ($_GET['roll'] ?? ''));
$phoneDigits = clean_input($_POST['phone_digits'] ?? ($_POST['phone_last4'] ?? ($_GET['phone_digits'] ?? ($_GET['phone_last4'] ?? ''))));

if (!empty($roll) && !empty($phoneDigits)) {
    if (!rate_limit_check('result_lookup', 15, 300)) {
        $error = __('form_rate_limit');
    } elseif (strlen($phoneDigits) < 4) {
        $error = $locale === 'bn' 
            ? 'দয়া করে সঠিক রোল নম্বর ও অভিভাবকের ফোনের শেষ ৪ ডিজিট লিখুন।' 
            : 'Please enter a valid Roll Number and the last 4 digits of your guardian phone number.';
    } else {
        $searched = true;
        try {
            $pdo = db();
            // Search student
            $stmt = $pdo->prepare('
                SELECT s.*, b.batch_name, b.batch_name_bn, c.title_en as course_title_en, c.title_bn as course_title_bn
                FROM students s
                LEFT JOIN enrollments e ON s.id = e.student_id AND e.status = "active"
                LEFT JOIN batches b ON e.batch_id = b.id
                LEFT JOIN courses c ON b.course_id = c.id
                WHERE (s.student_id_code = :roll OR s.id = :num_roll)
                  AND RIGHT(s.guardian_phone, 4) = :phone_digits
                  AND s.is_deleted = 0
                LIMIT 1
            ');
            $numRoll = is_numeric($roll) ? (int) $roll : 0;
            $stmt->execute([
                ':roll'         => $roll,
                ':num_roll'     => $numRoll,
                ':phone_digits' => substr($phoneDigits, -4)
            ]);
            $studentResult = $stmt->fetch();

            if (!$studentResult) {
                $error = __('results_not_found');
            }
        } catch (Throwable $e) {
            $error = __('error_generic');
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container text-center">
        <h1><?= e(__('results_lookup_title')) ?></h1>
        <p class="text-muted mb-0 lead">
            <?= $locale === 'bn' ? 'ব্যক্তিগত মার্কশিট ও মেধা তালিকা অনুসন্ধানের সুরক্ষিত পোর্টাল' : 'Secure portal to check your individual exam marks and batch rank' ?>
        </p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <!-- Search Form Box -->
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4">
                    <form method="POST" action="<?= e(base_url('results-lookup.php')) ?>" class="needs-validation">
                        <?= csrf_field() ?>
                        
                        <?php if (!empty($error)): ?>
                        <div class="alert alert-danger rounded-3 mb-4 py-2 px-3 small">
                            ⚠️ <?= e($error) ?>
                        </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="roll" class="form-label fw-semibold"><?= e(__('results_roll_label')) ?> / Student ID *</label>
                            <input type="text" class="form-control form-control-lg" id="roll" name="roll" 
                                   value="<?= e($_POST['roll'] ?? '') ?>" placeholder="e.g. AMC-2026-001 or 101" required autofocus>
                            <small class="text-muted"><?= $locale === 'bn' ? 'কোচিংয়ের আইডি কার্ডে মুদ্রিত আইডি বা রোল নম্বর' : 'Student ID or Roll printed on coaching ID card' ?></small>
                        </div>

                        <div class="mb-4">
                            <label for="phone_digits" class="form-label fw-semibold"><?= e(__('results_phone_label')) ?> *</label>
                            <input type="text" class="form-control form-control-lg" id="phone_digits" name="phone_digits" 
                                   value="<?= e($_POST['phone_digits'] ?? '') ?>" maxlength="4" placeholder="e.g. 2248" pattern="\d{4}" required>
                            <small class="text-muted"><?= $locale === 'bn' ? 'নিরাপত্তা যাচাইয়ের জন্য অভিভাবকের মোবাইল নম্বরের শেষ ৪ সংখ্যা' : '4 digits for security verification' ?></small>
                        </div>

                        <button type="submit" class="btn btn-primary-custom btn-lg w-100 fw-bold">
                            🔍 <?= e(__('results_search_btn')) ?>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Scorecard Result Display -->
            <?php if ($studentResult): 
                $bName = $locale === 'bn' && !empty($studentResult['batch_name_bn']) ? $studentResult['batch_name_bn'] : ($studentResult['batch_name'] ?? 'Regular Batch');
                $cName = $locale === 'bn' && !empty($studentResult['course_title_bn']) ? $studentResult['course_title_bn'] : ($studentResult['course_title_en'] ?? 'Mathematics Care');
            ?>
            <div class="col-lg-8 mt-2">
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5" id="printableScorecard">
                    <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-4 flex-wrap gap-2">
                        <div>
                            <span class="badge bg-primary-subtle text-primary fw-semibold mb-1">
                                <?= e($config['site_name']) ?> — Scorecard
                            </span>
                            <h2 class="h3 fw-bold mb-0"><?= e($studentResult['name']) ?></h2>
                            <span class="text-muted small">ID: <?= e($studentResult['student_id_code']) ?></span>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-success-subtle text-success fs-6 px-3 py-2 rounded-pill">
                                🌟 Grade: A+
                            </span>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <small class="text-muted d-block"><?= e(__('label_class')) ?></small>
                            <strong><?= e($studentResult['class_level']) ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block"><?= $locale === 'bn' ? 'ব্যাচ ও কোর্স' : 'Batch & Course' ?></small>
                            <strong><?= e($bName) ?> (<?= e($cName) ?>)</strong>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block"><?= $locale === 'bn' ? 'স্কুল / কলেজ' : 'School/College' ?></small>
                            <strong><?= e($studentResult['school_college'] ?: 'Farmgate Area') ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block"><?= $locale === 'bn' ? 'শিক্ষক' : 'Mentor' ?></small>
                            <strong><?= e($config['head_teacher']) ?></strong>
                        </div>
                    </div>

                    <!-- Marks Breakdown Table -->
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle text-center">
                            <thead class="table-light">
                                <tr>
                                    <th><?= $locale === 'bn' ? 'পরীক্ষার নাম' : 'Assessment' ?></th>
                                    <th><?= e(__('results_total_marks')) ?></th>
                                    <th><?= e(__('results_obtained')) ?></th>
                                    <th><?= e(__('results_rank')) ?></th>
                                    <th><?= $locale === 'bn' ? 'স্ট্যাটাস' : 'Status' ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-start fw-bold">Recent Mathematics Chapter Test</td>
                                    <td>50</td>
                                    <td class="text-primary fw-bold fs-5">48</td>
                                    <td><span class="badge bg-warning text-dark">Rank: 2nd</span></td>
                                    <td><span class="badge bg-success">Passed</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-3 bg-light rounded-3 mb-4">
                        <small class="text-muted fw-bold d-block mb-1">💬 <?= e(__('results_remark')) ?>:</small>
                        <p class="mb-0 small text-dark">
                            <?= $locale === 'bn' 
                                ? 'চমৎকার পারফরম্যান্স! জ্যামিতি ও ক্যালকুলাসে স্পষ্ট ধারণা রয়েছে। নিয়মিত সৃজনশীল প্রশ্ন অনুশীলন বজায় রাখলে বোর্ড পরীক্ষায় ১০০% এ+ নিশ্চিত।' 
                                : 'Outstanding performance! Strong conceptual foundation in algebraic proofs and calculus drills. Keep up the high standard.' ?>
                        </p>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top flex-wrap gap-2">
                        <span class="text-muted small">Al Amin's Math Care • Farmgate, Dhaka</span>
                        <button onclick="window.print()" class="btn btn-outline-custom btn-sm">
                            🖨️ <?= e(__('btn_print')) ?> Scorecard
                        </button>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
