<?php

/**
 * api/submit-feedback.php — Demo Feedback API Endpoint
 * Al Amin's Math Care | alaminmathcare.com
 *
 * Accepts POST JSON payload with demo feedback (1-5 star rating).
 * Requires lead ID from previous demo submission.
 * Stores feedback in demo_feedbacks table and updates leads table if needed.
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

// Rate limiting (3 per hour per IP for feedback submissions)
if (!rate_limit_check('feedback_submit', 3, 3600)) {
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
$leadId = $payload['lead_id'] ?? null;
$rating = $payload['rating'] ?? null;
$understandingLevel = $payload['understanding_level'] ?? '';
$comments = $payload['comments'] ?? '';

// Sanitize inputs
$understandingLevel = clean_input($understandingLevel);
$comments = clean_input($comments);

// Validation
if ($leadId === null || !is_numeric($leadId)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => __('feedback_error_lead_id')]);
    exit;
}

if ($rating === null || !is_numeric($rating) || $rating < 1 || $rating > 5) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => __('feedback_error_rating')]);
    exit;
}

// Prepare data for insertion
$data = [
    'lead_id' => (int) $leadId,
    'rating' => (int) $rating,
    'understanding_level' => $understandingLevel,
    'comments' => $comments,
    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
];

// Attempt to save to database
try {
    $pdo = db();
    
    // First verify the lead exists and is a demo request
    $checkStmt = $pdo->prepare('SELECT id, type FROM leads WHERE id = :lead_id AND is_deleted = 0 LIMIT 1');
    $checkStmt->execute([':lead_id' => $data['lead_id']]);
    $lead = $checkStmt->fetch();
    
    if (!$lead) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => __('feedback_error_lead_not_found')]);
        exit;
    }
    
    if ($lead['type'] !== 'demo_request') {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => __('feedback_error_invalid_lead_type')]);
        exit;
    }
    
    // Insert feedback with PDO Prepared Statement
    $stmt = $pdo->prepare('INSERT INTO demo_feedbacks (lead_id, rating, understanding_level, comments, ip_address) VALUES (:lead_id, :rating, :understanding_level, :comments, :ip_address)');
    
    $inserted = $stmt->execute([
        ':lead_id' => $data['lead_id'],
        ':rating' => $data['rating'],
        ':understanding_level' => $data['understanding_level'],
        ':comments' => $data['comments'],
        ':ip_address' => $data['ip_address']
    ]);
    
    if ($inserted) {
        $feedbackId = $pdo->lastInsertId();
        
        // Optionally update the lead with feedback status
        $updateStmt = $pdo->prepare('UPDATE leads SET status = :status WHERE id = :lead_id');
        $updateStmt->execute([':status' => 'demo_feedback_received', ':lead_id' => $data['lead_id']]);
        
        // Return success response
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true, 
            'message' => __('feedback_thank_you'),
            'data' => [
                'feedback_id' => $feedbackId,
                'lead_id' => $data['lead_id'],
                'rating' => $data['rating'],
                'timestamp' => date('c')
            ]
        ]);
    } else {
        throw new RuntimeException('Failed to insert feedback');
    }
    
} catch (Throwable $e) {
    // Fallback to file storage if database fails
    error_log('[DB Error] Feedback submission failed: ' . $e->getMessage());
    
    $feedbackData = [
        'lead_id' => $data['lead_id'],
        'rating' => $data['rating'],
        'understanding_level' => $data['understanding_level'],
        'comments' => $data['comments'],
        'ip_address' => $data['ip_address'],
        'created_at' => date('c')
    ];
    
    if (save_feedback_fallback($feedbackData)) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true, 
            'message' => __('feedback_thank_you'),
            'data' => [
                'feedback_id' => null, // Unknown in fallback mode
                'lead_id' => $data['lead_id'],
                'rating' => $data['rating'],
                'timestamp' => date('c')
            ]
        ]);
    } else {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => __('error_generic')]);
    }
}

/**
 * Save feedback to JSON file as fallback.
 *
 * @param array $feedbackData Feedback data
 * @return bool True on success
 */
function save_feedback_fallback(array $feedbackData): bool
{
    $dir = __DIR__ . '/../storage/feedback';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $file  = $dir . '/feedback_fallback.json';
    $feedbacks = [];
    if (is_file($file)) {
        $feedbacks = json_decode((string) file_get_contents($file), true) ?: [];
    }
    $feedbackData['created_at'] = date('c');
    $feedbacks[] = $feedbackData;
    return file_put_contents($file, json_encode($feedbacks, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}