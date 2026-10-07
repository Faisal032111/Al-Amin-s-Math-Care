<?php

/**
 * admin/settings/index.php — Site Settings Manager
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';
require_role_or_abort(['super_admin', 'admin']); // Super-admin only

$adminPageTitle    = 'Site Settings — Admin Panel';
$adminPageHeading  = 'Site Settings';
$adminPageSubheading = 'Manage phone numbers, social links, admission status, and SMS kill-switch';
$activeModule      = 'settings';

$pdo = db();

// Load all settings into a key→value map
$rows = $pdo->query('SELECT setting_key, setting_value FROM settings')->fetchAll(PDO::FETCH_KEY_PAIR);

// Default keys with labels
$settingsMeta = [
    'site_phone_primary'    => ['label' => 'Primary Phone Number',          'type' => 'text',   'placeholder' => '+8801XXXXXXXXX'],
    'site_phone_secondary'  => ['label' => 'Secondary Phone Number',        'type' => 'text',   'placeholder' => '+8801XXXXXXXXX (optional)'],
    'whatsapp_number'       => ['label' => 'WhatsApp Number',               'type' => 'text',   'placeholder' => '+8801XXXXXXXXX'],
    'facebook_url'          => ['label' => 'Facebook Page URL',             'type' => 'url',    'placeholder' => 'https://facebook.com/alaminmathcare'],
    'youtube_url'           => ['label' => 'YouTube Channel URL',           'type' => 'url',    'placeholder' => 'https://youtube.com/@alaminmathcare'],
    'admission_open'        => ['label' => 'Admission Status',              'type' => 'select', 'options' => ['1' => 'Open (Currently Accepting)', '0' => 'Closed']],
    'sms_enabled'           => ['label' => 'SMS Gateway (Kill-Switch)',      'type' => 'select', 'options' => ['1' => 'Enabled', '0' => 'Disabled (Kill-Switch)']],
    'sms_daily_limit'       => ['label' => 'Daily SMS Limit',               'type' => 'number', 'placeholder' => '50'],
    'banner_text_en'        => ['label' => 'Homepage Banner Text (English)', 'type' => 'text',   'placeholder' => 'e.g. Admission Open for SSC 2026 Batch!'],
    'banner_text_bn'        => ['label' => 'Homepage Banner Text (Bangla)', 'type' => 'text',   'placeholder' => 'যেমন: এসএসসি ২০২৬ ব্যাচের ভর্তি চলছে!'],
    'site_address_en'       => ['label' => 'Campus Address (English)',       'type' => 'text',   'placeholder' => '123, Farmgate, Dhaka 1216'],
    'site_address_bn'       => ['label' => 'Campus Address (Bangla)',        'type' => 'text',   'placeholder' => 'ফার্মগেট, ঢাকা ১২১৬'],
    'google_maps_url'       => ['label' => 'Google Maps Embed URL',         'type' => 'url',    'placeholder' => 'https://maps.google.com/...'],
    'meta_description_en'   => ['label' => 'Homepage Meta Description (SEO)', 'type' => 'textarea', 'placeholder' => 'Best mathematics coaching in Dhaka...'],
];

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $pdo->beginTransaction();
    try {
        foreach ($settingsMeta as $key => $meta) {
            $value = clean_input($_POST[$key] ?? '');
            // Sanitise booleans
            if ($meta['type'] === 'select') {
                $value = in_array($value, array_keys($meta['options']), true) ? $value : array_key_first($meta['options']);
            }
            $upsert = $pdo->prepare(
                'INSERT INTO settings (setting_key, setting_value) VALUES (:k, :v)
                 ON DUPLICATE KEY UPDATE setting_value = :v2'
            );
            $upsert->execute([':k' => $key, ':v' => $value, ':v2' => $value]);
            $rows[$key] = $value;
        }
        $pdo->commit();
        set_flash('success', 'Settings saved successfully.');
        header('Location: ' . base_url('admin/settings/'));
        exit;
    } catch (Throwable $ex) {
        $pdo->rollBack();
        $errors[] = 'Failed to save settings: ' . $ex->getMessage();
    }
}

require_once __DIR__ . '/../includes/header.php';

// Group keys for layout
$groupedSettings = [
    '📞 Contact & Social Media' => [
        'site_phone_primary', 'site_phone_secondary', 'whatsapp_number',
        'facebook_url', 'youtube_url', 'google_maps_url',
    ],
    '📍 Campus & SEO' => [
        'site_address_en', 'site_address_bn', 'meta_description_en',
    ],
    '📢 Homepage & Admission' => [
        'banner_text_en', 'banner_text_bn', 'admission_open',
    ],
    '📱 SMS Gateway Controls' => [
        'sms_enabled', 'sms_daily_limit',
    ],
];
?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger py-2 small">
        <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<form method="POST" action="">
    <?= csrf_field() ?>

    <?php foreach ($groupedSettings as $groupLabel => $keys): ?>
        <div class="card card-custom p-4 mb-4">
            <h6 class="fw-bold text-secondary mb-3" style="font-size:.8rem; letter-spacing:.05em; text-transform:uppercase;">
                <?= e($groupLabel) ?>
            </h6>
            <div class="row g-3">
                <?php foreach ($keys as $key):
                    $meta    = $settingsMeta[$key];
                    $current = $rows[$key] ?? '';
                    $colClass = in_array($meta['type'], ['textarea']) ? 'col-12' : 'col-md-6';
                ?>
                    <div class="<?= $colClass ?>">
                        <label class="form-label small fw-bold text-secondary"><?= e($meta['label']) ?></label>

                        <?php if ($meta['type'] === 'select'): ?>
                            <select name="<?= e($key) ?>" class="form-select">
                                <?php foreach ($meta['options'] as $val => $optLabel): ?>
                                    <option value="<?= e($val) ?>" <?= $current == $val ? 'selected' : '' ?>><?= e($optLabel) ?></option>
                                <?php endforeach; ?>
                            </select>

                        <?php elseif ($meta['type'] === 'textarea'): ?>
                            <textarea name="<?= e($key) ?>" rows="2" class="form-control"
                                      placeholder="<?= e($meta['placeholder'] ?? '') ?>"><?= e($current) ?></textarea>

                        <?php else: ?>
                            <input type="<?= e($meta['type']) ?>" name="<?= e($key) ?>" class="form-control"
                                   value="<?= e($current) ?>" placeholder="<?= e($meta['placeholder'] ?? '') ?>">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="text-end">
        <button type="submit" class="btn btn-primary px-5 fw-bold">
            <i class="bi bi-save-fill me-1"></i> Save All Settings
        </button>
    </div>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
