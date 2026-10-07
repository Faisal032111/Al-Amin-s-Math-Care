<?php

/**
 * api/export-payments.php — Secure Fee & Payment CSV/Excel Exporter
 * Al Amin's Math Care | alaminmathcare.com
 *
 * Exports monthly fee ledger and individual payment transaction records.
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
$type        = clean_input($_GET['type']    ?? 'ledger'); // ledger | transactions
$month       = clean_input($_GET['month']   ?? '');       // YYYY-MM
$batchId     = !empty($_GET['batch_id']) ? (int)$_GET['batch_id'] : null;
$statusFilter= clean_input($_GET['status']  ?? '');

try {
    $pdo = db();

    if ($type === 'transactions') {
        // ─── Payment Transaction Records (fee_records table) ─────────────
        $sql = '
            SELECT
                fr.receipt_no,
                fr.payment_date,
                fr.fee_month,
                s.student_id_code,
                s.name            AS student_name,
                s.guardian_phone,
                s.class_level,
                b.batch_name,
                fr.amount_paid,
                fr.payment_method,
                fr.transaction_reference,
                fr.remarks,
                fr.created_at     AS recorded_at
            FROM fee_records fr
            JOIN students s ON fr.student_id = s.id
            LEFT JOIN enrollments e ON s.id = e.student_id AND e.status = "active"
            LEFT JOIN batches b ON e.batch_id = b.id
            WHERE s.is_deleted = 0
        ';
        $params = [];

        if ($month !== '') {
            $sql .= ' AND fr.fee_month = :month';
            $params[':month'] = $month;
        }
        if ($batchId) {
            $sql .= ' AND b.id = :batch_id';
            $params[':batch_id'] = $batchId;
        }
        if ($statusFilter !== '') {
            $sql .= ' AND fr.payment_method = :pm';
            $params[':pm'] = $statusFilter;
        }

        $sql .= ' ORDER BY fr.payment_date DESC, fr.id DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        // ── CSV Headers for Transactions ──────────────────────────────────
        $filename = 'alamin_math_care_payment_transactions_' . date('Y-m-d_His') . '.csv';
        $headers  = [
            'Receipt No',
            'Payment Date',
            'Fee Month',
            'Student ID',
            'Student Name',
            'Guardian Phone',
            'Class Level',
            'Batch Name',
            'Amount Paid (BDT)',
            'Payment Method',
            'Transaction Reference',
            'Remarks',
            'Recorded At',
        ];

        $rowMapper = function (array $row): array {
            return [
                csv_safe((string)($row['receipt_no']             ?? '')),
                csv_safe(format_date((string)($row['payment_date']  ?? ''), 'Y-m-d')),
                csv_safe((string)($row['fee_month']              ?? '')),
                csv_safe((string)($row['student_id_code']        ?? '')),
                csv_safe((string)($row['student_name']           ?? '')),
                csv_safe((string)($row['guardian_phone']         ?? '')),
                csv_safe((string)($row['class_level']            ?? '')),
                csv_safe((string)($row['batch_name']             ?? 'N/A')),
                csv_safe(number_format((float)($row['amount_paid'] ?? 0), 2, '.', '')),
                csv_safe(ucfirst((string)($row['payment_method']   ?? ''))),
                csv_safe((string)($row['transaction_reference']  ?? '')),
                csv_safe((string)($row['remarks']                ?? '')),
                csv_safe(format_date((string)($row['recorded_at']  ?? ''), 'Y-m-d H:i')),
            ];
        };

    } else {
        // ─── Monthly Fee Ledger (student_fees table) ──────────────────────
        $selectedMonth = $month ?: date('Y-m');

        $sql = '
            SELECT
                s.student_id_code,
                s.name            AS student_name,
                s.guardian_name,
                s.guardian_phone,
                s.class_level,
                b.batch_name,
                sf.fee_month,
                sf.fee_amount,
                sf.paid_amount,
                sf.due_amount,
                sf.status         AS fee_status,
                sf.last_paid_date,
                (SELECT receipt_no FROM fee_records fr2
                 WHERE fr2.student_id = s.id
                   AND fr2.fee_month = :m2
                 ORDER BY fr2.id DESC LIMIT 1) AS latest_receipt
            FROM students s
            LEFT JOIN enrollments e ON s.id = e.student_id AND e.status = "active"
            LEFT JOIN batches b ON e.batch_id = b.id
            LEFT JOIN student_fees sf ON s.id = sf.student_id AND sf.fee_month = :m
            WHERE s.is_deleted = 0 AND s.status = "active"
        ';
        $params = [':m' => $selectedMonth, ':m2' => $selectedMonth];

        if ($batchId) {
            $sql .= ' AND b.id = :batch_id';
            $params[':batch_id'] = $batchId;
        }
        if ($statusFilter !== '') {
            if ($statusFilter === 'due') {
                $sql .= ' AND (sf.status = "due" OR sf.status IS NULL OR sf.status = "partial")';
            } else {
                $sql .= ' AND sf.status = :status';
                $params[':status'] = $statusFilter;
            }
        }

        $sql .= ' ORDER BY s.student_id_code ASC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        // ── CSV Headers for Ledger ────────────────────────────────────────
        $filename = 'alamin_math_care_fee_ledger_' . $selectedMonth . '_' . date('His') . '.csv';
        $headers  = [
            'Student ID',
            'Student Name',
            'Guardian Name',
            'Guardian Phone',
            'Class Level',
            'Batch Name',
            'Fee Month',
            'Fee Amount (BDT)',
            'Paid Amount (BDT)',
            'Due Amount (BDT)',
            'Payment Status',
            'Last Payment Date',
            'Latest Receipt No',
        ];

        $rowMapper = function (array $row): array {
            $feeAmt  = (float)($row['fee_amount']  ?? 0);
            $paidAmt = (float)($row['paid_amount'] ?? 0);
            $dueAmt  = max(0, $feeAmt - $paidAmt);
            return [
                csv_safe((string)($row['student_id_code'] ?? '')),
                csv_safe((string)($row['student_name']    ?? '')),
                csv_safe((string)($row['guardian_name']   ?? '')),
                csv_safe((string)($row['guardian_phone']  ?? '')),
                csv_safe((string)($row['class_level']     ?? '')),
                csv_safe((string)($row['batch_name']      ?? 'N/A')),
                csv_safe((string)($row['fee_month']       ?? '')),
                csv_safe(number_format($feeAmt,  2, '.', '')),
                csv_safe(number_format($paidAmt, 2, '.', '')),
                csv_safe(number_format($dueAmt,  2, '.', '')),
                csv_safe(ucfirst((string)($row['fee_status']      ?? 'due'))),
                csv_safe((string)($row['last_paid_date']  ?? 'N/A')),
                csv_safe((string)($row['latest_receipt']  ?? '')),
            ];
        };
    }

} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Database error while exporting payment records.']);
    exit;
}

// ── CSV Output ────────────────────────────────────────────
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');
fwrite($output, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel

fputcsv($output, array_map('csv_safe', $headers));
foreach ($rows as $row) {
    fputcsv($output, $rowMapper($row));
}

fclose($output);
exit;
