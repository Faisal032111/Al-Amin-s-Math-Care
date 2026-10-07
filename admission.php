<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/i18n.php';
require_once __DIR__ . '/includes/helpers.php';

$isDemo = isset($_GET['demo']) && $_GET['demo'] == '1';
$pageTitle = page_title($isDemo ? __('page_demo_title') : __('page_admission_title'));

$selectedCourseName = clean_input($_GET['course'] ?? '');
$selectedBatchName  = clean_input($_GET['batch'] ?? '');

$courses = [];
try {
    $pdo = db();
    $courses = $pdo->query('SELECT id, slug, title_en, title_bn, class_level FROM courses WHERE is_deleted = 0 AND is_active = 1 ORDER BY id ASC')->fetchAll();
} catch (Throwable $e) {
    // Fallback
}

$error = '';
$success = false;
$submittedData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. CSRF Verification (verify_csrf_token exits with 403 on failure)
    verify_csrf_token();

    // 2. Honeypot Anti-Spam
    if (!empty($_POST['website_hp'])) {
        // Silent bot rejection — fake success
        $success = true;
    }
    // 3. Rate Limiting Check
    elseif (!rate_limit_check('lead_submit', 8, 3600)) {
        $error = __('form_rate_limit');
    } else {
        $studentName   = clean_input($_POST['student_name'] ?? '');
        $guardianPhone = clean_input($_POST['guardian_phone'] ?? '');
        $whatsappNum   = clean_input($_POST['whatsapp_number'] ?? $guardianPhone);
        $classLevel    = clean_input($_POST['class_level'] ?? '');
        $courseId      = !empty($_POST['course_id']) ? (int) $_POST['course_id'] : null;
        $preferredDate = !empty($_POST['preferred_date']) ? clean_input($_POST['preferred_date']) : null;
        $message       = clean_input($_POST['message'] ?? '');
        $type          = clean_input($_POST['type'] ?? ($isDemo ? 'demo_request' : 'admission_enquiry'));

        if (empty($studentName) || empty($guardianPhone) || empty($classLevel)) {
            $error = __('form_error');
        } elseif (!is_valid_bd_phone($guardianPhone)) {
            $error = __('form_phone_error');
        } else {
            try {
                $pdo = db();
                $stmt = $pdo->prepare('
                    INSERT INTO leads 
                        (type, student_name, guardian_phone, whatsapp_number, class_level, course_id, preferred_date, message, ip_address, status)
                    VALUES 
                        (:type, :name, :phone, :wa, :class, :cid, :pdate, :msg, :ip, "new")
                ');
                $stmt->execute([
                    ':type'  => $type,
                    ':name'  => $studentName,
                    ':phone' => $guardianPhone,
                    ':wa'    => $whatsappNum ?: $guardianPhone,
                    ':class' => $classLevel,
                    ':cid'   => $courseId,
                    ':pdate' => $preferredDate,
                    ':msg'   => $message,
                    ':ip'    => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
                ]);
                $success = true;
                $submittedData = [
                    'name'  => $studentName,
                    'phone' => $guardianPhone,
                    'type'  => $type
                ];
            } catch (Throwable $e) {
                // Fallback to JSON save
                save_lead([
                    'type'            => $type,
                    'student_name'    => $studentName,
                    'guardian_phone'  => $guardianPhone,
                    'whatsapp_number' => $whatsappNum,
                    'class_level'     => $classLevel,
                    'course_id'       => $courseId,
                    'message'         => $message,
                ]);
                $success = true;
            }
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container text-center">
        <h1><?= e($isDemo ? __('page_demo_title') : __('page_admission_title')) ?></h1>
        <p class="text-muted mb-0 lead">
            <?= $isDemo 
                ? ($locale === 'bn' ? 'আল আমিন স্যারের গণিত ক্লাসের পদ্ধতি সরাসরি যাচাই করতে ফ্রি ডেমো বুক করুন' : 'Book a 100% free trial demo class at our Farmgate campus with Al Amin Sir')
                : ($locale === 'bn' ? 'ফার্মগেটে শুধুমাত্র গণিত স্পেশাল কেয়ারে ভর্তির প্রাথমিক আবেদন' : 'Apply for admission in our specialized mathematics batches at Farmgate, Dhaka') ?>
        </p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if ($success): ?>
                <!-- Submission Success Box -->
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 text-center bg-white">
                    <span class="fs-1">🎉</span>
                    <h2 class="h3 fw-bold text-success mt-2 mb-2">
                        <?= $locale === 'bn' ? 'আবেদন সফলভাবে গৃহীত হয়েছে!' : 'Application Successfully Received!' ?>
                    </h2>
                    <p class="text-muted lead mb-4">
                        <?= e($isDemo ? __('form_demo_success') : __('form_success')) ?>
                    </p>

                    <div class="p-3 bg-light rounded-3 mb-4 text-start small">
                        <strong>📍 <?= e(config_address()) ?></strong><br>
                        📞 <?= e(__('label_phone')) ?>: <?= e($config['phone_primary']) ?> | 💬 WhatsApp: <?= e($config['whatsapp']) ?>
                    </div>

                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <a href="<?= e(whatsapp_link($config['whatsapp'], "Assalamu Alaikum, I just submitted an admission application for " . ($submittedData['name'] ?? 'my child') . ".")) ?>" target="_blank" rel="noopener" class="btn btn-whatsapp btn-lg">
                            💬 <?= $locale === 'bn' ? 'হোয়াটসঅ্যাপে দ্রুত নিশ্চিত করুন' : 'Confirm Instantly on WhatsApp' ?>
                        </a>
                        <a href="<?= e(base_url('courses.php')) ?>" class="btn btn-outline-custom btn-lg">
                            <?= e(__('nav_courses')) ?>
                        </a>
                    </div>
                </div>

                <?php else: ?>
                <!-- Application Form -->
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2 border-bottom pb-3">
                        <div>
                            <span class="badge bg-primary-subtle text-primary fw-semibold small">
                                <?= $isDemo ? 'FREE TRIAL SESSION' : 'ADMISSION 2026–2027' ?>
                            </span>
                            <h2 class="h4 fw-bold mb-0 mt-1">
                                <?= $isDemo ? ($locale === 'bn' ? 'ফ্রি ডেমো ক্লাসের তথ্য পূরণ করুন' : 'Free Demo Registration') : ($locale === 'bn' ? 'ভর্তি ফরম পূরণ করুন' : 'Student Admission Form') ?>
                            </h2>
                        </div>
                        <div>
                            <?php if ($isDemo): ?>
                                <a href="<?= e(base_url('admission.php')) ?>" class="small text-primary fw-semibold"><?= $locale === 'bn' ? 'ভর্তি ফরম চান?' : 'Regular Admission Form' ?> →</a>
                            <?php else: ?>
                                <a href="<?= e(base_url('admission.php?demo=1')) ?>" class="small text-accent fw-semibold">🎯 <?= $locale === 'bn' ? 'ফ্রি ডেমো ক্লাস বুক করুন' : 'Book Free Demo' ?> →</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($error)): ?>
                    <div class="alert alert-danger rounded-3 py-2 px-3 small mb-4">
                        ⚠️ <?= e($error) ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= e(base_url('admission.php' . ($isDemo ? '?demo=1' : ''))) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="type" value="<?= $isDemo ? 'demo_request' : 'admission_enquiry' ?>">

                        <!-- Honeypot anti-spam field -->
                        <div style="display:none;" aria-hidden="true">
                            <input type="text" name="website_hp" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="student_name" class="form-label fw-semibold"><?= e(__('form_student_name')) ?> *</label>
                                <input type="text" class="form-control form-control-lg" id="student_name" name="student_name" 
                                       value="<?= e($_POST['student_name'] ?? '') ?>" placeholder="e.g. Tanvir Ahmed" required>
                            </div>

                            <div class="col-md-6">
                                <label for="class_level" class="form-label fw-semibold"><?= e(__('form_class_level')) ?> *</label>
                                <select class="form-select form-select-lg" id="class_level" name="class_level" required>
                                    <option value=""><?= $locale === 'bn' ? 'শ্রেণি নির্বাচন করুন' : 'Select Class Level' ?></option>
                                    <option value="Class 6-8" <?= ($_POST['class_level'] ?? '') === 'Class 6-8' ? 'selected' : '' ?>>Class 6–8 Foundation</option>
                                    <option value="Class 9-10 (SSC)" <?= ($_POST['class_level'] ?? '') === 'Class 9-10 (SSC)' ? 'selected' : '' ?>>Class 9–10 (SSC)</option>
                                    <option value="HSC 1st/2nd" <?= ($_POST['class_level'] ?? '') === 'HSC 1st/2nd' ? 'selected' : '' ?>>HSC (1st / 2nd Year)</option>
                                    <option value="Admission" <?= ($_POST['class_level'] ?? '') === 'Admission' ? 'selected' : '' ?>>Engineering & Varsity Admission</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="guardian_phone" class="form-label fw-semibold"><?= e(__('form_guardian_phone')) ?> *</label>
                                <input type="tel" class="form-control form-control-lg" id="guardian_phone" name="guardian_phone" 
                                       value="<?= e($_POST['guardian_phone'] ?? '') ?>" placeholder="01XXXXXXXXX" pattern="01[3-9]\d{8}" required>
                                <small class="text-muted"><?= $locale === 'bn' ? '১১ ডিজিটের সক্রিয় বাংলাদেশি মোবাইল নম্বর' : '11-digit active BD mobile number' ?></small>
                            </div>

                            <div class="col-md-6">
                                <label for="whatsapp_number" class="form-label fw-semibold"><?= e(__('form_whatsapp')) ?> (<?= $locale === 'bn' ? 'ঐচ্ছিক' : 'Optional' ?>)</label>
                                <input type="tel" class="form-control form-control-lg" id="whatsapp_number" name="whatsapp_number" 
                                       value="<?= e($_POST['whatsapp_number'] ?? '') ?>" placeholder="01XXXXXXXXX">
                            </div>

                            <div class="col-12">
                                <label for="course_id" class="form-label fw-semibold"><?= e(__('form_course')) ?></label>
                                <select class="form-select form-select-lg" id="course_id" name="course_id">
                                    <option value=""><?= $locale === 'bn' ? 'কোর্স নির্বাচন করুন (ঐচ্ছিক)' : 'Select Preferred Course (Optional)' ?></option>
                                    <?php foreach ($courses as $c): 
                                        $cTitle = content($c, 'title');
                                        $selected = (!empty($selectedCourseName) && str_contains(strtolower($cTitle), strtolower($selectedCourseName)))
                                                 || ((int)($_POST['course_id'] ?? 0) === (int)$c['id']);
                                    ?>
                                    <option value="<?= $c['id'] ?>" <?= $selected ? 'selected' : '' ?>>
                                        <?= e($cTitle) ?> (<?= e($c['class_level']) ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <?php if ($isDemo): ?>
                            <div class="col-12">
                                <label for="preferred_date" class="form-label fw-semibold"><?= e(__('form_preferred_date')) ?> *</label>
                                <input type="date" class="form-control form-control-lg" id="preferred_date" name="preferred_date" 
                                       value="<?= e($_POST['preferred_date'] ?? date('Y-m-d', strtotime('+2 days'))) ?>" min="<?= date('Y-m-d') ?>" required>
                                <small class="text-muted"><?= $locale === 'bn' ? 'ফার্মগেট ক্যাম্পাসে যে তারিখে ডেমো ক্লাস করতে চান' : 'Preferred date to visit our Farmgate campus' ?></small>
                            </div>
                            <?php endif; ?>

                            <div class="col-12">
                                <label for="message" class="form-label fw-semibold"><?= e(__('form_message')) ?></label>
                                <textarea class="form-control" id="message" name="message" rows="3" 
                                          placeholder="<?= $locale === 'bn' ? 'গণিতে কোনো নির্দিষ্ট অধ্যায়ে দুর্বলতা বা বিশেষ জিজ্ঞাসা থাকলে লিখুন...' : 'Any specific math weaknesses or questions...' ?>"><?= e($_POST['message'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary-custom btn-lg w-100 fw-bold">
                                <?= $isDemo ? ('🎯 ' . __('btn_free_demo')) : ('📝 ' . __('btn_submit')) ?>
                            </button>
                            <p class="text-center text-muted small mt-2 mb-0">
                                🔒 <?= $locale === 'bn' ? 'আপনার তথ্য সম্পূর্ণ সুরক্ষিত থাকবে এবং শুধুমাত্র কোচিং সংক্রান্ত যোগাযোগের জন্য ব্যবহৃত হবে।' : 'Your data is strictly confidential and used solely for admission counseling.' ?>
                            </p>
                        </div>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
