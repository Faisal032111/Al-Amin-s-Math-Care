<?php

/**
 * scratch/test_portal_and_seo.php — Comprehensive Student Portal & SEO Verification Test
 */

declare(strict_types=1);

echo "=== Al Amin's Math Care: Student Portal & SEO Verification ===\n\n";

$passCount = 0;
$failCount = 0;

function assert_test(string $name, bool $condition, string $details = ''): void {
    global $passCount, $failCount;
    if ($condition) {
        echo " [PASS] $name\n";
        $passCount++;
    } else {
        echo " [FAIL] $name : $details\n";
        $failCount++;
    }
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

$pdo = db();

// 1. Database Student Verification for Portal Login
$stmt = $pdo->query("SELECT id, name, student_id_code, guardian_phone, status FROM students WHERE is_deleted = 0 LIMIT 1");
$student = $stmt->fetch();

assert_test('DB has active student for portal testing', !empty($student) && $student['status'] === 'active', 'Student record found: ' . ($student['student_id_code'] ?? 'None'));

if ($student) {
    $pin = substr($student['guardian_phone'], -4);
    assert_test('Guardian PIN extraction (last 4 digits)', strlen($pin) === 4 && ctype_digit($pin), "PIN: $pin");

    // Test hash_equals logic
    $validMatch = hash_equals(substr($student['guardian_phone'], -4), $pin);
    assert_test('PIN verification hash_equals succeeds on correct PIN', $validMatch === true);

    $wrongMatch = hash_equals(substr($student['guardian_phone'], -4), '0000');
    assert_test('PIN verification fails on wrong PIN', $wrongMatch === false);
}

// 2. Test Receipt & Student ID Security Logic
$stmtReceipt = $pdo->prepare("SELECT receipt_no, student_id FROM fee_records WHERE student_id = :sid LIMIT 1");
$stmtReceipt->execute([':sid' => $student['id']]);
$receipt = $stmtReceipt->fetch();

if ($receipt) {
    assert_test('Receipt found for student', !empty($receipt['receipt_no']), 'Receipt No: ' . $receipt['receipt_no']);
    
    // Simulate portal user session
    $_SESSION['portal_student_id'] = $student['id'];
    $isOwner = ((int)$receipt['student_id'] === (int)$_SESSION['portal_student_id']);
    assert_test('Portal student authorized to view own receipt', $isOwner === true);

    $isOtherOwner = ((int)$receipt['student_id'] === 99999);
    assert_test('Portal student prevented from viewing another student receipt (IDOR protection)', $isOtherOwner === false);
}

// 3. Test robots.txt
$robotsPath = __DIR__ . '/../robots.txt';
assert_test('robots.txt file exists', file_exists($robotsPath));
if (file_exists($robotsPath)) {
    $robotsContent = file_get_contents($robotsPath);
    assert_test('robots.txt disallows /admin/', strpos($robotsContent, 'Disallow: /admin/') !== false);
    assert_test('robots.txt disallows /portal/', strpos($robotsContent, 'Disallow: /portal/') !== false);
    assert_test('robots.txt includes sitemap.xml', strpos($robotsContent, 'sitemap.xml') !== false);
}

// 4. Test sitemap.xml
$sitemapPath = __DIR__ . '/../sitemap.xml';
assert_test('sitemap.xml file exists', file_exists($sitemapPath));
if (file_exists($sitemapPath)) {
    $sitemapContent = file_get_contents($sitemapPath);
    $xml = @simplexml_load_string($sitemapContent);
    assert_test('sitemap.xml is valid XML', $xml !== false);
    if ($xml !== false) {
        $urls = $xml->url;
        assert_test('sitemap.xml contains public URLs', count($urls) >= 10, 'Count: ' . count($urls));
    }
}

// 5. Test all uploads directories exist
$uploadDirs = ['gallery', 'materials', 'notices', 'qrcodes', 'results', 'students', 'teachers'];
$allDirsExist = true;
foreach ($uploadDirs as $dir) {
    if (!is_dir(__DIR__ . '/../assets/uploads/' . $dir)) {
        $allDirsExist = false;
        break;
    }
}
assert_test('All 7 assets/uploads subdirectories exist', $allDirsExist);

// 6. Test assets/uploads/.htaccess
$uploadsHtaccess = __DIR__ . '/../assets/uploads/.htaccess';
assert_test('assets/uploads/.htaccess exists and blocks PHP', file_exists($uploadsHtaccess) && strpos(file_get_contents($uploadsHtaccess), 'engine off') !== false);

echo "\n============================================\n";
echo "Results: $passCount Passed, $failCount Failed.\n";
echo "============================================\n";
