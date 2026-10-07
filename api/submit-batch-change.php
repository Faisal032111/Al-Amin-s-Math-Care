<?php

/**
 * api/submit-batch-change.php — Batch Change Request API Endpoint
 * Al Amin's Math Care | alaminmathcare.com
 *
 * Accepts POST JSON payload with batch change request.
 * Saves to batch_change_requests table and notifies admin.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/helpers.php';

// Ensure this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => __('api_method_not_allowed')]);
    exit;
}

// CSRF token validation
verify_csrf_token();

// Rate limiting (5 per hour per IP)
if (!rate_limit_check('batch_change', 5, 3600)) {
    http_response_code(429);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => __('form_rate_limit')]);
    exit;
}

// Parse JSON payload
$payload = json_decode(file_get_contents('php://input'), true);
if (!$payload || !is_array($payload)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => __('api_invalid_payload')]);
    exit;
}

// Extract required fields
$studentId = $payload['student_id'] ?? null;
$currentBatchId = $payload['current_batch_id'] ?? null;
$requestedBatchId = $payload['requested_batch_id'] ?? null;
$reason = $payload['reason'] ?? '';
$guardianPhone = $payload['guardian_phone'] ?? '';

// Sanitize inputs
$reason = clean_input($reason);
$guardianPhone = clean_input($guardianPhone);

// Validation
if ($studentId === null || !is_numeric($studentId)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => __('batch_change_error_student_id')]);
    exit;
}

if ($requestedBatchId === null || !is_numeric($requestedBatchId)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => __('batch_change_error_batch_id')]);
    exit;
}

if (empty($reason)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => __('batch_change_error_reason')]);
    exit;
}

// Verify requested batch exists and is active
try {
    $pdo = db();
    
    $batchCheck = $pdo->prepare('SELECT id, batch_name, available_seats FROM batches WHERE id = :bid AND is_deleted = 0 AND is_active = 1 LIMIT 1');
    $batchCheck->execute([':bid' => $requestedBatchId]);
    $targetBatch = $batchCheck->fetch();
    
    if (!$targetBatch) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => __('batch_change_error_batch_not_found')]);
        exit;
    }
    
    // Check if requested batch has available seats
    if ((int) $targetBatch['available_seats'] <= 0) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => __('batch_change_error_batch_full')]);
        exit;
    }
    
    // Prepare data for insertion
    $data = [
        'student_id' => (int) $studentId,
        'current_batch_id' => $currentBatchId ? (int) $currentBatchId : null,
        'requested_batch_id' => (int) $requestedBatchId,
        'reason' => $reason,
        'guardian_phone' => $guardianPhone,
        'status' => 'pending',
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
    ];
    
    // Insert batch change request
    $stmt = $pdo->prepare('INSERT INTO batch_change_requests (student_id, current_batch_id, requested_batch_id, reason, guardian_phone, status, ip_address) VALUES (:student_id, :current_batch_id, :requested_batch_id, :reason, :guardian_phone, :status, :ip_address)');
    
    $inserted = $stmt->execute([
        ':student_id' => $data['student_id'],
        ':current_batch_id' => $data['current_batch_id'],
        ':requested_batch_id' => $data['requested_batch_id'],
        ':reason' => $data['reason'],
        ':guardian_phone' => $data['guardian_phone'],
        ':status' => $data['status'],
        ':ip_address' => $data['ip_address']
    ]);
    
    if ($inserted) {
        $requestId = $pdo->lastInsertId();
        
        // Notify admin via SMS if configured
        if (!empty($guardianPhone)) {
            try {
                trigger_batch_change_sms_notification($data, $targetBatch);
            } catch (Throwable $smsError) {
                error_log('[SMS Error] Batch change request ID: ' . $requestId . ' - ' . $smsError->getMessage());
            }
        }
        
        // Return success response
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => __('batch_change_submitted'),
            'data' => [
                'request_id' => $requestId,
                'target_batch' => $targetBatch['batch_name'],
                'status' => 'pending',
                'timestamp' => date('c')
            ]
        ]);
    } else {
        throw new RuntimeException('Failed to insert batch change request');
    }
    
} catch (Throwable $e) {
    error_log('[DB Error] Batch change request failed: ' . $e->getMessage());
    
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => __('error_generic')]);
}

/**
 * Trigger SMS notification for batch change request.
 */
function trigger_batch_change_sms_notification(array $requestData, array $targetBatch): void
{
    $config = app_config();
    
    if (empty($config['sms_gateway_url']) || empty($config['sms_api_key'])) {
        throw new RuntimeException('SMS gateway not configured');
    }
    
    $dailyLimit = $config['sms_daily_limit'] ?? 500;
    $usage = get_daily_sms_usage_batch_change();
    if ($usage >= $dailyLimit) {
        throw new RuntimeException('Daily SMS limit exceeded');
    }
    
    $studentId = $requestData['student_id'];
    $guardianPhone = $requestData['guardian_phone'];
    $targetBatchName = $targetBatch['batch_name'];
    $reason = $requestData['reason'];
    
    // Send notification to admin
    $adminPhone = $config['phone_primary'] ?? '01520102248';
    $adminMsg = "Batch change request: Student ID {$studentId} - Requesting: {$targetBatchName} - Reason: {$reason}. Call to confirm.";
    
    send_sms_batch_change($adminPhone, $adminMsg, $config['sms_sender_id'] ?? 'AlAmMath');
    
    // Send confirmation to guardian
    $guardianMsg = "Your batch change request for {$targetBatchName} is received. Admin will review and confirm soon. Al Amin's Math Care.";
    
    send_sms_batch_change($guardianPhone, $guardianMsg, $config['sms_sender_id'] ?? 'AlAmMath');
    
    // Log SMS usage
    log_sms_usage_batch_change($adminPhone, $adminMsg, null, $requestData);
    log_sms_usage_batch_change($guardianPhone, $guardianMsg, null, $requestData);
}

/**
 * Send SMS via configured gateway.
 */
function send_sms_batch_change(string $phone, string $message, string $senderId): ?array
{
    $config = app_config();
    $url = $config['sms_gateway_url'];
    $apiKey = $config['sms_api_key'];
    
    if (empty($url) || empty($apiKey)) {
        throw new RuntimeException('SMS gateway configuration missing');
    }
    
    // Sanitize phone number
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phone) === 11 && str_starts_with($phone, '0')) {
        $phone = '88' . $phone;
    }
    
    $data = [
        'recipient' => '+' . $phone,
        'message' => $message,
        'sender_id' => $senderId,
        'type' => 'sms'
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    
    curl_close($ch);
    
    if ($curlError) {
        throw new RuntimeException('SMS gateway error: ' . $curlError);
    }
    
    $responseData = json_decode($response, true);
    
    if ($httpCode !== 200 || !($responseData['success'] ?? false)) {
        throw new RuntimeException('SMS gateway returned error: ' . ($responseData['message'] ?? 'Unknown error'));
    }
    
    return $responseData;
}

/**
 * Get daily SMS usage count.
 */
function get_daily_sms_usage_batch_change(): int
{
    $cacheFile = __DIR__ . '/../storage/cache/sms_usage_' . date('Y-m-d') . '.txt';
    
    if (file_exists($cacheFile)) {
        $content = file_get_contents($cacheFile);
        return (int) $content;
    }
    
    return 0;
}

/**
 * Log SMS usage to cache.
 */
function log_sms_usage_batch_change(string $phone, string $message, ?array $response, array $requestData): void
{
    $config = app_config();
    $dailyLimit = $config['sms_daily_limit'] ?? 500;
    
    $usage = get_daily_sms_usage_batch_change();
    if ($usage >= $dailyLimit) {
        return;
    }
    
    $usage++;
    
    $logEntry = [
        'date' => date('Y-m-d'),
        'phone' => $phone,
        'message' => $message,
        'response' => $response,
        'request_id' => $requestData['id'] ?? null,
        'request_type' => 'batch_change',
        'timestamp' => date('c')
    ];
    
    $cacheDir = __DIR__ . '/../storage/cache';
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    
    $cacheFile = $cacheDir . '/sms_usage_' . date('Y-m-d') . '.json';
    
    $logData = [];
    if (file_exists($cacheFile)) {
        $logData = json_decode(file_get_contents($cacheFile), true) ?: [];
    }
    
    $logData[] = $logEntry;
    
    if (count($logData) > 1000) {
        $logData = array_slice($logData, -1000);
    }
    
    file_put_contents($cacheFile, json_encode($logData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    
    $usageFile = $cacheDir . '/sms_usage_' . date('Y-m-d') . '.txt';
    file_put_contents($usageFile, (string) $usage, LOCK_EX);
}