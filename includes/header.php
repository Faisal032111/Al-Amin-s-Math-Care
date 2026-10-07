<?php

declare(strict_types=1);

require_once __DIR__ . '/i18n.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/csrf.php';

$config = app_config();
$pageTitle = $pageTitle ?? page_title();
$bodyClass = $bodyClass ?? '';
?>
<!DOCTYPE html>
<html lang="<?= e($locale) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e(config_tagline()) ?>">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($pageTitle) ?></title>

    <!-- OpenGraph & Social Metadata -->
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e(config_tagline()) ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= e(base_url()) ?>">
    <meta property="og:site_name" content="<?= e($config['site_name']) ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800;900&family=Noto+Sans+Bengali:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= e(base_url('assets/css/main.css')) ?>" rel="stylesheet">

    <!-- Schema.org LocalBusiness / EducationalOrganization JSON-LD -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "EducationalOrganization",
      "name": "<?= addslashes($config['site_name']) ?>",
      "description": "Specialized Mathematics Coaching for SSC, HSC, and University Admission in Farmgate, Dhaka",
      "url": "<?= e(base_url()) ?>",
      "telephone": "<?= e($config['phone_primary']) ?>",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "46/1, Britter Goli, Opposite Holy Cross College",
        "addressLocality": "Farmgate",
        "addressRegion": "Dhaka",
        "postalCode": "1216",
        "addressCountry": "BD"
      }
    }
    </script>
</head>
<body class="locale-<?= e($locale) ?> <?= e($bodyClass) ?>">

<?php require __DIR__ . '/navbar.php'; ?>

<main>
