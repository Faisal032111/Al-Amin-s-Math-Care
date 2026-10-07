<?php

/**
 * demo.php — Free Demo Class Registration
 * Al Amin's Math Care | alaminmathcare.com
 *
 * Standalone page for free trial class booking.
 * Saves lead with type='demo_request'.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/i18n.php';
require_once __DIR__ . '/includes/helpers.php';

$pageTitle = page_title($locale === 'bn' ? 'ফ্রি ডেমো ক্লাস বুক করুন' : 'Book a Free Demo Class');

// Pre-fill from query string (e.g. coming from course page)
$selectedCourseId = (int) ($_GET['course_id'] ?? 0);
$selectedClass    = clean_input($_GET['class'] ?? '');

// Load courses for dropdown
$courses = [];
try {
    $pdo     = db();
    $courses = $pdo->query('SELECT id, slug, title_en, title_bn, class_level FROM courses WHERE is_deleted = 0 AND is_active = 1 ORDER BY id ASC')->fetchAll();
} catch (Throwable $e) {
    // Fallback: show empty list
}

// Form state
$error         = '';
$success       = false;
$submittedName = '';
$submittedPhone = '';

// ── POST Handler ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF check (exits 403 on failure)
    verify_csrf_token();

    // Honeypot anti-spam
    if (!empty($_POST['website_hp'])) {
        $success = true; // Silent fake success for bots
    } elseif (!rate_limit_check('demo_submit', 5, 3600)) {
        $error = __('form_rate_limit');
    } else {
        $studentName   = clean_input($_POST['student_name']   ?? '');
        $guardianPhone = clean_input($_POST['guardian_phone'] ?? '');
        $whatsappNum   = clean_input($_POST['whatsapp_number'] ?? '');
        $classLevel    = clean_input($_POST['class_level']    ?? '');
        $courseId      = !empty($_POST['course_id']) ? (int)$_POST['course_id'] : null;
        $preferredDate = !empty($_POST['preferred_date']) ? clean_input($_POST['preferred_date']) : null;
        $message       = clean_input($_POST['message'] ?? '');

        if (empty($studentName) || empty($guardianPhone) || empty($classLevel)) {
            $error = __('form_error');
        } elseif (!is_valid_bd_phone($guardianPhone)) {
            $error = __('form_phone_error');
        } else {
            $submittedName  = $studentName;
            $submittedPhone = $guardianPhone;

            try {
                $pdo  = db();
                $stmt = $pdo->prepare('
                    INSERT INTO leads
                        (type, student_name, guardian_phone, whatsapp_number, class_level, course_id, preferred_date, message, ip_address, status)
                    VALUES
                        ("demo_request", :name, :phone, :wa, :class, :cid, :pdate, :msg, :ip, "new")
                ');
                $stmt->execute([
                    ':name'  => $studentName,
                    ':phone' => $guardianPhone,
                    ':wa'    => $whatsappNum ?: $guardianPhone,
                    ':class' => $classLevel,
                    ':cid'   => $courseId,
                    ':pdate' => $preferredDate,
                    ':msg'   => $message,
                    ':ip'    => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                ]);
                $success = true;
            } catch (Throwable $e) {
                save_lead([
                    'type'            => 'demo_request',
                    'student_name'    => $studentName,
                    'guardian_phone'  => $guardianPhone,
                    'whatsapp_number' => $whatsappNum ?: $guardianPhone,
                    'class_level'     => $classLevel,
                    'course_id'       => $courseId,
                    'preferred_date'  => $preferredDate,
                    'message'         => $message,
                ]);
                $success = true;
            }
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<!-- ══════════════════════════════════════
     PAGE HERO — Demo Banner
══════════════════════════════════════ -->
<section class="demo-hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="badge demo-badge mb-3">
                    🎯 <?= $locale === 'bn' ? '১০০% ফ্রি — কোনো ফি নেই' : '100% FREE — Zero Cost Trial' ?>
                </span>
                <h1 class="demo-heading">
                    <?= $locale === 'bn'
                        ? 'আল আমিন স্যারের ক্লাস <span class="highlight">ফ্রিতেই</span> যাচাই করুন!'
                        : 'Experience Al Amin Sir\'s Teaching <span class="highlight">For Free!</span>' ?>
                </h1>
                <p class="demo-subtext">
                    <?= $locale === 'bn'
                        ? 'ফার্মগেটে একটি ডেমো ক্লাসে এসে নিজেই দেখুন — কেন প্রতি বছর শত শত শিক্ষার্থী গণিতে আমাদের উপর আস্থা রাখে।'
                        : 'Visit our Farmgate campus for one free trial session and discover why hundreds of students trust us with their mathematics every year.' ?>
                </p>

                <!-- Benefits List -->
                <ul class="demo-benefits">
                    <li>
                        <span class="benefit-icon">✅</span>
                        <span><?= $locale === 'bn' ? 'সম্পূর্ণ বিনামূল্যে — কোনো ভর্তি ফি নেই' : 'Completely free — no registration fee' ?></span>
                    </li>
                    <li>
                        <span class="benefit-icon">📐</span>
                        <span><?= $locale === 'bn' ? 'ধাপে ধাপে গণিতের সমস্যা সমাধান পদ্ধতি দেখুন' : 'Watch our unique step-by-step math problem approach' ?></span>
                    </li>
                    <li>
                        <span class="benefit-icon">👨‍🏫</span>
                        <span><?= $locale === 'bn' ? 'স্যারের সাথে সরাসরি প্রশ্ন করার সুযোগ' : 'Direct Q&A session with Al Amin Sir' ?></span>
                    </li>
                    <li>
                        <span class="benefit-icon">📍</span>
                        <span><?= $locale === 'bn' ? 'ফার্মগেট ক্যাম্পাস — হোলি ক্রস কলেজের বিপরীতে' : 'Farmgate Campus — Opp. Holy Cross College' ?></span>
                    </li>
                    <li>
                        <span class="benefit-icon">⏰</span>
                        <span><?= $locale === 'bn' ? 'আপনার সুবিধামতো দিন ও সময় বেছে নিন' : 'Choose your preferred day & time' ?></span>
                    </li>
                </ul>

                <!-- Social Proof Counter -->
                <div class="demo-proof">
                    <div class="proof-item">
                        <strong>500+</strong>
                        <span><?= $locale === 'bn' ? 'ডেমো ক্লাস সম্পন্ন' : 'Demo Classes Done' ?></span>
                    </div>
                    <div class="proof-divider"></div>
                    <div class="proof-item">
                        <strong>92%</strong>
                        <span><?= $locale === 'bn' ? 'ভর্তি রূপান্তর হার' : 'Enroll After Demo' ?></span>
                    </div>
                    <div class="proof-divider"></div>
                    <div class="proof-item">
                        <strong>10+</strong>
                        <span><?= $locale === 'bn' ? 'বছরের অভিজ্ঞতা' : 'Years Experience' ?></span>
                    </div>
                </div>
            </div>

            <!-- ── FORM COLUMN ── -->
            <div class="col-lg-6">
                <div class="demo-form-card">
                    <?php if ($success): ?>
                    <!-- ── SUCCESS STATE ── -->
                    <div class="demo-success">
                        <div class="success-icon">🎉</div>
                        <h2><?= $locale === 'bn' ? 'ডেমো বুকিং সফল হয়েছে!' : 'Demo Booked Successfully!' ?></h2>
                        <p>
                            <?= $locale === 'bn'
                                ? 'আমরা শীঘ্রই আপনার সাথে যোগাযোগ করে ডেমো ক্লাসের সময় নিশ্চিত করব। আপনার সন্তানের গণিত যাত্রার প্রথম পদক্ষেপ নেওয়ার জন্য ধন্যবাদ!'
                                : 'We\'ll contact you soon to confirm your demo class timing. Thank you for taking the first step in your child\'s math journey!' ?>
                        </p>

                        <div class="success-info">
                            <p>📍 <strong><?= e(config_address()) ?></strong></p>
                            <p>📞 <?= e($config['phone_primary']) ?> | 💬 <?= e($config['whatsapp']) ?></p>
                        </div>

                        <div class="success-actions">
                            <a href="<?= e(whatsapp_link($config['whatsapp'], 'Assalamu Alaikum! I just registered for a free demo class at Al Amin\'s Math Care. Please confirm my slot.')) ?>"
                               target="_blank" rel="noopener" class="btn btn-whatsapp btn-lg w-100 mb-3">
                                💬 <?= $locale === 'bn' ? 'হোয়াটসঅ্যাপে তাৎক্ষণিক নিশ্চিত করুন' : 'Confirm Instantly on WhatsApp' ?>
                            </a>
                            <a href="<?= e(base_url('courses.php')) ?>" class="btn btn-outline-custom w-100">
                                📚 <?= e(__('nav_courses')) ?>
                            </a>
                        </div>
                    </div>

                    <?php else: ?>
                    <!-- ── FORM STATE ── -->
                    <div class="demo-form-header">
                        <span class="form-badge">📅 <?= $locale === 'bn' ? 'ডেমো রেজিস্ট্রেশন ফর্ম' : 'Demo Registration Form' ?></span>
                        <h2><?= $locale === 'bn' ? 'আপনার স্লট বুক করুন' : 'Reserve Your Free Slot' ?></h2>
                        <p class="form-note"><?= $locale === 'bn' ? 'মাত্র ২ মিনিটে ফর্মটি পূরণ করুন' : 'Fill in 2 minutes — 100% Free' ?></p>
                    </div>

                    <?php if (!empty($error)): ?>
                    <div class="form-error-alert">
                        ⚠️ <?= e($error) ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= e(base_url('demo.php')) ?>" id="demoForm" novalidate>
                        <?= csrf_field() ?>
                        <!-- Honeypot -->
                        <div style="display:none;" aria-hidden="true">
                            <input type="text" name="website_hp" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="form-grid">
                            <!-- Student Name -->
                            <div class="form-group full-width">
                                <label for="student_name" class="form-label">
                                    👤 <?= e(__('form_student_name')) ?> <span class="required">*</span>
                                </label>
                                <input type="text"
                                       class="form-control form-control-lg"
                                       id="student_name"
                                       name="student_name"
                                       value="<?= e($_POST['student_name'] ?? '') ?>"
                                       placeholder="<?= $locale === 'bn' ? 'শিক্ষার্থীর পুরো নাম লিখুন' : 'Student\'s full name' ?>"
                                       required>
                            </div>

                            <!-- Class Level -->
                            <div class="form-group">
                                <label for="class_level" class="form-label">
                                    📚 <?= e(__('form_class_level')) ?> <span class="required">*</span>
                                </label>
                                <select class="form-select form-select-lg" id="class_level" name="class_level" required>
                                    <option value=""><?= $locale === 'bn' ? 'শ্রেণি নির্বাচন করুন' : 'Select Class' ?></option>
                                    <?php
                                    $classOptions = [
                                        'Class 6-8'         => 'Class 6–8 (Foundation)',
                                        'Class 9-10 (SSC)'  => 'Class 9–10 (SSC)',
                                        'HSC 1st/2nd'       => 'HSC (1st / 2nd Year)',
                                        'Admission'         => 'Engineering & Varsity Admission',
                                    ];
                                    foreach ($classOptions as $val => $label):
                                        $sel = (($_POST['class_level'] ?? $selectedClass) === $val) ? 'selected' : '';
                                    ?>
                                    <option value="<?= e($val) ?>" <?= $sel ?>><?= e($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Course -->
                            <div class="form-group">
                                <label for="course_id" class="form-label">
                                    🎯 <?= e(__('form_course')) ?>
                                    <span class="optional">(<?= $locale === 'bn' ? 'ঐচ্ছিক' : 'Optional' ?>)</span>
                                </label>
                                <select class="form-select form-select-lg" id="course_id" name="course_id">
                                    <option value=""><?= $locale === 'bn' ? 'কোর্স নির্বাচন করুন' : 'Select Course' ?></option>
                                    <?php foreach ($courses as $c):
                                        $cTitle = content($c, 'title');
                                        $sel = ((int)($_POST['course_id'] ?? $selectedCourseId) === (int)$c['id']) ? 'selected' : '';
                                    ?>
                                    <option value="<?= $c['id'] ?>" <?= $sel ?>>
                                        <?= e($cTitle) ?> (<?= e($c['class_level']) ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Guardian Phone -->
                            <div class="form-group">
                                <label for="guardian_phone" class="form-label">
                                    📞 <?= e(__('form_guardian_phone')) ?> <span class="required">*</span>
                                </label>
                                <input type="tel"
                                       class="form-control form-control-lg"
                                       id="guardian_phone"
                                       name="guardian_phone"
                                       value="<?= e($_POST['guardian_phone'] ?? '') ?>"
                                       placeholder="01XXXXXXXXX"
                                       pattern="01[3-9]\d{8}"
                                       required>
                                <small class="field-hint"><?= $locale === 'bn' ? 'অভিভাবকের বাংলাদেশি মোবাইল নম্বর' : 'Guardian\'s active BD mobile' ?></small>
                            </div>

                            <!-- WhatsApp -->
                            <div class="form-group">
                                <label for="whatsapp_number" class="form-label">
                                    💬 <?= e(__('form_whatsapp')) ?>
                                    <span class="optional">(<?= $locale === 'bn' ? 'ঐচ্ছিক' : 'Optional' ?>)</span>
                                </label>
                                <input type="tel"
                                       class="form-control form-control-lg"
                                       id="whatsapp_number"
                                       name="whatsapp_number"
                                       value="<?= e($_POST['whatsapp_number'] ?? '') ?>"
                                       placeholder="01XXXXXXXXX">
                                <small class="field-hint"><?= $locale === 'bn' ? 'আলাদা হলে লিখুন, নইলে খালি রাখুন' : 'If different from phone above' ?></small>
                            </div>

                            <!-- Preferred Date -->
                            <div class="form-group full-width">
                                <label for="preferred_date" class="form-label">
                                    📅 <?= e(__('form_preferred_date')) ?> <span class="required">*</span>
                                </label>
                                <input type="date"
                                       class="form-control form-control-lg"
                                       id="preferred_date"
                                       name="preferred_date"
                                       value="<?= e($_POST['preferred_date'] ?? date('Y-m-d', strtotime('+2 days'))) ?>"
                                       min="<?= date('Y-m-d') ?>"
                                       required>
                                <small class="field-hint"><?= $locale === 'bn' ? 'ফার্মগেট ক্যাম্পাসে কোনো দিনে আসতে চান?' : 'Preferred date to visit Farmgate campus' ?></small>
                            </div>

                            <!-- Message -->
                            <div class="form-group full-width">
                                <label for="message" class="form-label">
                                    📝 <?= e(__('form_message')) ?>
                                    <span class="optional">(<?= $locale === 'bn' ? 'ঐচ্ছিক' : 'Optional' ?>)</span>
                                </label>
                                <textarea class="form-control"
                                          id="message"
                                          name="message"
                                          rows="3"
                                          placeholder="<?= $locale === 'bn' ? 'গণিতে কোনো নির্দিষ্ট দুর্বলতা বা বিশেষ প্রশ্ন থাকলে জানান...' : 'Any specific math topic you\'re struggling with? Let us know...' ?>"><?= e($_POST['message'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <!-- Submit -->
                        <button type="submit" class="btn-demo-submit" id="demoSubmitBtn">
                            <span class="submit-text">
                                🎯 <?= $locale === 'bn' ? 'ফ্রি ডেমো ক্লাস বুক করুন' : 'Book My Free Demo Class' ?>
                            </span>
                            <span class="submit-spinner" style="display:none;">
                                <span class="spinner-border spinner-border-sm me-2"></span>
                                <?= $locale === 'bn' ? 'পাঠানো হচ্ছে...' : 'Submitting...' ?>
                            </span>
                        </button>

                        <p class="form-privacy">
                            🔒 <?= $locale === 'bn' ? 'আপনার তথ্য সম্পূর্ণ সুরক্ষিত ও গোপনীয়।' : 'Your information is 100% private and secure.' ?>
                        </p>
                    </form>

                    <!-- Already Applied? -->
                    <div class="already-applied">
                        <span><?= $locale === 'bn' ? 'ভর্তি হতে চান?' : 'Ready to enroll directly?' ?></span>
                        <a href="<?= e(base_url('admission.php')) ?>">
                            <?= $locale === 'bn' ? 'ভর্তি ফরম পূরণ করুন →' : 'Regular Admission Form →' ?>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════
     HOW IT WORKS
══════════════════════════════════════ -->
<section class="section bg-light-subtle">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title"><?= $locale === 'bn' ? 'ডেমো ক্লাস কীভাবে হয়?' : 'How the Demo Class Works' ?></h2>
            <p class="section-subtitle text-muted"><?= $locale === 'bn' ? 'তিনটি সহজ ধাপে আপনার ডেমো সম্পন্ন করুন' : 'Three simple steps to your free demo' ?></p>
        </div>

        <div class="row g-4 justify-content-center">
            <?php
            $steps = $locale === 'bn' ? [
                ['01', '📝', 'ফর্ম পূরণ করুন', 'উপরের ফর্মে শিক্ষার্থীর নাম, শ্রেণি ও পছন্দের তারিখ দিন।'],
                ['02', '📞', 'কনফার্মেশন কল', 'আমরা ২৪ ঘণ্টার মধ্যে ফোন করে ডেমোর সময় নিশ্চিত করব।'],
                ['03', '🎓', 'ক্লাসে আসুন', 'ফার্মগেটে এসে ৪৫ মিনিটের লাইভ ক্লাস উপভোগ করুন।'],
            ] : [
                ['01', '📝', 'Fill the Form', 'Enter student name, class level, and your preferred demo date above.'],
                ['02', '📞', 'Confirmation Call', 'We\'ll call within 24 hours to confirm your exact demo timing.'],
                ['03', '🎓', 'Attend the Class', 'Visit Farmgate campus and enjoy a live 45-minute math session.'],
            ];
            foreach ($steps as $step): ?>
            <div class="col-md-4">
                <div class="how-step">
                    <div class="step-number"><?= $step[0] ?></div>
                    <div class="step-icon"><?= $step[1] ?></div>
                    <h3><?= e($step[2]) ?></h3>
                    <p><?= e($step[3]) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════
     WHAT TO EXPECT — Demo Agenda
══════════════════════════════════════ -->
<section class="section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <h2 class="section-title mb-4">
                    <?= $locale === 'bn' ? 'ডেমো ক্লাসে কী কী দেখবেন?' : 'What to Expect in the Demo' ?>
                </h2>
                <div class="agenda-list">
                    <?php
                    $agenda = $locale === 'bn' ? [
                        ['🧮', '০–১০ মিনিট', 'পরিচয় ও শিক্ষার্থীর দুর্বল অধ্যায় চিহ্নিত করা'],
                        ['📐', '১০–৩৫ মিনিট', 'আল আমিন স্যারের লাইভ গণিত পাঠদান — ধাপে ধাপে সমস্যা সমাধান'],
                        ['❓', '৩৫–৪৫ মিনিট', 'শিক্ষার্থী ও অভিভাবকদের সরাসরি প্রশ্নোত্তর পর্ব'],
                        ['📋', 'শেষে', 'কোর্স পরিচিতি, ব্যাচ সময়সূচি ও ফি বিস্তারিত জানার সুযোগ'],
                    ] : [
                        ['🧮', '0–10 min', 'Introduction & identifying student\'s weak areas in mathematics'],
                        ['📐', '10–35 min', 'Live teaching session by Al Amin Sir — step-by-step problem solving'],
                        ['❓', '35–45 min', 'Open Q&A — students and parents can ask anything'],
                        ['📋', 'End', 'Course overview, batch schedule & fee discussion'],
                    ];
                    foreach ($agenda as $item): ?>
                    <div class="agenda-item">
                        <div class="agenda-icon"><?= $item[0] ?></div>
                        <div class="agenda-content">
                            <strong><?= e($item[1]) ?></strong>
                            <span><?= e($item[2]) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="campus-info-card">
                    <h3>📍 <?= $locale === 'bn' ? 'ক্যাম্পাস লোকেশন' : 'Campus Location' ?></h3>
                    <p class="campus-address"><?= e($locale === 'bn' ? $config['address_bn'] : $config['address_en']) ?></p>

                    <div class="map-embed">
                        <iframe
                            src="<?= e($config['google_maps_embed']) ?>"
                            width="100%"
                            height="220"
                            style="border:0; border-radius: 12px;"
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="Al Amin's Math Care - Farmgate Location">
                        </iframe>
                    </div>

                    <div class="campus-contact">
                        <a href="tel:<?= e($config['phone_primary']) ?>" class="contact-chip">
                            📞 <?= e($config['phone_primary']) ?>
                        </a>
                        <a href="<?= e(whatsapp_link($config['whatsapp'], __('whatsapp_prefill', ['course' => 'Demo Class']))) ?>"
                           target="_blank" rel="noopener" class="contact-chip whatsapp">
                            💬 WhatsApp
                        </a>
                        <a href="<?= e($config['google_maps_directions']) ?>"
                           target="_blank" rel="noopener" class="contact-chip directions">
                            🗺️ <?= $locale === 'bn' ? 'দিকনির্দেশনা' : 'Get Directions' ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════
     CTA Bottom Strip
══════════════════════════════════════ -->
<section class="demo-cta-strip">
    <div class="container text-center">
        <h2><?= $locale === 'bn' ? 'আর দেরি কেন? আজই স্লট বুক করুন!' : 'Don\'t Wait — Book Your Free Slot Today!' ?></h2>
        <p><?= $locale === 'bn' ? 'সীমিত আসন। প্রতিদিন নতুন ব্যাচ শুরু হচ্ছে।' : 'Limited seats. New batches starting daily.' ?></p>
        <a href="#demoForm" class="btn btn-primary-custom btn-lg">
            🎯 <?= $locale === 'bn' ? 'এখনই ফ্রি ডেমো বুক করুন' : 'Book Free Demo Now' ?>
        </a>
    </div>
</section>

<!-- ══════════════════════════════════════
     PAGE-SPECIFIC STYLES
══════════════════════════════════════ -->
<style>
/* ── Demo Hero ───────────────────────── */
.demo-hero {
    background: linear-gradient(135deg, #0a1628 0%, #112240 60%, #0d3460 100%);
    padding: 5rem 0 4rem;
    color: #fff;
    position: relative;
    overflow: hidden;
}
.demo-hero::before {
    content: '';
    position: absolute;
    top: -40%;
    right: -10%;
    width: 600px;
    height: 600px;
    background: radial-gradient(circle, rgba(37,99,235,0.18) 0%, transparent 70%);
    pointer-events: none;
}
.demo-badge {
    background: rgba(37,99,235,0.25);
    color: #60a5fa;
    font-size: .85rem;
    font-weight: 700;
    padding: .45rem 1rem;
    border-radius: 50px;
    border: 1px solid rgba(96,165,250,0.3);
    letter-spacing: .5px;
}
.demo-heading {
    font-size: clamp(1.9rem, 4vw, 2.8rem);
    font-weight: 800;
    line-height: 1.2;
    margin-bottom: 1.2rem;
    color: #fff;
}
.demo-heading .highlight {
    color: #facc15;
    position: relative;
}
.demo-subtext {
    color: rgba(255,255,255,.75);
    font-size: 1.05rem;
    line-height: 1.7;
    margin-bottom: 2rem;
}
.demo-benefits {
    list-style: none;
    padding: 0;
    margin: 0 0 2rem;
    display: flex;
    flex-direction: column;
    gap: .7rem;
}
.demo-benefits li {
    display: flex;
    align-items: flex-start;
    gap: .75rem;
    color: rgba(255,255,255,.88);
    font-size: .95rem;
    line-height: 1.5;
}
.benefit-icon {
    flex-shrink: 0;
    width: 1.5rem;
    text-align: center;
}
.demo-proof {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 14px;
    padding: 1.2rem 1.5rem;
    margin-top: .5rem;
}
.proof-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}
.proof-item strong {
    font-size: 1.6rem;
    font-weight: 800;
    color: #facc15;
    line-height: 1;
}
.proof-item span {
    font-size: .75rem;
    color: rgba(255,255,255,.65);
    margin-top: .3rem;
    white-space: nowrap;
}
.proof-divider {
    width: 1px;
    height: 40px;
    background: rgba(255,255,255,.2);
}

/* ── Demo Form Card ─────────────────── */
.demo-form-card {
    background: #fff;
    border-radius: 20px;
    padding: 2.5rem 2rem;
    box-shadow: 0 24px 60px rgba(0,0,0,.35);
    border: 1px solid rgba(255,255,255,.1);
}
.demo-form-header {
    margin-bottom: 1.5rem;
}
.form-badge {
    display: inline-block;
    font-size: .78rem;
    font-weight: 700;
    color: #2563eb;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    padding: .3rem .8rem;
    border-radius: 50px;
    margin-bottom: .6rem;
}
.demo-form-header h2 {
    font-size: 1.5rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: .3rem;
}
.form-note {
    font-size: .85rem;
    color: #64748b;
    margin: 0;
}
.form-error-alert {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #dc2626;
    border-radius: 10px;
    padding: .75rem 1rem;
    font-size: .9rem;
    margin-bottom: 1rem;
}
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1.2rem;
}
.form-group { display: flex; flex-direction: column; }
.form-group.full-width { grid-column: 1 / -1; }
.form-label {
    font-weight: 600;
    font-size: .875rem;
    color: #374151;
    margin-bottom: .4rem;
}
.required { color: #dc2626; }
.optional {
    font-weight: 400;
    color: #9ca3af;
    font-size: .8rem;
}
.field-hint {
    font-size: .75rem;
    color: #9ca3af;
    margin-top: .25rem;
}
.btn-demo-submit {
    display: block;
    width: 100%;
    padding: 1rem;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #fff;
    border: none;
    border-radius: 12px;
    font-size: 1.05rem;
    font-weight: 700;
    cursor: pointer;
    transition: transform .2s, box-shadow .2s;
    box-shadow: 0 4px 15px rgba(37,99,235,.4);
    margin-bottom: .75rem;
}
.btn-demo-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(37,99,235,.5);
}
.form-privacy {
    text-align: center;
    font-size: .78rem;
    color: #9ca3af;
    margin: 0;
}
.already-applied {
    margin-top: 1.2rem;
    padding-top: 1.2rem;
    border-top: 1px solid #f1f5f9;
    text-align: center;
    font-size: .85rem;
    color: #64748b;
}
.already-applied a {
    color: #2563eb;
    font-weight: 600;
    text-decoration: none;
    margin-left: .4rem;
}
.already-applied a:hover { text-decoration: underline; }

/* ── Success State ──────────────────── */
.demo-success { text-align: center; padding: 1rem 0; }
.success-icon { font-size: 3.5rem; margin-bottom: .75rem; }
.demo-success h2 {
    font-size: 1.5rem;
    font-weight: 800;
    color: #16a34a;
    margin-bottom: .75rem;
}
.demo-success p {
    color: #4b5563;
    line-height: 1.7;
    margin-bottom: 1.25rem;
}
.success-info {
    background: #f8fafc;
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1.5rem;
    font-size: .875rem;
    color: #374151;
    text-align: left;
}
.success-info p { margin: .3rem 0; }
.success-actions { display: flex; flex-direction: column; gap: .75rem; }

/* ── How It Works ───────────────────── */
.how-step {
    background: #fff;
    border-radius: 16px;
    padding: 2rem;
    text-align: center;
    border: 1px solid #e2e8f0;
    transition: transform .25s, box-shadow .25s;
    height: 100%;
}
.how-step:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 30px rgba(0,0,0,.08);
}
.step-number {
    display: inline-block;
    width: 2.2rem;
    height: 2.2rem;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #fff;
    border-radius: 50%;
    font-size: .85rem;
    font-weight: 800;
    line-height: 2.2rem;
    margin-bottom: .75rem;
}
.step-icon { font-size: 2.2rem; margin-bottom: .75rem; }
.how-step h3 {
    font-size: 1.05rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: .5rem;
}
.how-step p {
    font-size: .875rem;
    color: #64748b;
    line-height: 1.6;
    margin: 0;
}

/* ── Agenda ─────────────────────────── */
.agenda-list { display: flex; flex-direction: column; gap: 1rem; }
.agenda-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem 1.2rem;
    background: #f8fafc;
    border-radius: 12px;
    border-left: 4px solid #2563eb;
    transition: background .2s;
}
.agenda-item:hover { background: #eff6ff; }
.agenda-icon { font-size: 1.5rem; flex-shrink: 0; }
.agenda-content { display: flex; flex-direction: column; gap: .15rem; }
.agenda-content strong {
    font-size: .85rem;
    font-weight: 700;
    color: #2563eb;
}
.agenda-content span {
    font-size: .9rem;
    color: #374151;
    line-height: 1.5;
}

/* ── Campus Card ────────────────────── */
.campus-info-card {
    background: #fff;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 8px 30px rgba(0,0,0,.08);
    border: 1px solid #e2e8f0;
}
.campus-info-card h3 {
    font-size: 1.15rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: .5rem;
}
.campus-address {
    font-size: .9rem;
    color: #4b5563;
    margin-bottom: 1rem;
    line-height: 1.6;
}
.map-embed { border-radius: 12px; overflow: hidden; margin-bottom: 1.25rem; }
.campus-contact { display: flex; flex-wrap: wrap; gap: .6rem; }
.contact-chip {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .45rem .9rem;
    border-radius: 50px;
    font-size: .85rem;
    font-weight: 600;
    text-decoration: none;
    border: 1.5px solid #e2e8f0;
    color: #374151;
    background: #f8fafc;
    transition: all .2s;
}
.contact-chip:hover {
    border-color: #2563eb;
    color: #2563eb;
    background: #eff6ff;
}
.contact-chip.whatsapp:hover {
    border-color: #16a34a;
    color: #16a34a;
    background: #f0fdf4;
}
.contact-chip.directions:hover {
    border-color: #ea580c;
    color: #ea580c;
    background: #fff7ed;
}

/* ── CTA Strip ──────────────────────── */
.demo-cta-strip {
    background: linear-gradient(135deg, #1e3a5f, #2563eb);
    padding: 3.5rem 0;
    color: #fff;
}
.demo-cta-strip h2 {
    font-size: 1.7rem;
    font-weight: 800;
    margin-bottom: .5rem;
    color: #fff;
}
.demo-cta-strip p {
    color: rgba(255,255,255,.75);
    margin-bottom: 1.5rem;
}

/* ── Responsive ─────────────────────── */
@media (max-width: 991px) {
    .demo-hero { padding: 3.5rem 0 3rem; }
    .demo-proof { flex-wrap: wrap; justify-content: center; }
}
@media (max-width: 575px) {
    .form-grid { grid-template-columns: 1fr; }
    .demo-form-card { padding: 1.75rem 1.25rem; }
    .demo-proof { gap: 1rem; }
    .proof-divider { display: none; }
}
</style>

<script>
// Smooth scroll to form on CTA click
document.querySelectorAll('a[href="#demoForm"]').forEach(a => {
    a.addEventListener('click', function(e) {
        e.preventDefault();
        const form = document.getElementById('demoForm');
        if (form) {
            form.scrollIntoView({ behavior: 'smooth', block: 'center' });
            const first = form.querySelector('input:not([type=hidden])');
            if (first) setTimeout(() => first.focus(), 600);
        }
    });
});

// Show spinner on submit
const form = document.getElementById('demoForm');
if (form) {
    form.addEventListener('submit', function() {
        const btn = document.getElementById('demoSubmitBtn');
        if (btn) {
            btn.querySelector('.submit-text').style.display = 'none';
            btn.querySelector('.submit-spinner').style.display = 'inline-flex';
            btn.disabled = true;
        }
    });
}

// Auto-fill WhatsApp from Guardian Phone
const phoneInput    = document.getElementById('guardian_phone');
const whatsappInput = document.getElementById('whatsapp_number');
if (phoneInput && whatsappInput) {
    phoneInput.addEventListener('input', function() {
        if (!whatsappInput.value) {
            whatsappInput.placeholder = this.value || '01XXXXXXXXX';
        }
    });
}
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
