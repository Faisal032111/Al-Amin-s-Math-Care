<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/i18n.php';
require_once __DIR__ . '/includes/helpers.php';

$pageTitle = page_title(__('page_contact_title'));
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <h1><?= e(__('page_contact_title')) ?></h1>
        <p class="text-muted mb-0 lead"><?= e(__('section_contact_sub')) ?></p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="contact-card h-100">
                    <h2 class="h5 fw-bold mb-3"><?= e(__('section_contact_title')) ?></h2>
                    <p class="mb-3"><strong><?= e(__('label_address')) ?>:</strong><br><?= e(config_address()) ?></p>
                    <p class="mb-3"><strong><?= e(__('label_landmark')) ?>:</strong> <?= e($config['landmark']) ?></p>
                    <p class="mb-3"><strong><?= e(__('label_phone')) ?>:</strong><br>
                        <a href="<?= e(phone_link($config['phone_primary'])) ?>"><?= e($config['phone_primary']) ?></a><br>
                        <a href="<?= e(phone_link($config['phone_secondary'])) ?>"><?= e($config['phone_secondary']) ?></a>
                    </p>
                    <p class="mb-3"><strong>WhatsApp:</strong> <a href="<?= e(whatsapp_link($config['whatsapp'])) ?>" target="_blank" rel="noopener"><?= e($config['whatsapp']) ?></a></p>
                    <p class="mb-3"><strong><?= e(__('label_hours')) ?>:</strong> <?= e($locale === 'bn' ? $config['opening_hours_bn'] : $config['opening_hours_en']) ?></p>
                    <p class="mb-3"><strong>Website:</strong> alaminmathcare.com</p>
                    <a class="btn btn-primary-custom" href="<?= e($config['google_maps_directions']) ?>" target="_blank" rel="noopener"><?= e(__('btn_get_directions')) ?></a>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="map-wrap mb-4">
                    <iframe src="<?= e($config['google_maps_embed']) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Al Amin's Math Care Location"></iframe>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-cta btn-lg" href="<?= e(phone_link($config['phone_primary'])) ?>"><?= e(__('btn_call')) ?></a>
                    <a class="btn btn-whatsapp btn-lg" href="<?= e(whatsapp_link($config['whatsapp'])) ?>" target="_blank" rel="noopener"><?= e(__('btn_whatsapp')) ?></a>
                    <a class="btn btn-primary-custom btn-lg" href="<?= e(base_url('admission.php')) ?>"><?= e(__('btn_admission')) ?></a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
