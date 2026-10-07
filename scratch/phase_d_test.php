<?php
/**
 * scratch/phase_d_test.php - Phase D Comprehensive Functional Test
 * Tests all CRUD operations and exports against real DB
 */
declare(strict_types=1);

session_id('phased_test_session');
session_start();
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_user_id']   = 1;
$_SESSION['admin_role']      = 'super_admin';

require 'includes/db.php';
require 'includes/helpers.php';

$pdo    = db();
$passed = 0;
$failed = 0;

function test(string $name, callable $fn): void {
    global $passed, $failed;
    try {
        $result = $fn();
        if ($result === false) {
            echo "✗ {$name}: assertion failed" . PHP_EOL;
            $failed++;
        } else {
            echo "✓ {$name}" . ($result !== true ? ": {$result}" : '') . PHP_EOL;
            $passed++;
        }
    } catch (Throwable $e) {
        echo "✗ {$name}: " . $e->getMessage() . PHP_EOL;
        $failed++;
    }
}

echo "========================================" . PHP_EOL;
echo " Phase D: Admin CRUD & Export Tests" . PHP_EOL;
echo "========================================" . PHP_EOL . PHP_EOL;

// ── 1. Students ────────────────────────────────────────────
echo "--- Students ---" . PHP_EOL;

test('Students list query', function() use ($pdo) {
    $count = $pdo->query('SELECT COUNT(*) FROM students WHERE is_deleted = 0')->fetchColumn();
    return (int)$count . ' active students';
});

test('Student ID code format', function() use ($pdo) {
    $sid = $pdo->query("SELECT student_id_code FROM students LIMIT 1")->fetchColumn();
    return preg_match('/^AMC-\d{4}-\d{3,}$/', (string)$sid) ? true : false;
});

test('Guardian phone format', function() use ($pdo) {
    $phone = $pdo->query("SELECT guardian_phone FROM students LIMIT 1")->fetchColumn();
    return is_valid_bd_phone((string)$phone) ? "valid: {$phone}" : false;
});

test('Student with enrollment', function() use ($pdo) {
    $row = $pdo->query('SELECT s.name, b.batch_name FROM students s JOIN enrollments e ON s.id = e.student_id AND e.status = "active" JOIN batches b ON e.batch_id = b.id LIMIT 1')->fetch();
    return $row ? $row['name'] . ' → ' . $row['batch_name'] : false;
});

// ── 2. Attendance ─────────────────────────────────────────
echo PHP_EOL . "--- Attendance ---" . PHP_EOL;

test('Attendance records exist', function() use ($pdo) {
    $count = $pdo->query('SELECT COUNT(*) FROM attendances')->fetchColumn();
    return (int)$count . ' attendance records';
});

test('Attendance date range', function() use ($pdo) {
    $row = $pdo->query('SELECT MIN(attendance_date) as oldest, MAX(attendance_date) as newest FROM attendances')->fetch();
    return $row['oldest'] . ' to ' . $row['newest'];
});

test('Attendance status distribution', function() use ($pdo) {
    $rows = $pdo->query('SELECT status, COUNT(*) as cnt FROM attendances GROUP BY status')->fetchAll();
    $parts = array_map(fn($r) => $r['status'] . ':' . $r['cnt'], $rows);
    return implode(', ', $parts);
});

// ── 3. Fee Records & Migration ────────────────────────────
echo PHP_EOL . "--- Fee Ledger & Records ---" . PHP_EOL;

test('fee_records has payment_date column', function() use ($pdo) {
    $cols = $pdo->query('DESCRIBE fee_records')->fetchAll(PDO::FETCH_COLUMN);
    return in_array('payment_date', $cols);
});

test('fee_records has fee_month column', function() use ($pdo) {
    $cols = $pdo->query('DESCRIBE fee_records')->fetchAll(PDO::FETCH_COLUMN);
    return in_array('fee_month', $cols);
});

test('student_fees ledger', function() use ($pdo) {
    $count = $pdo->query('SELECT COUNT(*) FROM student_fees')->fetchColumn();
    return (int)$count . ' ledger entries';
});

test('Monthly fee totals', function() use ($pdo) {
    $row = $pdo->query('SELECT SUM(paid_amount) as paid, SUM(fee_amount) as due FROM student_fees WHERE fee_month = "' . date('Y-m') . '"')->fetch();
    return "Paid: ৳" . number_format((float)($row['paid'] ?? 0)) . ", Due: ৳" . number_format((float)($row['due'] ?? 0));
});

// ── 4. Courses & Batches ──────────────────────────────────
echo PHP_EOL . "--- Courses & Batches ---" . PHP_EOL;

test('Active courses', function() use ($pdo) {
    $count = $pdo->query('SELECT COUNT(*) FROM courses WHERE is_deleted = 0 AND is_active = 1')->fetchColumn();
    return (int)$count . ' active courses';
});

test('Batches with seat tracking', function() use ($pdo) {
    $row = $pdo->query('SELECT batch_name, total_seats, available_seats FROM batches WHERE is_deleted = 0 LIMIT 1')->fetch();
    return $row ? $row['batch_name'] . ": {$row['available_seats']}/{$row['total_seats']} seats" : false;
});

test('Batch-course-teacher join', function() use ($pdo) {
    $row = $pdo->query('SELECT b.batch_name, c.title_en, t.name_en FROM batches b JOIN courses c ON b.course_id = c.id JOIN teachers t ON b.teacher_id = t.id WHERE b.is_deleted = 0 LIMIT 1')->fetch();
    return $row ? $row['batch_name'] . " / " . $row['title_en'] . " / " . $row['name_en'] : false;
});

// ── 5. Export Queries ─────────────────────────────────────
echo PHP_EOL . "--- Export Queries ---" . PHP_EOL;

test('Student export query', function() use ($pdo) {
    $count = $pdo->query('SELECT COUNT(*) FROM students s LEFT JOIN enrollments e ON s.id = e.student_id AND e.status = "active" WHERE s.is_deleted = 0')->fetchColumn();
    return (int)$count . ' rows for student export';
});

test('Attendance export query', function() use ($pdo) {
    $count = $pdo->query('SELECT COUNT(*) FROM attendances a JOIN students s ON a.student_id = s.id JOIN batches b ON a.batch_id = b.id')->fetchColumn();
    return (int)$count . ' rows for attendance export';
});

test('Payment export query (ledger)', function() use ($pdo) {
    $count = $pdo->query('SELECT COUNT(*) FROM students s LEFT JOIN enrollments e ON s.id = e.student_id AND e.status = "active" LEFT JOIN student_fees sf ON s.id = sf.student_id AND sf.fee_month = "' . date('Y-m') . '" WHERE s.is_deleted = 0')->fetchColumn();
    return (int)$count . ' rows for ledger export';
});

test('Payment transactions export query', function() use ($pdo) {
    $count = $pdo->query('SELECT COUNT(*) FROM fee_records fr JOIN students s ON fr.student_id = s.id WHERE s.is_deleted = 0')->fetchColumn();
    return (int)$count . ' payment records';
});

// ── 6. csv_safe() protection ──────────────────────────────
echo PHP_EOL . "--- Security: CSV Injection Prevention ---" . PHP_EOL;

test('csv_safe blocks = formula', function() {
    return csv_safe('=SUM(1+1)') === "'=SUM(1+1)";
});
test('csv_safe blocks + formula', function() {
    return csv_safe('+cmd') === "'+cmd";
});
test('csv_safe allows normal text', function() {
    return csv_safe('Tanvir Ahmed') === 'Tanvir Ahmed';
});
test('csv_safe allows Bengali text', function() {
    return csv_safe('আল আমিন') === 'আল আমিন';
});

// ── 7. Settings Table ─────────────────────────────────────
echo PHP_EOL . "--- Settings ---" . PHP_EOL;

test('Settings table accessible', function() use ($pdo) {
    $count = $pdo->query('SELECT COUNT(*) FROM settings')->fetchColumn();
    return (int)$count . ' settings rows (empty is OK)';
});

test('Settings upsert works', function() use ($pdo) {
    $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES ("test_phase_d", "ok") ON DUPLICATE KEY UPDATE setting_value = "ok"')->execute();
    $val = $pdo->query('SELECT setting_value FROM settings WHERE setting_key = "test_phase_d"')->fetchColumn();
    return $val === 'ok';
});

// ── 8. Leads ──────────────────────────────────────────────
echo PHP_EOL . "--- Leads Management ---" . PHP_EOL;

test('Leads table', function() use ($pdo) {
    $count = $pdo->query('SELECT COUNT(*) FROM leads WHERE is_deleted = 0')->fetchColumn();
    return (int)$count . ' leads';
});

test('Pending leads count', function() use ($pdo) {
    $count = $pdo->query('SELECT COUNT(*) FROM leads WHERE status IN ("new", "contacted") AND is_deleted = 0')->fetchColumn();
    return (int)$count . ' pending leads';
});

// ── Summary ────────────────────────────────────────────────
echo PHP_EOL . "========================================" . PHP_EOL;
$total = $passed + $failed;
echo "PASSED: {$passed} / {$total}" . PHP_EOL;
if ($failed > 0) {
    echo "FAILED: {$failed}" . PHP_EOL;
    exit(1);
}
echo "ALL TESTS PASSED ✓" . PHP_EOL;
