<?php
/**
 * scratch/http_smoke_test.php - HTTP smoke test via cURL
 * Tests all admin pages return HTTP 200 (after auth redirect, check for redirect or 200)
 */
declare(strict_types=1);

$base = 'http://127.0.0.1:8000';

// Build a cookie jar for session persistence
$cookieJar = tempnam(sys_get_temp_dir(), 'amc_cookie_');

// ── Step 1: Login ──────────────────────────────────────────
echo "=== Step 1: Admin Login ===" . PHP_EOL;

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $base . '/admin/login.php',
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query([
        'email'      => 'admin@alaminmathcare.com',
        'password'   => 'Faisal@5511045',
        'csrf_token' => '',   // will get proper token below
    ]),
    CURLOPT_COOKIEJAR      => $cookieJar,
    CURLOPT_COOKIEFILE     => $cookieJar,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER         => true,
    CURLOPT_TIMEOUT        => 10,
]);

// First: GET the login page to get CSRF token
curl_setopt($ch, CURLOPT_POST, false);
curl_setopt($ch, CURLOPT_URL, $base . '/admin/login.php');
$html = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "GET login.php → HTTP {$httpCode}" . PHP_EOL;

// Extract CSRF token
preg_match('/name="csrf_token" value="([^"]+)"/', $html, $m);
$csrfToken = $m[1] ?? '';
echo "CSRF token: " . ($csrfToken ? substr($csrfToken, 0, 16) . '...' : 'NOT FOUND') . PHP_EOL;

// POST login
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_URL, $base . '/admin/login.php');
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'email'      => 'admin@alaminmathcare.com',
    'password'   => 'Faisal@5511045',
    'csrf_token' => $csrfToken,
]));
$loginResp = curl_exec($ch);
$loginCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$loginLocation = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
echo "POST login → HTTP {$loginCode}" . PHP_EOL;
echo "Redirect → " . ($loginLocation ?: '(none)') . PHP_EOL;

// Follow the redirect manually
if ($loginCode === 302) {
    curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_URL, $loginLocation ?: $base . '/admin/index.php');
    curl_exec($ch);
    echo "Followed redirect → " . curl_getinfo($ch, CURLINFO_HTTP_CODE) . PHP_EOL;
}

echo PHP_EOL;

// ── Step 2: Test all admin pages ──────────────────────────
$pages = [
    'admin/index.php'                                              => 'Dashboard',
    'admin/students/'                                              => 'Students List',
    'admin/students/create.php'                                    => 'Student Create Form',
    'admin/students/edit.php?id=1'                                 => 'Student Edit Form',
    'admin/courses/'                                               => 'Courses List',
    'admin/courses/create.php'                                     => 'Course Create Form',
    'admin/batches/'                                               => 'Batches List',
    'admin/batches/create.php'                                     => 'Batch Create Form',
    'admin/attendance/'                                            => 'Attendance Index',
    'admin/attendance/view.php?batch_id=1&date=2026-10-07'        => 'Attendance View',
    'admin/attendance/take.php?batch_id=1&date=2026-10-07'        => 'Attendance Take',
    'admin/fee-ledger/'                                            => 'Fee Ledger',
    'admin/fee-ledger/collect.php'                                 => 'Fee Collect Form',
    'admin/leads/'                                                 => 'Leads',
    'admin/settings/'                                              => 'Settings',
    'api/export-students.php'                                      => 'Export Students',
    'api/export-attendance.php?batch_id=1'                        => 'Export Attendance',
    'api/export-payments.php?type=ledger&month=' . date('Y-m')    => 'Export Payments Ledger',
];

echo "=== Step 2: Page HTTP Status Tests ===" . PHP_EOL;
curl_setopt($ch, CURLOPT_POST, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HEADER, false);

$passed = 0;
$failed = 0;

foreach ($pages as $path => $label) {
    curl_setopt($ch, CURLOPT_URL, $base . '/' . $path);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Check for PHP fatal errors/warnings in output
    $hasFatal = str_contains((string)$body, 'Fatal error') || str_contains((string)$body, 'Parse error');
    $hasWarning = str_contains((string)$body, 'PHP Warning') || str_contains((string)$body, 'PHP Notice');

    if ($code === 200 && !$hasFatal) {
        $suffix = $hasWarning ? ' ⚠ PHP Warning' : '';
        echo "✓ [{$code}] {$label}{$suffix}" . PHP_EOL;
        $passed++;
    } elseif ($code === 200 && $hasFatal) {
        echo "✗ [{$code}] {$label}: PHP FATAL ERROR in output" . PHP_EOL;
        echo "  " . substr((string)$body, 0, 300) . PHP_EOL;
        $failed++;
    } else {
        echo "✗ [{$code}] {$label}" . PHP_EOL;
        $failed++;
    }
}

curl_close($ch);
@unlink($cookieJar);

echo PHP_EOL;
echo "=== Results ===" . PHP_EOL;
echo "Passed: {$passed} / " . count($pages) . PHP_EOL;
if ($failed > 0) {
    echo "Failed: {$failed}" . PHP_EOL;
}
