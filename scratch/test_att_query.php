<?php
require_once __DIR__ . '/../includes/db.php';
$pdo = db();

$batchId = 1;
$date = '2026-10-07';

$stuStmt = $pdo->prepare('
    SELECT s.id, s.student_id_code, s.name, s.guardian_name, s.guardian_phone,
           a.status as current_status, a.sms_sent_status
    FROM students s
    JOIN enrollments e ON s.id = e.student_id
    LEFT JOIN attendances a ON s.id = a.student_id AND a.batch_id = :att_bid AND a.attendance_date = :dt
    WHERE e.batch_id = :enr_bid AND e.status = "active" AND s.is_deleted = 0
    ORDER BY s.student_id_code ASC
');
$stuStmt->execute([':att_bid' => $batchId, ':enr_bid' => $batchId, ':dt' => $date]);
$students = $stuStmt->fetchAll();

echo "Loaded " . count($students) . " students for Batch 1 attendance sheet on " . $date . ":\n";
foreach ($students as $s) {
    echo "- [" . $s['student_id_code'] . "] " . $s['name'] . " (Phone: " . $s['guardian_phone'] . ")\n";
}
