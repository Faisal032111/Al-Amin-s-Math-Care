<?php
require_once __DIR__ . '/../includes/db.php';
$pdo = db();

$students = [
    [1, 'AMC-2026-001', 'Tanvir Ahmed', 'Md. Kamal Hossain', '01711001122', 'Class 9-10 (SSC)', 'Government Laboratory High School', 'amc_token_tanvir_001'],
    [2, 'AMC-2026-002', 'Nusrat Jahan Mim', 'Farhana Begum', '01819223344', 'Class 9-10 (SSC)', 'Holy Cross Girls High School', 'amc_token_mim_002'],
    [3, 'AMC-2026-003', 'Abrar Fahim', 'Shahidul Islam', '01911334455', 'Class 9-10 (SSC)', 'Dhaka Residential Model College', 'amc_token_abrar_003'],
    [4, 'AMC-2026-004', 'Sadia Islam Riya', 'Rezaul Karim', '01722445566', 'Class 9-10 (SSC)', 'Viqarunnisa Noon School', 'amc_token_sadia_004'],
    [5, 'AMC-2026-005', 'Mahmudul Hasan', 'Anwarul Haque', '01611556677', 'Class 9-10 (SSC)', 'Motijheel Ideal School', 'amc_token_mahmud_005']
];

$stmt = $pdo->prepare('
    INSERT INTO students (id, student_id_code, name, guardian_name, guardian_phone, class_level, school_college, qr_code_token, status, is_deleted)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, "active", 0)
    ON DUPLICATE KEY UPDATE name = VALUES(name), guardian_phone = VALUES(guardian_phone)
');

foreach ($students as $s) {
    $stmt->execute($s);
}

$enrollments = [
    [1, 1, 1, '2026-10-01', 2500.00, 2500.00, 'paid', 'active'],
    [2, 2, 1, '2026-10-01', 2500.00, 2500.00, 'paid', 'active'],
    [3, 3, 1, '2026-10-02', 2500.00, 1500.00, 'partially_paid', 'active'],
    [4, 4, 1, '2026-10-02', 2500.00, 2500.00, 'paid', 'active'],
    [5, 5, 1, '2026-10-03', 2500.00, 0.00, 'unpaid', 'active']
];

$enStmt = $pdo->prepare('
    INSERT INTO enrollments (id, student_id, batch_id, enrollment_date, total_fee, paid_amount, payment_status, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE status = VALUES(status), paid_amount = VALUES(paid_amount)
');

foreach ($enrollments as $e) {
    $enStmt->execute($e);
}

echo "Successfully seeded " . count($students) . " students and " . count($enrollments) . " enrollments into Batch 1.\n";
