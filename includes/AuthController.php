<?php

/**
 * Authentication Controller - Demonstrates auth system usage
 * Shows how to protect routes and verify authentication
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

function handle_login() {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        $valid_users = [
            'admin' => 'Faisal@5511045',
            'teacher' => 'al-amin-sir',
            'moderator' => 'admin'
        ];
        
        if (isset($valid_users[$username]) && $valid_users[$username] === $password) {
            $token = generate_session_token();
            $role = $username; // Use username as role for demo
            
            // Store user data in session
            $_SESSION['user'] = [
                'token' => $token,
                'role' => $role,
                'user_id' => 1
            ];
            
            return true;
        }
    }
    return false;
}

function handle_logout() {
    unset($_SESSION['user']);
    session_destroy();
    return true;
}

function handle_protected_route() {
    if (!require_admin()) {
        echo "Access denied: Not authenticated\n";
        return false;
    }
    
    $role = getUserRole();
    if (!$role) {
        echo "Access denied: Insufficient privileges\n";
        return false;
    }
    
    echo "Access granted: " . $role . "\n";
    return true;
}

function handle_dashboard() {
    if (!require_admin()) {
        echo "Access denied: Not an administrator\n";
        return false;
    }
    
    echo "Welcome to the dashboard, " . getUserRole() . "!\n";
    return true;
}
