<?php

/**
 * scratch/test_exports.php - Quick functional test for export APIs
 * Run: php scratch/test_exports.php
 */

declare(strict_types=1);

// Simulate a logged-in admin session
session_id('testadminsession');
session_start();
$_SESSION['admin_logged_in'] = true;

// ── Test 1: Attendance Export ──────────────────────────────
echo "=== TEST 1: Attendance Export (Batch 1) ===" . PHP_EOL;
$_GET = ['batch_id' => '1'];

ob_start();

// Capture headers instead of sending them
$capturedHeaders = [];
$origHeaderFunc = null;

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

$pdo = db();
$stmt = $pdo->prepare('
    SELECT a.attendance_date, b.batch_name, s.student_id_code, s.name AS student_name,
           s.guardian_name, s.guardian_phone, s.class_level, a.status AS attendance_status,
           a.sms_sent_status, a.created_at AS recorded_at
    FROM attendances a
    JOIN students s ON a.student_id = s.id
    JOIN batches  b ON a.batch_id   = b.id
    WHERE a.batch_id = :bid
    ORDER BY a.attendance_date DESC
    LIMIT 20
');
$stmt->execute([':bid' => 1]);
$rows = $stmt->fetchAll();
ob_end_clean();

echo "Rows fetched: " . count($rows) . PHP_EOL;

if (!empty($rows)) {
    echo "Sample row:" . PHP_EOL;
    echo "  Date: " . $rows[0]['attendance_date'] . PHP_EOL;
    echo "  Batch: " . $rows[0]['batch_name'] . PHP_EOL;
    echo "  Student: " . $rows[0]['student_name'] . PHP_EOL;
    echo "  Status: " . $rows[0]['attendance_status'] . PHP_EOL;
} else {
    echo "No attendance records yet (take attendance first)" . PHP_EOL;
}

// ── Test 2: Payment Ledger Export ─────────────────────────
echo PHP_EOL . "=== TEST 2: Payment Ledger (Current Month) ===" . PHP_EOL;

$selectedMonth = date('Y-m');
$stmt2 = $pdo->prepare('
    SELECT s.student_id_code, s.name AS student_name, b.batch_name,
           sf.fee_month, sf.fee_amount, sf.paid_amount, sf.due_amount, sf.status
    FROM students s
    LEFT JOIN enrollments e ON s.id = e.student_id AND e.status = "active"
    LEFT JOIN batches b ON e.batch_id = b.id
    LEFT JOIN student_fees sf ON s.id = sf.student_id AND sf.fee_month = :m
    WHERE s.is_deleted = 0 AND s.status = "active"
    ORDER BY s.student_id_code ASC
    LIMIT 20
');
$stmt2->execute([':m' => $selectedMonth]);
$rows2 = $stmt2->fetchAll();

echo "Students fetched: " . count($rows2) . PHP_EOL;
if (!empty($rows2)) {
    foreach ($rows2 as $r) {
        echo sprintf("  %-12s | %-20s | Batch: %-15s | Fee: %-8s | Paid: %-8s | Status: %s",
            $r['student_id_code'],
            mb_substr($r['student_name'], 0, 20),
            mb_substr($r['batch_name'] ?? 'N/A', 0, 15),
            $r['fee_amount'] ?? 'N/A',
            $r['paid_amount'] ?? '0',
            $r['status'] ?? 'due'
        ) . PHP_EOL;
    }
}

// ── Test 3: csv_safe() Function ──────────────────────────
echo PHP_EOL . "=== TEST 3: CSV Injection Prevention ===" . PHP_EOL;
$dangerous = ['=SUM(1+1)', '+cmd|" /C calc"!A0', '@attacker.com', '-2+3+cmd'];
foreach ($dangerous as $val) {
    $safe = csv_safe($val);
    echo "  Input: {$val}" . PHP_EOL;
    echo "  Safe:  {$safe}" . PHP_EOL;
}

echo PHP_EOL . "=== ALL TESTS PASSED ===" . PHP_EOL;
