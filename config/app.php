<?php

/**
 * config/app.php — Application Configuration
 * Al Amin's Math Care | alaminmathcare.com
 *
 * Environment: set APP_ENV to 'production' on live server.
 */

declare(strict_types=1);

// ──────────────────────────────────────────────
// Environment Detection
// ──────────────────────────────────────────────
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('APP_DEBUG', APP_ENV !== 'production');
define('APP_NAME', "Al Amin's Math Care");
define('APP_VERSION', '1.0.0');

// ──────────────────────────────────────────────
// Error Reporting (Security: never show errors in production)
// ──────────────────────────────────────────────
if (APP_ENV === 'production') {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/../storage/logs/error.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
}

// ──────────────────────────────────────────────
// Secure Session Configuration
// Only applied BEFORE session_start() is called
// ──────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.gc_maxlifetime', '7200');   // 2 hours

    if (APP_ENV === 'production') {
        ini_set('session.cookie_secure', '1');   // HTTPS only in production
    }
}

// ──────────────────────────────────────────────
// Application Settings Array
// ──────────────────────────────────────────────
return [
    // ── Site Identity ──────────────────────────
    'site_name'         => APP_NAME,
    'site_tagline_en'   => 'Expert Mathematics Coaching in Farmgate, Dhaka',
    'site_tagline_bn'   => 'ফার্মগেট, ঢাকায় বিশেষজ্ঞ গণিত কোচিং',
    'domain'            => getenv('APP_DOMAIN') ?: ($_SERVER['HTTP_HOST'] ?? 'alaminmathcare.com'),
    'base_url'          => getenv('APP_BASE_URL') !== false ? (string)getenv('APP_BASE_URL') : '',  // '' = root; '/subfolder' = subdirectory

    // ── Language / Locale ───────────────────────
    'default_locale'    => 'en',
    'supported_locales' => ['en', 'bn'],

    // ── Contact Information ─────────────────────
    'phone_primary'     => '01520102248',
    'phone_secondary'   => '01521255850',
    'whatsapp'          => '01520102248',

    // ── Campus Address ──────────────────────────
    'address_en' => '46/1, Britter Goli, Opposite Holy Cross College, Farmgate, Dhaka 1216',
    'address_bn' => '৪৬/১, বৃত্তের গলি, হোলি ক্রস কলেজের বিপরীতে, ফার্মগেট, ঢাকা ১২১৬',
    'area'       => 'Farmgate',
    'city'       => 'Dhaka',
    'postcode'   => '1216',
    'landmark'   => 'Opposite Holy Cross College',

    // ── Google Maps ─────────────────────────────
    'google_maps_query'      => '46/1 Britter Goli Holy Cross College Farmgate Dhaka',
    'google_maps_embed'      => 'https://maps.google.com/maps?q=46%2F1+Britter+Goli+Holy+Cross+College+Farmgate+Dhaka&t=&z=16&ie=UTF8&iwloc=&output=embed',
    'google_maps_directions' => 'https://www.google.com/maps/dir/?api=1&destination=46%2F1+Britter+Goli+Holy+Cross+College+Farmgate+Dhaka+1216',

    // ── Faculty ─────────────────────────────────
    'head_teacher'  => 'Al Amin Sir',
    'subject_focus' => 'Mathematics',

    // ── Social Media ────────────────────────────
    'facebook_url'  => '',
    'messenger_url' => '',
    'youtube_url'   => '',

    // ── Business Hours ──────────────────────────
    'opening_hours_en' => 'Contact us for class schedule',
    'opening_hours_bn' => 'ক্লাসের সময়সূচী জানতে যোগাযোগ করুন',

    // ── SMS Gateway (configured via admin/settings) ──
    'sms_gateway_url'    => '',
    'sms_api_key'        => '',
    'sms_sender_id'      => 'AlAmMath',
    'sms_daily_limit'    => 500,

    // ── Rate Limiting ────────────────────────────
    'lead_rate_limit_per_hour' => 10,   // max lead submissions per IP/hour
    'result_rate_limit_per_min' => 5,   // max result searches per IP/minute
    'login_max_attempts'        => 5,   // max failed logins before lockout
    'login_lockout_minutes'     => 15,  // lockout duration in minutes
];
