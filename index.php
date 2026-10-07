<?php

/**
 * index.php — Home Page
 * Al Amin's Math Care | alaminmathcare.com
 *
 * Public landing page showcasing courses, teacher profile,
 * why choose us, results highlight, demo class CTA, and contact info.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/i18n.php';
require_once __DIR__ . '/includes/helpers.php';

$pageTitle = page_title();
$config    = app_config();

// Fetch active featured courses from DB with fallback
$featuredCourses = [];
try {
    $pdo = db();
    $featuredCourses = $pdo->query('SELECT * FROM courses WHERE is_deleted = 0 AND is_active = 1 ORDER BY id ASC LIMIT 6')->fetchAll();
} catch (Throwable $e) {
    $featuredCourses = courses_data();
}

// Fetch active notices with fallback
$recentNotices = [];
try {
    $pdo = db();
    $recentNotices = $pdo->query('SELECT * FROM notices WHERE is_published = 1 AND is_deleted = 0 ORDER BY id DESC LIMIT 3')->fetchAll();
} catch (Throwable $e) {
    $recentNotices = [];
}

require __DIR__ . '/includes/header.php';
?>

<!-- ── Hero Section ───────────────────────────────────────────────────────── -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <div class="hero-content">
                    <span class="hero-badge">
                        ✨ <?= e(__('hero_badge')) ?>
                    </span>
                    <h1 class="hero-title">
                        <?= e(__('hero_headline')) ?>
                    </h1>
                    <p class="hero-subtitle">
                        <?= e(__('hero_subheadline')) ?>
                    </p>
                    <div class="hero-actions">
                        <a href="<?= e(base_url('demo.php')) ?>" class="btn btn-cta btn-lg shadow-sm">
                            🎯 <?= e(__('btn_free_demo')) ?>
                        </a>
                        <a href="<?= e(base_url('admission.php')) ?>" class="btn btn-outline-light btn-lg">
                            📝 <?= e(__('btn_admission')) ?>
                        </a>
                        <a href="<?= e(whatsapp_link($config['whatsapp'], __('whatsapp_prefill', ['course' => __('mathematics')]))) ?>" target="_blank" rel="noopener" class="btn btn-whatsapp btn-lg d-none d-sm-inline-flex align-items-center gap-2">
                            💬 WhatsApp
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="hero-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="mb-0 text-white">📌 <?= $locale === 'bn' ? 'সংক্ষিপ্ত তথ্য' : 'Quick Overview' ?></h3>
                        <span class="badge bg-warning text-dark fw-bold"><?= e(__('hero_seats_left')) ?></span>
                    </div>
                    <div class="hero-info-item">
                        <div class="icon">📍</div>
                        <div>
                            <strong><?= $locale === 'bn' ? 'ক্যাম্পাস অবস্থান' : 'Campus Location' ?></strong>
                            <p class="mb-0 text-light opacity-75 small"><?= e(config_address()) ?></p>
                        </div>
                    </div>
                    <div class="hero-info-item">
                        <div class="icon">📐</div>
                        <div>
                            <strong><?= $locale === 'bn' ? 'বিশেষায়িত বিষয়' : 'Specialized Subject' ?></strong>
                            <p class="mb-0 text-light opacity-75 small"><?= e(__('mathematics')) ?> (Class 6 – 10, SSC, HSC & Admission)</p>
                        </div>
                    </div>
                    <div class="hero-info-item">
                        <div class="icon">👨‍🏫</div>
                        <div>
                            <strong><?= $locale === 'bn' ? 'সরাসরি পাঠদান' : 'Direct Mentorship' ?></strong>
                            <p class="mb-0 text-light opacity-75 small"><?= e($config['head_teacher']) ?></p>
                        </div>
                    </div>
                    <div class="hero-info-item">
                        <div class="icon">📞</div>
                        <div>
                            <strong><?= $locale === 'bn' ? 'সরাসরি কল' : 'Direct Call' ?></strong>
                            <p class="mb-0 text-light opacity-75 small">
                                <a href="<?= e(phone_link($config['phone_primary'])) ?>" class="text-warning text-decoration-none fw-bold"><?= e($config['phone_primary']) ?></a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── Quick Statistics / Highlights ──────────────────────────────────────── -->
<section class="py-4 bg-white border-bottom shadow-sm">
    <div class="container">
        <div class="row g-3 text-center">
            <div class="col-6 col-md-3">
                <div class="p-3">
                    <div class="display-6 fw-bold text-primary">100%</div>
                    <div class="text-muted small fw-semibold"><?= $locale === 'bn' ? 'গণিত কেন্দ্রিক যত্ন' : 'Math Focused Care' ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3">
                    <div class="display-6 fw-bold text-primary">500+</div>
                    <div class="text-muted small fw-semibold"><?= $locale === 'bn' ? 'A+ প্রাপ্ত শিক্ষার্থী' : 'A+ Achievers' ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3">
                    <div class="display-6 fw-bold text-primary">10+</div>
                    <div class="text-muted small fw-semibold"><?= $locale === 'bn' ? 'বছরের শিক্ষকতা অভিজ্ঞতা' : 'Years Experience' ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3">
                    <div class="display-6 fw-bold text-primary">1:1</div>
                    <div class="text-muted small fw-semibold"><?= $locale === 'bn' ? 'ব্যক্তিগত ডাউট সলভিং' : 'Personal Care & Doubt Solving' ?></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── Why Choose Us ──────────────────────────────────────────────────────── -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2><?= e(__('section_why_title')) ?></h2>
            <p><?= $locale === 'bn' ? 'গণিতে সর্বোচ্চ দক্ষতা ও আত্মবিশ্বাস গড়ার পূর্ণাঙ্গ পরিবেশ' : 'Comprehensive environment for achieving math excellence and confidence' ?></p>
        </div>
        <div class="row g-4">
            <?php for ($i = 1; $i <= 6; $i++): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm p-4 rounded-4" style="transition: transform 0.2s ease;">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.25rem;">
                            <?= ['🎯', '📊', '👥', '📍', '🏆', '⏰'][$i - 1] ?>
                        </div>
                        <h5 class="fw-bold mb-0 text-primary"><?= e(__('why_' . $i . '_title')) ?></h5>
                    </div>
                    <p class="text-muted mb-0 small"><?= e(__('why_' . $i . '_text')) ?></p>
                </div>
            </div>
            <?php endfor; ?>
        </div>
    </div>
</section>

<!-- ── Featured Courses ───────────────────────────────────────────────────── -->
<section class="section bg-light border-top border-bottom">
    <div class="container">
        <div class="section-header">
            <h2><?= e(__('section_courses_title')) ?></h2>
            <p><?= e(__('section_courses_sub')) ?></p>
        </div>
        <div class="row g-4">
            <?php if (!empty($featuredCourses)): ?>
                <?php foreach ($featuredCourses as $c): ?>
                <?php
                    $title = $locale === 'bn' ? ($c['title_bn'] ?? $c['title_en'] ?? $c['subject'] ?? 'Math Course') : ($c['title_en'] ?? $c['subject'] ?? 'Math Course');
                    $classLevel = $c['class_level'] ?? $c['class'] ?? 'All';
                    $slug = $c['slug'] ?? 'course';
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-body p-4 d-flex flex-direction-column flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-1 rounded-pill">
                                        <?= e($classLevel) ?>
                                    </span>
                                    <span class="badge bg-success-subtle text-success fw-bold px-2 py-1 rounded-pill small">
                                        <?= e(__('course_status_open')) ?>
                                    </span>
                                </div>
                                <h5 class="card-title fw-bold text-dark mb-2 mt-2"><?= e($title) ?></h5>
                                <p class="card-text text-muted small mb-3">
                                    <?= e($locale === 'bn' ? ($c['short_description_bn'] ?? 'নিয়মিত ক্লাস, অধ্যায়ভিত্তিক পরীক্ষা ও স্পেশাল প্র্যাকটিস শিট।') : ($c['short_description_en'] ?? 'Regular classes, chapter tests, and curated problem sheets.')) ?>
                                </p>
                            </div>
                            <div class="pt-3 border-top mt-2 d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted d-block"><?= e(__('course_teacher')) ?></small>
                                    <strong class="text-primary small"><?= e($c['teacher'] ?? $config['head_teacher']) ?></strong>
                                </div>
                                <a href="<?= e(base_url('course-details.php?slug=' . urlencode((string)$slug))) ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    <?= e(__('btn_view_details')) ?> →
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="text-center mt-5">
            <a href="<?= e(base_url('courses.php')) ?>" class="btn btn-primary-custom px-4 py-2">
                <?= e(__('btn_view_all')) ?> <?= e(__('nav_courses')) ?> &rarr;
            </a>
        </div>
    </div>
</section>

<!-- ── Teacher Spotlight ──────────────────────────────────────────────────── -->
<section class="section">
    <div class="container">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-lg-5 bg-white">
            <div class="row align-items-center g-4">
                <div class="col-lg-4 text-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary mx-auto d-flex align-items-center justify-content-center shadow-sm" style="width: 160px; height: 160px; font-size: 4rem;">
                        👨‍🏫
                    </div>
                    <h3 class="fw-bold mt-3 mb-1 text-primary"><?= e(__('teacher_name')) ?></h3>
                    <p class="text-muted fw-semibold small"><?= e(__('teacher_role')) ?></p>
                    <span class="badge bg-warning text-dark px-3 py-1 rounded-pill">10+ Years Dedicated Math Teaching</span>
                </div>
                <div class="col-lg-8">
                    <h4 class="fw-bold mb-3"><?= $locale === 'bn' ? 'গণিত ভীতি দূর করে নিশ্চিত সফলতা অর্জন' : 'Transforming Math Fear into Top Achievement' ?></h4>
                    <p class="text-muted lead fs-6 mb-4"><?= e(__('teacher_bio')) ?></p>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-success fw-bold">✓</span>
                                <span class="small fw-semibold"><?= $locale === 'bn' ? 'বেসিক থেকে অ্যাডভান্সড কনসেপ্ট ক্লিয়ারিং' : 'Foundational to Advanced Concepts' ?></span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-success fw-bold">✓</span>
                                <span class="small fw-semibold"><?= $locale === 'bn' ? 'বোর্ড ও অ্যাডমিশন স্ট্যান্ডার্ড সমাধান' : 'Board & Admission Problem Solving' ?></span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-success fw-bold">✓</span>
                                <span class="small fw-semibold"><?= $locale === 'bn' ? 'অধ্যায়ভিত্তিক স্পেশাল হ্যান্ডনোট ও শিট' : 'Chapterwise Handnotes & Worksheets' ?></span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-success fw-bold">✓</span>
                                <span class="small fw-semibold"><?= $locale === 'bn' ? 'নিয়মিত মডেল টেস্ট ও মূল্যায়ন' : 'Regular Model Tests & Evaluation' ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-top d-flex flex-wrap gap-3">
                        <a href="<?= e(base_url('teachers.php')) ?>" class="btn btn-outline-custom">
                            <?= e(__('btn_view_details')) ?>
                        </a>
                        <a href="<?= e(base_url('demo.php')) ?>" class="btn btn-cta">
                            <?= e(__('btn_free_demo')) ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── Free Demo CTA Banner ───────────────────────────────────────────────── -->
<section class="py-5 bg-primary text-white position-relative overflow-hidden">
    <div class="container text-center py-4">
        <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill mb-3">
            🎁 <?= $locale === 'bn' ? '১০০% ফ্রি ট্রায়াল ক্লাস' : '100% Free Trial Class' ?>
        </span>
        <h2 class="display-6 fw-bold mb-3"><?= $locale === 'bn' ? 'একটি ফ্রি ডেমো ক্লাসে অংশ নিয়ে নিজেই যাচাই করুন' : 'Attend a Free Demo Class & Experience the Difference' ?></h2>
        <p class="lead opacity-75 max-w-600 mx-auto mb-4" style="max-width: 650px;">
            <?= $locale === 'bn' ? 'কোনো অগ্রিম ফি ছাড়া ক্লাসে অংশ নিয়ে দেখুন আল আমিন স্যারের পাঠদান পদ্ধতি কতটা সহজ ও কার্যকরী।' : 'Join a free session with Al Amin Sir with zero upfront commitment.' ?>
        </p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="<?= e(base_url('demo.php')) ?>" class="btn btn-warning btn-lg fw-bold text-dark px-4 py-3 shadow">
                🎯 <?= e(__('btn_free_demo')) ?>
            </a>
            <a href="<?= e(base_url('admission.php')) ?>" class="btn btn-outline-light btn-lg px-4 py-3">
                📋 <?= e(__('btn_admission')) ?>
            </a>
        </div>
    </div>
</section>

<!-- ── Recent Notices & Results Lookup ────────────────────────────────────── -->
<section class="section bg-light border-top">
    <div class="container">
        <div class="row g-4">
            <!-- Notices -->
            <div class="col-lg-6">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="fw-bold mb-0 text-primary">📢 <?= e(__('section_notices_title')) ?></h4>
                        <a href="<?= e(base_url('notices.php')) ?>" class="text-primary small fw-semibold"><?= e(__('btn_view_all')) ?> →</a>
                    </div>
                    <?php if (!empty($recentNotices)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentNotices as $notice): ?>
                            <div class="list-group-item px-0 py-3 bg-transparent border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="mb-0 fw-bold"><?= e($notice['title_' . $locale] ?? $notice['title_en'] ?? $notice['title'] ?? 'Notice') ?></h6>
                                    <span class="badge bg-secondary-subtle text-secondary small"><?= format_date((string)($notice['published_at'] ?? 'now')) ?></span>
                                </div>
                                <p class="text-muted small mb-0"><?= e(mb_strimwidth((string)($notice['content_' . $locale] ?? $notice['content_en'] ?? ''), 0, 100, '...')) ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted small my-3"><?= e(__('notice_no_notices')) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Student Results Lookup Box -->
            <div class="col-lg-6">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4">
                    <h4 class="fw-bold mb-2 text-primary">🏆 <?= e(__('results_lookup_title')) ?></h4>
                    <p class="text-muted small mb-4"><?= $locale === 'bn' ? 'সরাসরি রোল নম্বর দিয়ে সাপ্তাহিক ও মডেল টেস্ট পরীক্ষার মার্কশিট চেক করুন।' : 'Check weekly exam and model test marks directly with Roll number.' ?></p>
                    
                    <form action="<?= e(base_url('results-lookup.php')) ?>" method="GET" class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label small fw-bold"><?= e(__('results_roll_label')) ?></label>
                            <input type="text" name="roll" class="form-control" placeholder="e.g. 1024" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-bold"><?= e(__('results_phone_label')) ?></label>
                            <input type="text" name="phone_last4" class="form-control" placeholder="e.g. 5678" maxlength="4" required>
                        </div>
                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-primary-custom w-100">
                                🔍 <?= e(__('results_search_btn')) ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── Campus Location & Contact ──────────────────────────────────────────── -->
<section class="section">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <span class="text-primary fw-bold small text-uppercase tracking-wider">📍 <?= e(__('section_location_title')) ?></span>
                <h2 class="fw-bold mt-2 mb-3"><?= e(__('section_contact_title')) ?></h2>
                <p class="text-muted mb-4"><?= e(__('section_location_sub')) ?></p>
                <div class="mb-3">
                    <strong><?= e(__('label_address')) ?>:</strong>
                    <p class="text-muted mb-0"><?= e(config_address()) ?></p>
                </div>
                <div class="mb-3">
                    <strong><?= e(__('label_phone')) ?>:</strong>
                    <p class="text-muted mb-0">
                        <a href="<?= e(phone_link($config['phone_primary'])) ?>" class="text-primary fw-bold"><?= e($config['phone_primary']) ?></a>, 
                        <a href="<?= e(phone_link($config['phone_secondary'])) ?>" class="text-primary fw-bold"><?= e($config['phone_secondary']) ?></a>
                    </p>
                </div>
                <div>
                    <a href="<?= e(base_url('contact.php')) ?>" class="btn btn-outline-custom">
                        <?= e(__('nav_contact')) ?> <?= $locale === 'bn' ? 'পেজে যান' : 'Page' ?> →
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden p-2 bg-white">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3651.8488344079817!2d90.3857504!3d23.7527663!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3755b8af4f21054f%3A0x959954a2f8b5f3a0!2sFarmgate%2C%20Dhaka!5e0!3m2!1sen!2sbd!4v1700000000000!5m2!1sen!2sbd" 
                        width="100%" 
                        height="320" 
                        style="border:0; border-radius: 12px;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>