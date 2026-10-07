<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/i18n.php';
require_once __DIR__ . '/includes/helpers.php';

$pageTitle = page_title(__('page_about_title'));
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <h1><?= e(__('page_about_title')) ?></h1>
        <p class="text-muted mb-0 lead"><?= e(__('page_about_lead')) ?></p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-card">
                    <h2 class="h4 fw-bold text-primary mb-3"><?= e($config['site_name']) ?></h2>
                    <p><?= e(__('page_about_lead')) ?></p>
                    <p><?= e(__('page_about_mission')) ?></p>
                    <hr>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><strong><?= e(__('course_subject')) ?>:</strong> <?= e(__('mathematics')) ?> <?= e($locale === 'bn' ? 'শুধু' : 'only') ?></li>
                        <li class="mb-2"><strong><?= e(__('course_teacher')) ?>:</strong> <?= e($config['head_teacher']) ?></li>
                        <li class="mb-2"><strong><?= e(__('label_address')) ?>:</strong> <?= e(config_address()) ?></li>
                        <li><strong><?= e(__('label_phone')) ?>:</strong> <?= e($config['phone_primary']) ?>, <?= e($config['phone_secondary']) ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
