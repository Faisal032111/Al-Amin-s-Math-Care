<?php

/**
 * includes/csrf.php — CSRF Token Protection
 * Al Amin's Math Care | alaminmathcare.com
 *
 * Usage in forms:
 *   <?= csrf_input() ?>
 *
 * Usage in POST handlers:
 *   verify_csrf_token();   // Aborts with 403 if invalid
 */

declare(strict_types=1);

// Ensure session is started before using CSRF functions
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate (or retrieve existing) CSRF token for the current session.
 * Token is a cryptographically secure random 64-character hex string.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Regenerate the CSRF token (call after login/logout).
 */
function csrf_regenerate(): void
{
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Return a hidden HTML input field containing the CSRF token.
 * Use inside every <form> that performs a POST action.
 */
function csrf_input(): string
{
    $token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="_csrf_token" value="' . $token . '">';
}

/**
 * Alias for csrf_input() — returns hidden CSRF input field.
 */
function csrf_field(): string
{
    return csrf_input();
}

/**
 * Verify the submitted CSRF token against the session token.
 * On failure: logs the attempt and sends HTTP 403.
 *
 * @param string $field  The POST field name (default: '_csrf_token')
 */
function verify_csrf_token(string $field = '_csrf_token'): void
{
    $submitted = $_POST[$field] ?? '';
    $expected  = $_SESSION['csrf_token'] ?? '';

    // hash_equals prevents timing attacks
    if ($submitted === '' || $expected === '' || !hash_equals($expected, $submitted)) {
        // Log the CSRF failure (IP + URI)
        $logFile = __DIR__ . '/../storage/logs/security.log';
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        error_log(
            '[' . date('Y-m-d H:i:s') . '] CSRF FAIL | IP: '
            . ($_SERVER['REMOTE_ADDR'] ?? 'unknown')
            . ' | URI: ' . ($_SERVER['REQUEST_URI'] ?? 'unknown') . PHP_EOL,
            3,
            $logFile
        );

        http_response_code(403);
        exit('403 Forbidden — Invalid security token. Please go back and try again.');
    }

    // ✅ After successful verification, regenerate for next request (double-submit prevention)
    csrf_regenerate();
}

/**
 * Verify CSRF token for JSON/API responses (returns JSON error instead of exiting with text).
 */
function verify_csrf_token_api(string $field = '_csrf_token'): void
{
    $submitted = $_POST[$field] ?? '';
    $expected  = $_SESSION['csrf_token'] ?? '';

    if ($submitted === '' || $expected === '' || !hash_equals($expected, $submitted)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid security token. Please refresh and try again.']);
        exit;
    }

    csrf_regenerate();
}
