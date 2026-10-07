<?php

/**
 * api/submit-demo.php — Demo Class Registration API Endpoint
 * Al Amin's Math Care | alaminmathcare.com
 *
 * Accepts POST JSON payload with free demo class request.
 * Returns 200 with JSON success response on success, or appropriate error status.
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

// Rate limiting (5 per hour per IP for demo submissions)
if (!rate_limit_check('demo_submit', 5, 3600)) {
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
$studentName = $payload['student_name'] ?? '';
$guardianPhone = $payload['guardian_phone'] ?? '';
$whatsappNum = $payload['whatsapp_number'] ?? $guardianPhone;
$classLevel = $payload['class_level'] ?? '';
$courseId = !empty($payload['course_id']) ? (int) $payload['course_id'] : null;
$preferredDate = !empty($payload['preferred_date']) ? $payload['preferred_date'] : null;
$message = $payload['message'] ?? '';

// Sanitize all inputs
$studentName = clean_input($studentName);
$guardianPhone = clean_input($guardianPhone);
$whatsappNum = clean_input($whatsappNum);
$classLevel = clean_input($classLevel);
$preferredDate = clean_input($preferredDate);
$message = clean_input($message);

// Validation
if (empty($studentName) || empty($guardianPhone) || empty($classLevel)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => __('form_error')]);
    exit;
}

if (!is_valid_bd_phone($guardianPhone)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => __('form_phone_error')]);
    exit;
}

// Prepare data for insertion
$data = [
    'type'            => 'demo_request',
    'student_name'    => $studentName,
    'guardian_phone'  => $guardianPhone,
    'whatsapp_number' => $whatsappNum,
    'class_level'     => $classLevel,
    'course_id'       => $courseId,
    'preferred_date'  => $preferredDate,
    'message'         => $message,
    'ip_address'      => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
    'status'          => 'new'
];

// Attempt to save to database
try {
    $pdo = db();
    
    // Insert with PDO Prepared Statement for SQL injection protection
    $stmt = $pdo->prepare('INSERT INTO leads (type, student_name, guardian_phone, whatsapp_number, class_level, course_id, preferred_date, message, ip_address, status) VALUES (:type, :student_name, :guardian_phone, :whatsapp_number, :class_level, :course_id, :preferred_date, :message, :ip_address, :status)');
    
    $inserted = $stmt->execute([
        ':type' => $data['type'],
        ':student_name' => $data['student_name'],
        ':guardian_phone' => $data['guardian_phone'],
        ':whatsapp_number' => $data['whatsapp_number'],
        ':class_level' => $data['class_level'],
        ':course_id' => $data['course_id'],
        ':preferred_date' => $data['preferred_date'],
        ':message' => $data['message'],
        ':ip_address' => $data['ip_address'],
        ':status' => $data['status']
    ]);
    
    $leadId = $pdo->lastInsertId();
    $data['id'] = $leadId;
    
    // Trigger SMS confirmation
    if (!empty($data['guardian_phone'])) {
        try {
            trigger_demo_sms_confirmation($data);
        } catch (Throwable $smsError) {
            // Log SMS error but don't fail the entire operation
            error_log('[SMS Error] Demo submission ID: ' . ($leadId ?? 'unknown') . ' - ' . $smsError->getMessage());
        }
    }
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => __('form_demo_success'),
        'data' => [
            'id' => $leadId,
            'type' => 'demo_request',
            'timestamp' => date('c'),
            'whatsapp_link' => whatsapp_link($data['whatsapp_number'] ?? $data['guardian_phone'], __('whatsapp_prefill', ['course' => 'Free Demo Class']))
        ]
    ]);
    
} catch (Throwable $e) {
    // Fallback to file storage if database fails
    error_log('[DB Error] Demo submission failed: ' . $e->getMessage());
    
    if (save_lead($data)) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true, 
            'message' => __('form_demo_success'),
            'data' => [
                'id' => null, // Unknown in fallback mode
                'type' => 'demo_request',
                'timestamp' => date('c'),
                'whatsapp_link' => whatsapp_link($data['whatsapp_number'] ?? $data['guardian_phone'], __('whatsapp_prefill', ['course' => 'Free Demo Class']))
            ]
        ]);
    } else {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => __('error_generic')]);
    }
}

/**
 * Trigger SMS confirmation for demo class registration.
 *
 * Sends two messages:
 * 1. Confirmation SMS to the guardian's phone
 * 2. Internal notification to the head teacher/receptionist
 */
function trigger_demo_sms_confirmation(array $leadData): void
{
    $config = app_config();
    
    // Check if SMS gateway is configured
    if (empty($config['sms_gateway_url']) || empty($config['sms_api_key'])) {
        throw new RuntimeException('SMS gateway not configured');
    }
    
    // Check daily SMS limit
    $dailyLimit = $config['sms_daily_limit'] ?? 500;
    $usage = get_daily_sms_usage_demo();
    if ($usage >= $dailyLimit) {
        throw new RuntimeException('Daily SMS limit exceeded');
    }
    
    $studentName = $leadData['student_name'];
    $guardianPhone = $leadData['guardian_phone'];
    $preferredDate = $leadData['preferred_date'] ?? date('Y-m-d', strtotime('+2 days'));
    
    // Send confirmation SMS to guardian
    $confirmationMsg = "Thank you! Your free demo class request for {$studentName} is received. Our team will call within 24 hours to confirm your slot. Al Amin's Math Care.";
    
    send_sms_demo(
        $guardianPhone,
        $confirmationMsg,
        $config['sms_sender_id'] ?? 'AlAmMath'
    );
    
    // Send internal notification to admin/head teacher
    $internalPhone = $config['phone_primary'] ?? '01520102248';
    $internalMsg = "New demo booking: {$studentName} - Guardian: {$guardianPhone} - Date: {$preferredDate}. Call to confirm.";
    
    try {
        send_sms_demo($internalPhone, $internalMsg, $config['sms_sender_id'] ?? 'AlAmMath', false);
    } catch (Throwable $e) {
        // Internal notification SMS failure should not block the confirmation
        error_log('[SMS Error] Internal demo notification failed: ' . $e->getMessage());
    }
    
    // Log both SMS sends
    log_sms_usage_demo($guardianPhone, $confirmationMsg, null, $leadData);
    log_sms_usage_demo($internalPhone, $internalMsg, null, $leadData);
}

/**
 * Send SMS via configured gateway (demo-specific version).
 */
function send_sms_demo(string $phone, string $message, string $senderId, bool $incrementUsage = true): ?array
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
    
    // Prepare request data
    $data = [
        'recipient' => '+' . $phone,
        'message' => $message,
        'sender_id' => $senderId,
        'type' => 'sms'
    ];
    
    if ($incrementUsage) {
        $dailyLimit = $config['sms_daily_limit'] ?? 500;
        $usage = get_daily_sms_usage_demo();
        if ($usage >= $dailyLimit) {
            throw new RuntimeException('Daily SMS limit exceeded');
        }
    }
    
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
function get_daily_sms_usage_demo(): int
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
function log_sms_usage_demo(string $phone, string $message, ?array $response, array $leadData): void
{
    $config = app_config();
    $dailyLimit = $config['sms_daily_limit'] ?? 500;
    
    $usage = get_daily_sms_usage_demo();
    if ($usage >= $dailyLimit) {
        return;
    }
    
    $usage++;
    
    $logEntry = [
        'date' => date('Y-m-d'),
        'phone' => $phone,
        'message' => $message,
        'response' => $response,
        'lead_id' => $leadData['id'] ?? null,
        'lead_type' => 'demo_request',
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
    
    // Update daily usage counter
    $usageFile = $cacheDir . '/sms_usage_' . date('Y-m-d') . '.txt';
    file_put_contents($usageFile, (string) $usage, LOCK_EX);
}