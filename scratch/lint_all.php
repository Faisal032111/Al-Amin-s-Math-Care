<?php
/**
 * scratch/lint_all.php — Lint all key admin PHP files
 */
$files = [
    'admin/students/create.php',
    'admin/students/edit.php',
    'admin/students/delete.php',
    'admin/students/index.php',
    'admin/attendance/take.php',
    'admin/attendance/view.php',
    'admin/attendance/index.php',
    'admin/fee-ledger/collect.php',
    'admin/fee-ledger/index.php',
    'admin/fee-ledger/receipt.php',
    'admin/courses/create.php',
    'admin/courses/edit.php',
    'admin/courses/index.php',
    'admin/batches/create.php',
    'admin/batches/edit.php',
    'admin/batches/index.php',
    'admin/leads/index.php',
    'admin/settings/index.php',
    'admin/index.php',
    'api/export-students.php',
    'api/export-attendance.php',
    'api/export-payments.php',
];

$passed = 0;
$failed = 0;

foreach ($files as $file) {
    $output = [];
    $code = 0;
    exec("php -l " . escapeshellarg($file) . " 2>&1", $output, $code);
    $result = implode(' ', $output);
    if ($code === 0) {
        echo "✓ {$file}" . PHP_EOL;
        $passed++;
    } else {
        echo "✗ {$file}: {$result}" . PHP_EOL;
        $failed++;
    }
}

echo PHP_EOL;
echo "Passed: {$passed} / " . count($files) . PHP_EOL;
if ($failed > 0) {
    echo "FAILED: {$failed}" . PHP_EOL;
    exit(1);
}
echo "All files OK!" . PHP_EOL;
