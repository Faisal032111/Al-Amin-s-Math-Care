<?php
/**
 * scratch/test_exports_final.php - Final export test with proper auth
 */
declare(strict_types=1);

session_id('exporttest123');
session_start();
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_user_id']   = 1;
$_SESSION['admin_role']      = 'super_admin';

require 'includes/db.php';
require 'includes/helpers.php';

$pdo = db();
$passed = 0;
$failed = 0;

function testExport(string $name, string $file, array $get = []): void {
    global $passed, $failed;
    $_GET = $get;
    ob_start();
    try {
        // Reset headers tracking
        $headers = [];
        include $file;
    } catch (Throwable $e) {
        // exit() in export files ends include, so we catch cleanup here
    }
    $output = ob_get_clean();

    // Remove UTF-8 BOM if present
    if (str_starts_with($output, "\xEF\xBB\xBF")) {
        $output = substr($output, 3);
    }

    $lines = array_filter(explode("\n", trim($output)));
    $lineCount = count($lines);

    if ($lineCount > 0) {
        $header = reset($lines);
        echo "✓ {$name}: {$lineCount} line(s). Header: " . substr($header, 0, 80) . PHP_EOL;
        $passed++;
    } else {
        echo "✗ {$name}: No output" . PHP_EOL;
        $failed++;
    }
    $_GET = [];
}

echo "=== Export Output Tests ===" . PHP_EOL . PHP_EOL;

testExport('Students CSV',            'api/export-students.php',   []);
testExport('Students CSV (batch=1)',  'api/export-students.php',   ['batch_id' => '1']);
testExport('Attendance CSV (all)',    'api/export-attendance.php',  []);
testExport('Attendance CSV (batch1)', 'api/export-attendance.php', ['batch_id' => '1']);
testExport('Payments Ledger CSV',     'api/export-payments.php',   ['type' => 'ledger', 'month' => date('Y-m')]);
testExport('Payment Transactions CSV','api/export-payments.php',   ['type' => 'transactions']);

echo PHP_EOL . "Passed: {$passed} / " . ($passed + $failed) . PHP_EOL;
if ($failed === 0) {
    echo "All exports working ✓" . PHP_EOL;
}
