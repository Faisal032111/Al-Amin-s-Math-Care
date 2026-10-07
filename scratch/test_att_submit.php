<?php
require_once __DIR__ . '/../includes/db.php';
$pdo = db();

$batchId = 1;
$date = '2026-10-07';
$attendanceData = [
    1 => 'present',
    2 => 'present',
    3 => 'present',
    4 => 'absent',
    5 => 'present'
];

$insertStmt = $pdo->prepare('
    INSERT INTO attendances (student_id, batch_id, attendance_date, status, recorded_by, sms_sent_status, sms_response)
    VALUES (:sid, :bid, :dt, :st, :uid, :sms_st, :sms_resp)
    ON DUPLICATE KEY UPDATE status = VALUES(status), recorded_by = VALUES(recorded_by)
');

foreach ($attendanceData as $sid => $status) {
    $insertStmt->execute([
        ':sid' => $sid,
        ':bid' => $batchId,
        ':dt' => $date,
        ':st' => $status,
        ':uid' => 1,
        ':sms_st' => 'none',
        ':sms_resp' => null
    ]);
}

echo "Successfully recorded attendance for Batch 1 on $date.\n";

$records = $pdo->query("
    SELECT s.student_id_code, s.name, a.status, a.attendance_date, a.sms_sent_status 
    FROM attendances a 
    JOIN students s ON a.student_id = s.id 
    WHERE a.batch_id = 1 AND a.attendance_date = '$date'
")->fetchAll();

echo json_encode($records, JSON_PRETTY_PRINT) . "\n";
