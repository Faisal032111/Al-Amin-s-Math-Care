<?php

/**
 * includes/sms.php — SMS Gateway Helper
 * Al Amin's Math Care | alaminmathcare.com
 *
 * Centralized SMS sending with rate limiting, daily quota, kill-switch, and logging.
 * All API endpoints should use these functions instead of direct SMS calls.
 */

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

// ──────────────────────────────────────────────────────
// SMS Configuration
// ──────────────────────────────────────────────────────

/**
 * Get SMS gateway configuration from app config.
 *
 * @return array Config array with keys: gateway_url, api_key, sender_id, daily_limit, enabled
 */
function sms_get_config(): array
{
    $config = app_config();
    return [
        'gateway_url'  => $config['sms_gateway_url'] ?? '',
        'api_key'      => $config['sms_api_key'] ?? '',
        'sender_id'    => $config['sms_sender_id'] ?? 'AlAmMath',
        'daily_limit'  => (int) ($config['sms_daily_limit'] ?? 500),
        'enabled'      => !empty($config['sms_gateway_url']) && !empty($config['sms_api_key'])
    ];
}

// ──────────────────────────────────────────────────────
// Core SMS Sending Function
// ──────────────────────────────────────────────────────

/**
 * Send SMS via configured gateway.
 *
 * @param string $phone      Recipient phone number (11-digit BD format: 01XXXXXXXXX)
 * @param string $message    SMS message content
 * @param string $type       Message type: 'lead', 'demo', 'feedback', 'batch_change', 'attendance', 'fee', 'custom'
 * @param array  $meta       Optional metadata for logging (lead_id, student_id, batch_id, etc.)
 * @return array             ['success' => bool, 'message' => string, 'response' => array|null]
 */
function sms_send(string $phone, string $message, string $type = 'custom', array $meta = []): array
{
    $cfg = sms_get_config();

    // Kill-switch check
    if (!$cfg['enabled']) {
        return ['success' => false, 'message' => 'SMS gateway not configured', 'response' => null];
    }

    // Validate phone
    $cleanPhone = sms_sanitize_phone($phone);
    if (!sms_is_valid_bd_phone($cleanPhone)) {
        return ['success' => false, 'message' => 'Invalid Bangladesh phone number', 'response' => null];
    }

    // Rate limiting: per-IP is handled at API level, here we check daily quota
    if (!sms_check_daily_quota($cfg['daily_limit'])) {
        return ['success' => false, 'message' => 'Daily SMS limit exceeded', 'response' => null];
    }

    // Prepare payload
    $data = [
        'recipient' => '+' . $cleanPhone,
        'message'   => $message,
        'sender_id' => $cfg['sender_id'],
        'type'      => 'sms'
    ];

    // Send via cURL
    $ch = curl_init($cfg['gateway_url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $cfg['api_key'],
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);

    curl_close($ch);

    $success = false;
    $responseData = null;
    $errorMsg = '';

    if ($curlError) {
        $errorMsg = 'cURL error: ' . $curlError;
    } else {
        $responseData = json_decode($response, true);
        if ($httpCode === 200 && ($responseData['success'] ?? false)) {
            $success = true;
        } else {
            $errorMsg = 'Gateway error: ' . ($responseData['message'] ?? 'HTTP ' . $httpCode);
        }
    }

    // Log the attempt
    sms_log_attempt($cleanPhone, $message, $type, $success, $errorMsg, $responseData, $meta);

    // Increment daily counter on success
    if ($success) {
        sms_increment_daily_quota();
    }

    return ['success' => $success, 'message' => $success ? 'SMS sent' : $errorMsg, 'response' => $responseData];
}

// ──────────────────────────────────────────────────────
// Convenience Functions for Common Message Types
// ──────────────────────────────────────────────────────

/**
 * Send admission/demo lead confirmation to guardian.
 */
function sms_send_lead_confirmation(string $guardianPhone, string $studentName, string $classLevel): array
{
    $message = "Thank you! Admission enquiry for {$studentName} ({$classLevel}) received. Our team will call within 24 hours. Al Amin's Math Care.";
    return sms_send($guardianPhone, $message, 'lead', ['student_name' => $studentName]);
}

/**
 * Send demo class booking confirmation.
 */
function sms_send_demo_confirmation(string $guardianPhone, string $studentName, string $preferredDate): array
{
    $message = "Free demo class booked for {$studentName} on {$preferredDate}. We'll call to confirm timing. Al Amin's Math Care.";
    return sms_send($guardianPhone, $message, 'demo', ['student_name' => $studentName, 'date' => $preferredDate]);
}

/**
 * Send batch change request notification to admin and guardian.
 */
function sms_send_batch_change_notification(string $adminPhone, string $guardianPhone, string $studentId, string $targetBatch, string $reason): array
{
    $adminMsg = "Batch change request: Student {$studentId} → {$targetBatch}. Reason: {$reason}. Call to confirm.";
    $guardianMsg = "Your batch change request for {$targetBatch} received. Admin will review and call back. Al Amin's Math Care.";

    $r1 = sms_send($adminPhone, $adminMsg, 'batch_change', ['student_id' => $studentId, 'batch' => $targetBatch, 'role' => 'admin']);
    $r2 = sms_send($guardianPhone, $guardianMsg, 'batch_change', ['student_id' => $studentId, 'batch' => $targetBatch, 'role' => 'guardian']);

    return ['success' => $r1['success'] && $r2['success'], 'message' => $r1['message'] . ' | ' . $r2['message']];
}

/**
 * Send attendance alert to guardian.
 */
function sms_send_attendance_alert(string $guardianPhone, string $studentName, string $batchName, string $date, string $status): array
{
    $statusText = $status === 'absent' ? 'absent' : ($status === 'late' ? 'late' : 'present');
    $message = "Attendance alert: {$studentName} was {$statusText} in {$batchName} on {$date}. Al Amin's Math Care.";
    return sms_send($guardianPhone, $message, 'attendance', ['student_name' => $studentName, 'batch' => $batchName, 'date' => $date, 'status' => $status]);
}

/**
 * Send fee due/paid notification.
 */
function sms_send_fee_notification(string $guardianPhone, string $studentName, float $amount, string $status, string $month = ''): array
{
    $monthText = $month ? " for {$month}" : '';
    $statusText = $status === 'due' ? 'due' : 'paid';
    $message = "Fee {$statusText}: BDT {$amount}{$monthText} for {$studentName}. Al Amin's Math Care.";
    return sms_send($guardianPhone, $message, 'fee', ['student_name' => $studentName, 'amount' => $amount, 'month' => $month, 'status' => $status]);
}

/**
 * Send result published notification.
 */
function sms_send_result_notification(string $guardianPhone, string $studentName, string $examTitle, string $rank = ''): array
{
    $rankText = $rank ? " Rank: {$rank}" : '';
    $message = "Result published: {$studentName} - {$examTitle}{$rankText}. Check portal. Al Amin's Math Care.";
    return sms_send($guardianPhone, $message, 'result', ['student_name' => $studentName, 'exam' => $examTitle, 'rank' => $rank]);
}

// ──────────────────────────────────────────────────────
// Internal Helpers
// ──────────────────────────────────────────────────────

/**
 * Sanitize phone number to digits only, add 88 prefix if needed.
 *
 * @param string $phone Raw phone number
 * @return string Cleaned number with 88 prefix
 */
function sms_sanitize_phone(string $phone): string
{
    $digits = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
        $digits = '88' . $digits;
    }
    return $digits;
}

/**
 * Validate Bangladesh mobile number format.
 *
 * @param string $phone Cleaned phone number (with 88 prefix)
 * @return bool
 */
function sms_is_valid_bd_phone(string $phone): bool
{
    // With 88 prefix: 8801XXXXXXXXX (13 digits)
    // Without prefix: 01XXXXXXXXX (11 digits)
    return (bool) preg_match('/^(88)?01[3-9]\d{8}$/', $phone);
}

/**
 * Check if daily SMS quota has been exceeded.
 *
 * @param int $limit Daily limit
 * @return bool True if within limit
 */
function sms_check_daily_quota(int $limit): bool
{
    $usage = sms_get_daily_usage();
    return $usage < $limit;
}

/**
 * Get current daily SMS usage count.
 *
 * @return int Usage count
 */
function sms_get_daily_usage(): int
{
    $cacheFile = __DIR__ . '/../storage/cache/sms_usage_' . date('Y-m-d') . '.txt';

    if (file_exists($cacheFile)) {
        $content = file_get_contents($cacheFile);
        return (int) $content;
    }

    return 0;
}

/**
 * Increment daily SMS usage counter.
 */
function sms_increment_daily_quota(): void
{
    $usage = sms_get_daily_usage() + 1;
    $cacheDir = __DIR__ . '/../storage/cache';
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    $usageFile = $cacheDir . '/sms_usage_' . date('Y-m-d') . '.txt';
    file_put_contents($usageFile, (string) $usage, LOCK_EX);
}

/**
 * Log SMS attempt to daily JSON log file.
 *
 * @param string $phone       Recipient phone
 * @param string $message     Message content
 * @param string $type        Message type
 * @param bool   $success     Whether sent successfully
 * @param string $error       Error message if failed
 * @param array  $response    Gateway response
 * @param array  $meta        Additional metadata
 */
function sms_log_attempt(string $phone, string $message, string $type, bool $success, string $error, ?array $response, array $meta): void
{
    $cacheDir = __DIR__ . '/../storage/cache';
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }

    $logEntry = [
        'date'      => date('Y-m-d'),
        'time'      => date('H:i:s'),
        'phone'     => $phone,
        'type'      => $type,
        'message'   => $message,
        'success'   => $success,
        'error'     => $error ?: null,
        'gateway'   => $response,
        'meta'      => $meta
    ];

    $logFile = $cacheDir . '/sms_log_' . date('Y-m-d') . '.json';
    $logData = [];

    if (file_exists($logFile)) {
        $logData = json_decode(file_get_contents($logFile), true) ?: [];
    }

    $logData[] = $logEntry;

    // Keep only last 2000 entries
    if (count($logData) > 2000) {
        $logData = array_slice($logData, -2000);
    }

    file_put_contents($logFile, json_encode($logData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

// ──────────────────────────────────────────────────────
// Admin Utilities
// ──────────────────────────────────────────────────────

/**
 * Get SMS usage statistics for admin dashboard.
 *
 * @param string $date Optional date (Y-m-d), defaults to today
 * @return array Stats: total, success, failed, by_type
 */
function sms_get_stats(string $date = ''): array
{
    $date = $date ?: date('Y-m-d');
    $cacheDir = __DIR__ . '/../storage/cache';

    // Daily usage
    $usageFile = $cacheDir . '/sms_usage_' . $date . '.txt';
    $total = file_exists($usageFile) ? (int) file_get_contents($usageFile) : 0;

    // Detailed log
    $logFile = $cacheDir . '/sms_log_' . $date . '.json';
    $logs = file_exists($logFile) ? (json_decode(file_get_contents($logFile), true) ?: []) : [];

    $success = 0;
    $failed = 0;
    $byType = [];

    foreach ($logs as $log) {
        if ($log['success']) {
            $success++;
        } else {
            $failed++;
        }
        $type = $log['type'] ?? 'unknown';
        if (!isset($byType[$type])) $byType[$type] = ['total' => 0, 'success' => 0, 'failed' => 0];
        $byType[$type]['total']++;
        if ($log['success']) $byType[$type]['success']++;
        else $byType[$type]['failed']++;
    }

    return [
        'date'      => $date,
        'total'     => $total,
        'success'   => $success,
        'failed'    => $failed,
        'by_type'   => $byType,
        'quota_used' => ($total / (app_config()['sms_daily_limit'] ?? 500)) * 100
    ];
}

/**
 * Get recent SMS logs for admin review.
 *
 * @param int $limit Max records
 * @return array
 */
function sms_get_recent_logs(int $limit = 100): array
{
    $cacheDir = __DIR__ . '/../storage/cache';
    $allLogs = [];

    // Get logs from last 7 days
    for ($i = 0; $i < 7; $i++) {
        $date = date('Y-m-d', strtotime("-{$i} days"));
        $logFile = $cacheDir . '/sms_log_' . $date . '.json';
        if (file_exists($logFile)) {
            $logs = json_decode(file_get_contents($logFile), true) ?: [];
            $allLogs = array_merge($allLogs, $logs);
        }
    }

    // Sort by time desc
    usort($allLogs, function ($a, $b) {
        return ($b['time'] ?? '') <=> ($a['time'] ?? '');
    });

    return array_slice($allLogs, 0, $limit);
}

/**
 * Reset daily SMS usage (admin only, emergency).
 */
function sms_reset_daily_quota(): bool
{
    $usageFile = __DIR__ . '/../storage/cache/sms_usage_' . date('Y-m-d') . '.txt';
    if (file_exists($usageFile)) {
        return unlink($usageFile);
    }
    return true;
}

/**
 * Get SMS gateway configuration status.
 *
 * @return array
 */
function sms_get_gateway_status(): array
{
    $cfg = sms_get_config();
    return [
        'configured' => $cfg['enabled'],
        'gateway_url' => $cfg['enabled'] ? parse_url($cfg['gateway_url'], PHP_URL_HOST) : null,
        'sender_id' => $cfg['sender_id'],
        'daily_limit' => $cfg['daily_limit'],
        'today_usage' => sms_get_daily_usage(),
        'quota_remaining' => max(0, $cfg['daily_limit'] - sms_get_daily_usage())
    ];
}