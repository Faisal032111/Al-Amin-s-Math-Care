<?php

declare(strict_types=1);
?>
<nav class="mobile-bottom-nav d-lg-none" aria-label="Mobile navigation">
    <a href="<?= e(base_url('index.php')) ?>" class="<?= active_nav('index.php') ?>">
        <span class="icon">⌂</span>
        <span><?= e(__('nav_home')) ?></span>
    </a>
    <a href="<?= e(base_url('courses.php')) ?>" class="<?= active_nav('courses.php') ?>">
        <span class="icon">📚</span>
        <span><?= e(__('nav_courses')) ?></span>
    </a>
    <a href="<?= e(base_url('notices.php')) ?>" class="<?= active_nav('notices.php') ?>">
        <span class="icon">📢</span>
        <span><?= e(__('nav_notices')) ?></span>
    </a>
    <a href="<?= e(phone_link($config['phone_primary'])) ?>">
        <span class="icon">📞</span>
        <span><?= e(__('btn_call')) ?></span>
    </a>
    <a href="<?= e(base_url('admission.php')) ?>" class="<?= active_nav('admission.php') ?>">
        <span class="icon">✎</span>
        <span><?= e(__('nav_admission')) ?></span>
    </a>
</nav>
