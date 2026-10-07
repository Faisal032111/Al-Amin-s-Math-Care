<?php

/**
 * api/submit-lead.php — Lead Submission API Endpoint
 * Al Amin's Math Care | alaminmathcare.com
 *
 * Accepts POST JSON payload with admission enquiry data.
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

// Rate limiting (10 per hour per IP)
if (!rate_limit_check('lead_submit', 10, 3600)) {
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
$type = $payload['type'] ?? 'admission_enquiry';
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
    'type'            => $type,
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
    
    // Trigger SMS notification if a phone number is present
    if (!empty($data['guardian_phone'])) {
        try {
            trigger_lead_sms_notification($data);
        } catch (Throwable $smsError) {
            // Log SMS error but don't fail the entire operation
            error_log('[SMS Error] Lead submission ID: ' . ($pdo->lastInsertId() ?? 'unknown') . ' - ' . $smsError->getMessage());
        }
    }
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => __('form_success'),
        'data' => [
            'id' => $pdo->lastInsertId() ?? null,
            'timestamp' => date('c'),
            'phone' => $data['guardian_phone']
        ]
    ]);
    
} catch (Throwable $e) {
    // Fallback to file storage if database fails
    error_log('[DB Error] Lead submission failed: ' . $e->getMessage());
    
    if (save_lead($data)) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true, 
            'message' => __('form_success'),
            'data' => [
                'id' => null, // Unknown in fallback mode
                'timestamp' => date('c'),
                'phone' => $data['guardian_phone']
            ]
        ]);
    } else {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => __('error_generic')]);
    }
}

/**
 * Trigger SMS notification for new lead submission.
 *
 * @param array $leadData Lead data array
 * @throws RuntimeException if SMS service is unavailable or fails
 */
function trigger_lead_sms_notification(array $leadData): void
{
    $config = app_config();
    
    // Check if SMS gateway is configured
    if (empty($config['sms_gateway_url']) || empty($config['sms_api_key'])) {
        throw new RuntimeException('SMS gateway not configured');
    }
    
    // Check daily SMS limit (increment and check)
    $dailyLimit = $config['sms_daily_limit'] ?? 500;
    $usage = get_daily_sms_usage();
    if ($usage >= $dailyLimit) {
        throw new RuntimeException('Daily SMS limit exceeded');
    }
    
    // Prepare SMS message
    $studentName = $leadData['student_name'];
    $guardianPhone = $leadData['guardian_phone'];
    $classLevel = $leadData['class_level'];
    $courseName = 'Mathematics Admission';
    $message = "New admission enquiry: {$studentName} - Class: {$classLevel} - Guardian: {$guardianPhone}";
    
    // Send SMS via API
    $response = send_sms(
        $guardianPhone,
        $message,
        $config['sms_sender_id'] ?? 'AlAmMath'
    );
    
    // Log the SMS send attempt
    log_sms_usage($guardianPhone, $message, $response, $leadData);
}

/**
 * Send SMS via configured gateway.
 *
 * @param string $phone Phone number
 * @param string $message SMS message content
 * @param string $senderId Sender ID
 * @return array|null Response data or null on failure
 */
function send_sms(string $phone, string $message, string $senderId): ?array
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
 *
 * @return int Current SMS usage count
 */
function get_daily_sms_usage(): int
{
    $config = app_config();
    $usageKey = 'sms_usage_' . date('Y-m-d');
    $cacheFile = __DIR__ . '/../../storage/cache/' . $usageKey . '.txt';
    
    if (file_exists($cacheFile)) {
        $content = file_get_contents($cacheFile);
        return (int) $content;
    }
    
    return 0;
}

/**
 * Log SMS usage to cache.
 *
 * @param string $phone Phone number
 * @param string $message SMS message
 * @param array|null $response SMS response
 * @param array $leadData Lead data
 */
function log_sms_usage(string $phone, string $message, ?array $response, array $leadData): void
{
    $config = app_config();
    $dailyLimit = $config['sms_daily_limit'] ?? 500;
    
    $usage = get_daily_sms_usage();
    if ($usage >= $dailyLimit) {
        return; // Don't log if at limit
    }
    
    $usage++;
    
    $logEntry = [
        'date' => date('Y-m-d'),
        'phone' => $phone,
        'message' => $message,
        'response' => $response,
        'lead_id' => $leadData['id'] ?? null,
        'timestamp' => date('c')
    ];
    
    $cacheDir = __DIR__ . '/../../storage/cache';
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    
    $cacheFile = $cacheDir . '/sms_usage_' . date('Y-m-d') . '.json';
    
    $logData = [];
    if (file_exists($cacheFile)) {
        $logData = json_decode(file_get_contents($cacheFile), true) ?: [];
    }
    
    $logData[] = $logEntry;
    
    // Keep only last 1000 entries
    if (count($logData) > 1000) {
        $logData = array_slice($logData, -1000);
    }
    
    file_put_contents($cacheFile, json_encode($logData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    
    // Update daily usage counter
    $usageFile = $cacheDir . '/sms_usage_' . date('Y-m-d') . '.txt';
    file_put_contents($usageFile, (string) $usage, LOCK_EX);
}