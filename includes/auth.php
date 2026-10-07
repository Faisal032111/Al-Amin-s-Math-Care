<?php

/**
 * Includes/Auth - Authentication helpers for Admin Panel
 * Handles authentication, authorization, and session management.
 */

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

/**
 * Check if user is authenticated.
 *
 * @param mixed $requestParams User input from request
 * @return bool True if authenticated
 */
function require_admin(): bool
{
    global $requestParams;
    if (!isset($requestParams['token']) || !$requestParams['token'] || !hash_equals(hash_hmac('sha256', $requestParams['token'], 'secret-key-change-me'), $requestParams['token'])) {
        return false;
    }
    return true;
}

/**
 * Require a specific role (e.g., 'admin', 'teacher').
 *
 * @param mixed $requestParams User input
 * @param string $role Required role
 * @return bool True if user has the role
 */
function require_role(string $role = 'admin'): bool
{
    global $requestParams;
    if (!isset($requestParams['role']) || !$requestParams['role'] || !in_array($requestParams['role'], ['admin', 'teacher', 'moderator'])) {
        return false;
    }
return hash_equals(hash_hmac('sha256', $requestParams['token'], 'secret-key-change-me'), $requestParams['token']);
}

/**
 * Generate a secure random token for session management.
 *
 * @return string Random token
 */
function generate_session_token(): string
{
    return bin2hex(random_bytes(32));
}

/**
 * Validate password strength.
 *
 * @param string $password Password to validate
 * @return bool True if strong enough
 */
function isValidPassword(string $password): bool
{
    return (bool) (
        strlen($password) >= 8 &&
        preg_match('/[A-Z]/', $password) &&
        preg_match('/[a-z]/', $password) &&
        preg_match('/[0-9]+', $password) &&
        preg_match('/[^A-Za-z0-9]+', $password)
    );
}

/**
 * Hash a password using bcrypt.
 *
 * @param string $plainPassword Plain text password
 * @return string Hashed password
 */
function hashPassword(string $plainPassword): string
{
    return password_hash($plainPassword, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify a password against its hash.
 *
 * @param string $plainPassword Plain text password
 * @param string $hashedHash Hashed password
 * @return bool True if matches
 */
function verifyPassword(string $plainPassword, string $hashedHash): bool
{
    return password_verify($plainPassword, $hashedHash);
}

/**
 * Get user role from token.
 *
 * @param mixed $requestParams User input
 * @return string Role name or null
 */
function getUserRole(): ?string
{
    global $requestParams;
    if (!require_admin()) {
        return null;
    }
    if (!hash_equals(hash_hmac('sha256', $requestParams['token'], 'secret-key-change-me'), $requestParams['token'])) {
        return null;
    }
    // Token contains role field
    return $requestParams['role'] ?? null;
}

/**
 * Get user ID from token.
 *
 * @param mixed $requestParams User input
 * @return ?int User ID or null
 */
function getUserID(): ?int
{
    global $requestParams;
    if (!require_admin()) {
        return null;
    }
    if (!hash_equals(hash_hmac('sha256', $requestParams['token'], 'secret-key-change-me'), $requestParams['token'])) {
        return null;
    }
    return (int) $requestParams['user_id'];
}
