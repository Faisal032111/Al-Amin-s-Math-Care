<?php

declare(strict_types=1);
?>
<header class="site-header sticky-top">
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="<?= e(base_url('index.php')) ?>">
                <span class="brand-mark">∑</span>
                <span class="brand-text">
                    <strong>Al Amin's</strong>
                    <small>Math Care</small>
                </span>
            </a>

            <div class="d-flex align-items-center gap-2 order-lg-3 ms-auto ms-lg-0">
                <?php require __DIR__ . '/lang-toggle.php'; ?>
                <a class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 rounded-pill px-2 py-1" href="<?= e(base_url('portal/login.php')) ?>" title="<?= $locale === 'bn' ? 'স্টুডেন্ট পোর্টাল' : 'Student Portal' ?>">
                    <span>🎓</span>
                    <span class="small fw-semibold d-none d-sm-inline"><?= $locale === 'bn' ? 'পোর্টাল' : 'Portal' ?></span>
                </a>
                <a class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 rounded-pill px-2 py-1" href="<?= e(base_url('admin/login.php')) ?>" title="<?= $locale === 'bn' ? 'অ্যাডমিন পোর্টাল' : 'Admin Portal' ?>">
                    <span>🔐</span>
                    <span class="small fw-semibold d-none d-sm-inline"><?= $locale === 'bn' ? 'অ্যাডমিন' : 'Admin' ?></span>
                </a>
                <a class="btn btn-sm btn-cta d-none d-md-inline-flex" href="<?= e(phone_link($config['phone_primary'])) ?>">
                    <?= e(__('btn_call')) ?>
                </a>
                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>

            <div class="collapse navbar-collapse order-lg-2" id="mainNav">
                <ul class="navbar-nav mx-lg-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link <?= active_nav('index.php') ?>" href="<?= e(base_url('index.php')) ?>"><?= e(__('nav_home')) ?></a></li>
                    <li class="nav-item"><a class="nav-link <?= active_nav('about.php') ?>" href="<?= e(base_url('about.php')) ?>"><?= e(__('nav_about')) ?></a></li>
                    <li class="nav-item"><a class="nav-link <?= active_nav('courses.php') ?>" href="<?= e(base_url('courses.php')) ?>"><?= e(__('nav_courses')) ?></a></li>
                    <li class="nav-item"><a class="nav-link <?= active_nav('batches.php') ?>" href="<?= e(base_url('batches.php')) ?>"><?= e(__('nav_batches')) ?></a></li>
                    <li class="nav-item"><a class="nav-link <?= active_nav('teachers.php') ?>" href="<?= e(base_url('teachers.php')) ?>"><?= e(__('nav_teachers')) ?></a></li>
                    <li class="nav-item"><a class="nav-link <?= active_nav('results.php') ?>" href="<?= e(base_url('results.php')) ?>"><?= e(__('nav_results')) ?></a></li>
                    <li class="nav-item"><a class="nav-link <?= active_nav('notices.php') ?>" href="<?= e(base_url('notices.php')) ?>"><?= e(__('nav_notices')) ?></a></li>
                    <li class="nav-item"><a class="nav-link <?= active_nav('contact.php') ?>" href="<?= e(base_url('contact.php')) ?>"><?= e(__('nav_contact')) ?></a></li>
                    <li class="nav-item"><a class="nav-link nav-admission <?= active_nav('admission.php') ?>" href="<?= e(base_url('admission.php')) ?>"><?= e(__('nav_admission')) ?></a></li>
                </ul>
            </div>
        </div>
    </nav>
</header>
