<?php

/**
 * includes/helpers.php — Global Utility Functions
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);

// ──────────────────────────────────────────────────────
// 1. XSS Protection
// ──────────────────────────────────────────────────────

/**
 * Escape a string for safe HTML output (XSS prevention).
 * Always use this when echoing user-supplied or DB data.
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ──────────────────────────────────────────────────────
// 2. Input Sanitization
// ──────────────────────────────────────────────────────

/**
 * Clean and trim a single input value.
 * Use for text fields before validation.
 */
function clean_input(mixed $data): string
{
    return trim(strip_tags((string) $data));
}

/**
 * Validate a Bangladeshi mobile number (01XXXXXXXXX format).
 * Accepts 11-digit numbers starting with 01.
 */
function is_valid_bd_phone(string $phone): bool
{
    return (bool) preg_match('/^01[3-9]\d{8}$/', preg_replace('/\D/', '', $phone));
}

/**
 * Sanitize a phone number to digits only.
 */
function sanitize_phone(string $phone): string
{
    return preg_replace('/[^0-9+]/', '', $phone);
}

// ──────────────────────────────────────────────────────
// 3. URL & Slug Helpers
// ──────────────────────────────────────────────────────

/**
 * Convert a string to a URL-safe slug.
 * Example: "SSC Higher Math" → "ssc-higher-math"
 */
function slugify(string $text): string
{
    // Transliterate to ASCII if possible
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

// ──────────────────────────────────────────────────────
// 4. Date & Time Helpers
// ──────────────────────────────────────────────────────

/**
 * Convert a Gregorian date string to Bangla digits.
 * Example: "2025-09-15" → "২০২৫-০৯-১৫"
 */
function bangla_date(string $date = '', string $format = 'd M Y'): string
{
    $timestamp = $date !== '' ? strtotime($date) : time();
    $formatted = date($format, $timestamp !== false ? $timestamp : time());

    $en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $bn = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];

    return str_replace($en, $bn, $formatted);
}

/**
 * Format a date string for human-friendly display.
 */
function format_date(string $date, string $format = 'd M Y'): string
{
    $ts = strtotime($date);
    return $ts !== false ? date($format, $ts) : $date;
}

/**
 * Returns human-readable "time ago" string.
 * Example: "3 days ago"
 */
function time_ago(string $datetime): string
{
    $diff = time() - strtotime($datetime);
    if ($diff < 60)    return 'just now';
    if ($diff < 3600)  return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return date('d M Y', strtotime($datetime));
}

// ──────────────────────────────────────────────────────
// 5. Link Helpers
// ──────────────────────────────────────────────────────

/**
 * Generate a tel: link for a phone number.
 */
function phone_link(string $number): string
{
    $digits = preg_replace('/\D/', '', $number);
    // Ensure country code 88 prefix
    if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
        $digits = '88' . $digits;
    }
    return 'tel:+' . $digits;
}

/**
 * Generate a WhatsApp chat link with optional pre-filled message.
 */
function whatsapp_link(string $number, string $message = ''): string
{
    $digits = preg_replace('/\D/', '', $number);
    if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
        $digits = '88' . $digits;
    }
    $url = 'https://wa.me/' . $digits;
    if ($message !== '') {
        $url .= '?text=' . rawurlencode($message);
    }
    return $url;
}

// ──────────────────────────────────────────────────────
// 6. Config & Navigation Helpers
// ──────────────────────────────────────────────────────

/**
 * Get the localized address from app config.
 */
function config_address(): string
{
    $locale = function_exists('get_current_lang') ? get_current_lang() : 'en';
    $config = app_config();
    return $locale === 'bn' ? $config['address_bn'] : $config['address_en'];
}

/**
 * Get the localized site tagline from app config.
 */
function config_tagline(): string
{
    $locale = function_exists('get_current_lang') ? get_current_lang() : 'en';
    $config = app_config();
    return $locale === 'bn' ? $config['site_tagline_bn'] : $config['site_tagline_en'];
}

/**
 * Build a <title> tag value.
 */
function page_title(string $title = ''): string
{
    $site = app_config()['site_name'];
    return $title !== '' ? $title . ' — ' . $site : $site . ' | ' . config_tagline();
}

/**
 * Return 'active' CSS class if the current page matches.
 */
function active_nav(string $page): string
{
    $current = basename($_SERVER['PHP_SELF'] ?? 'index.php');
    return $current === $page ? 'active' : '';
}

// ──────────────────────────────────────────────────────
// 7. CSV / Excel Export Helper (CSV Injection Prevention)
// ──────────────────────────────────────────────────────

/**
 * Sanitize a cell value for safe CSV export.
 * Prevents CSV Injection (formula injection attack).
 * Prefixes dangerous leading characters with a single quote.
 */
function csv_safe(string $value): string
{
    $dangerous = ['=', '+', '-', '@', "\t", "\r", "\n"];
    if ($value !== '' && in_array($value[0], $dangerous, true)) {
        $value = "'" . $value;
    }
    return $value;
}

// ──────────────────────────────────────────────────────
// 8. Rate Limiting Helper (IP-based, file-backed)
// ──────────────────────────────────────────────────────

/**
 * Simple IP-based rate limiter using file storage.
 *
 * @param string $key      Unique action key (e.g., 'lead_submit', 'result_search')
 * @param int    $limit    Max allowed attempts in the window
 * @param int    $window   Time window in seconds (default: 3600 = 1 hour)
 * @return bool            TRUE if within limit, FALSE if exceeded
 */
function rate_limit_check(string $key, int $limit, int $window = 3600): bool
{
    $ip      = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $hash    = md5($ip . $key);
    $dir     = __DIR__ . '/../storage/rate_limits';
    $file    = $dir . '/' . $hash . '.json';

    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $now  = time();
    $data = ['count' => 0, 'window_start' => $now];

    if (is_file($file)) {
        $data = json_decode((string) file_get_contents($file), true) ?: $data;
    }

    // Reset window if expired
    if ($now - $data['window_start'] > $window) {
        $data = ['count' => 0, 'window_start' => $now];
    }

    if ($data['count'] >= $limit) {
        return false; // Rate limit exceeded
    }

    $data['count']++;
    file_put_contents($file, json_encode($data), LOCK_EX);
    return true;
}

// ──────────────────────────────────────────────────────
// 9. Static Course Data (fallback before DB is set up)
// ──────────────────────────────────────────────────────

function courses_data(): array
{
    return [
        ['slug' => 'class-6-8-mathematics',    'class' => '6–8',       'subject' => 'Mathematics', 'teacher' => 'Al Amin Sir', 'duration' => 'Ongoing',         'fee' => 'Contact us'],
        ['slug' => 'ssc-general-mathematics',   'class' => 'SSC',       'subject' => 'Mathematics', 'teacher' => 'Al Amin Sir', 'duration' => 'Full syllabus',   'fee' => 'Contact us'],
        ['slug' => 'ssc-higher-mathematics',    'class' => 'SSC',       'subject' => 'Higher Math', 'teacher' => 'Al Amin Sir', 'duration' => 'Full syllabus',   'fee' => 'Contact us'],
        ['slug' => 'hsc-higher-math-1st-paper', 'class' => 'HSC',       'subject' => 'Higher Math 1st', 'teacher' => 'Al Amin Sir', 'duration' => 'Full syllabus', 'fee' => 'Contact us'],
        ['slug' => 'hsc-higher-math-2nd-paper', 'class' => 'HSC',       'subject' => 'Higher Math 2nd', 'teacher' => 'Al Amin Sir', 'duration' => 'Full syllabus', 'fee' => 'Contact us'],
        ['slug' => 'admission-mathematics',     'class' => 'Admission', 'subject' => 'Mathematics', 'teacher' => 'Al Amin Sir', 'duration' => 'Intensive prep', 'fee' => 'Contact us'],
    ];
}
