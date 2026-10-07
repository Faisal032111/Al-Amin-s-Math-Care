<?php

/**
 * api/export-attendance.php — Secure Attendance CSV/Excel Exporter
 * Al Amin's Math Care | alaminmathcare.com
 *
 * Exports attendance records filtered by batch, date range, or single date.
 * Protected against CSV Formula Injection (CWE-1236) via csv_safe().
 * Requires admin authentication.
 */

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

// ── Auth Guard ────────────────────────────────────────────
if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Admin login required.']);
    exit;
}

// ── Input Filters ─────────────────────────────────────────
$batchId   = !empty($_GET['batch_id'])   ? (int)$_GET['batch_id']   : null;
$dateFrom  = clean_input($_GET['date_from']  ?? '');
$dateTo    = clean_input($_GET['date_to']    ?? '');
$singleDate= clean_input($_GET['date']       ?? '');   // single-day shortcut
$status    = clean_input($_GET['status']     ?? '');   // present | absent | late

// Validate date formats (YYYY-MM-DD)
$validDate = fn(string $d): bool => (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && strtotime($d) !== false;

if ($singleDate && $validDate($singleDate)) {
    $dateFrom = $singleDate;
    $dateTo   = $singleDate;
}

// Clamp range: if only one boundary given, default the other
if ($dateFrom && !$dateTo)  $dateTo   = date('Y-m-d');
if ($dateTo   && !$dateFrom) $dateFrom = date('Y-m-d', strtotime('-1 year'));

try {
    $pdo = db();

    $sql = '
        SELECT
            a.attendance_date,
            b.batch_name,
            s.student_id_code,
            s.name            AS student_name,
            s.guardian_name,
            s.guardian_phone,
            s.class_level,
            a.status          AS attendance_status,
            a.sms_sent_status,
            a.created_at      AS recorded_at
        FROM attendances a
        JOIN students s ON a.student_id = s.id
        JOIN batches  b ON a.batch_id   = b.id
        WHERE 1=1
    ';
    $params = [];

    if ($batchId) {
        $sql .= ' AND a.batch_id = :batch_id';
        $params[':batch_id'] = $batchId;
    }
    if ($dateFrom && $validDate($dateFrom)) {
        $sql .= ' AND a.attendance_date >= :date_from';
        $params[':date_from'] = $dateFrom;
    }
    if ($dateTo && $validDate($dateTo)) {
        $sql .= ' AND a.attendance_date <= :date_to';
        $params[':date_to'] = $dateTo;
    }
    if ($status !== '') {
        $sql .= ' AND a.status = :status';
        $params[':status'] = $status;
    }

    $sql .= ' ORDER BY a.attendance_date DESC, b.batch_name ASC, s.student_id_code ASC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Database error while exporting attendance records.']);
    exit;
}

// ── CSV Output ────────────────────────────────────────────
$filename = 'alamin_math_care_attendance_' . date('Y-m-d_His') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

// UTF-8 BOM for Excel Bengali rendering
fwrite($output, "\xEF\xBB\xBF");

// Column Headers
fputcsv($output, array_map('csv_safe', [
    'Date',
    'Batch Name',
    'Student ID',
    'Student Name',
    'Guardian Name',
    'Guardian Phone',
    'Class Level',
    'Attendance Status',
    'SMS Notification',
    'Recorded At',
]));

foreach ($rows as $row) {
    fputcsv($output, [
        csv_safe(format_date((string)($row['attendance_date'] ?? ''), 'Y-m-d')),
        csv_safe((string)($row['batch_name']          ?? '')),
        csv_safe((string)($row['student_id_code']     ?? '')),
        csv_safe((string)($row['student_name']        ?? '')),
        csv_safe((string)($row['guardian_name']       ?? '')),
        csv_safe((string)($row['guardian_phone']      ?? '')),
        csv_safe((string)($row['class_level']         ?? '')),
        csv_safe(ucfirst((string)($row['attendance_status'] ?? ''))),
        csv_safe(ucfirst((string)($row['sms_sent_status']   ?? 'not_sent'))),
        csv_safe(format_date((string)($row['recorded_at']   ?? ''), 'Y-m-d H:i')),
    ]);
}

fclose($output);
exit;
