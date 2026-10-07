<?php

/**
 * api/export-students.php — Secure Student & Guardian CSV/Excel Exporter
 * Al Amin's Math Care | alaminmathcare.com
 *
 * Exports student records, guardian contacts, enrollments, and payment status.
 * Protected against CSV Formula Injection (CWE-1236) via csv_safe().
 * Requires admin authentication.
 */

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

// Auth Guard: Admin access required
if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access. Please log in as an administrator.'
    ]);
    exit;
}

// Fetch query filters (e.g. batch_id, status, class_level)
$batchId    = !empty($_GET['batch_id']) ? (int)$_GET['batch_id'] : null;
$classLevel = clean_input($_GET['class_level'] ?? '');
$status     = clean_input($_GET['status'] ?? '');

try {
    $pdo = db();

    $sql = '
        SELECT 
            s.id,
            s.student_id_code,
            s.name AS student_name,
            s.guardian_name,
            s.guardian_phone,
            s.class_level,
            s.school_college,
            s.status AS student_status,
            s.created_at AS registered_at,
            b.batch_name,
            c.title_en AS course_title,
            e.total_fee,
            e.paid_amount,
            e.due_amount,
            e.payment_status
        FROM students s
        LEFT JOIN enrollments e ON s.id = e.student_id AND e.status = "active"
        LEFT JOIN batches b ON e.batch_id = b.id
        LEFT JOIN courses c ON b.course_id = c.id
        WHERE s.is_deleted = 0
    ';

    $params = [];

    if ($batchId) {
        $sql .= ' AND e.batch_id = :batch_id';
        $params[':batch_id'] = $batchId;
    }

    if ($classLevel !== '') {
        $sql .= ' AND s.class_level = :class_level';
        $params[':class_level'] = $classLevel;
    }

    if ($status !== '') {
        $sql .= ' AND s.status = :status';
        $params[':status'] = $status;
    }

    $sql .= ' ORDER BY s.id DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $students = $stmt->fetchAll();

} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Database error while exporting student records.'
    ]);
    exit;
}

// Set CSV Headers for Direct File Download
$filename = 'alamin_math_care_students_' . date('Y-m-d_His') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// Open PHP Output Stream
$output = fopen('php://output', 'w');

// Write UTF-8 BOM so Microsoft Excel renders Unicode / Bengali characters properly
fwrite($output, "\xEF\xBB\xBF");

// Define CSV Column Headers
$headers = [
    'Student ID',
    'Student Name',
    'Guardian Name',
    'Guardian Phone',
    'Class Level',
    'School / College',
    'Enrolled Course',
    'Batch Name',
    'Total Fee (BDT)',
    'Paid Amount (BDT)',
    'Due Amount (BDT)',
    'Payment Status',
    'Student Status',
    'Registration Date'
];

fputcsv($output, array_map('csv_safe', $headers));

// Write Data Rows
foreach ($students as $row) {
    $line = [
        csv_safe((string)($row['student_id_code'] ?? 'N/A')),
        csv_safe((string)($row['student_name'] ?? '')),
        csv_safe((string)($row['guardian_name'] ?? '')),
        csv_safe((string)($row['guardian_phone'] ?? '')),
        csv_safe((string)($row['class_level'] ?? '')),
        csv_safe((string)($row['school_college'] ?? '')),
        csv_safe((string)($row['course_title'] ?? 'N/A')),
        csv_safe((string)($row['batch_name'] ?? 'N/A')),
        csv_safe(number_format((float)($row['total_fee'] ?? 0), 2, '.', '')),
        csv_safe(number_format((float)($row['paid_amount'] ?? 0), 2, '.', '')),
        csv_safe(number_format((float)($row['due_amount'] ?? 0), 2, '.', '')),
        csv_safe(ucfirst((string)($row['payment_status'] ?? 'unpaid'))),
        csv_safe(ucfirst((string)($row['student_status'] ?? 'active'))),
        csv_safe(format_date((string)($row['registered_at'] ?? 'now'), 'Y-m-d H:i'))
    ];

    fputcsv($output, $line);
}

fclose($output);
exit;
