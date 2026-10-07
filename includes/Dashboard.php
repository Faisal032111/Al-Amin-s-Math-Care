<?php

/**
 * Dashboard - Protected admin/teacher area
 * Shows user-specific content based on authentication status
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/AuthController.php';

// Check authentication
if (!require_admin()) {
    http_response_code(403);
    echo "🚫 Access Denied: Not authenticated\n";
    exit();
}

// Check role
$role = getUserRole();
if (!$role) {
    http_response_code(401);
    echo "🚫 Access Denied: Insufficient privileges\n";
    exit();
}

// Display dashboard content based on role
$roleColor = $role === 'admin' ? '#dc3545' : $role === 'teacher' ? '#28a745' : '#ffc107';
$roleLabel = ucfirst($role);

echo "🎓 Welcome to the Dashboard, " . $roleLabel . "!\n\n";

echo "Current Status: " . ($role === 'admin' ? "ADMIN" : $role === 'teacher' ? "TEACHER" : "MODERATOR") . "\n\n";

echo "Available Actions:\n";
echo "• View course materials\n";
echo "• Manage student enrollments\n";
echo "• Generate reports\n";
echo "• Access administrative controls\n\n";

// Role-specific content
if ($role === 'admin') {
    echo "🔑 ADMIN PANEL\n";
    echo "• User Management\n";
    echo "• Course Catalog\n";
    echo "• Student Records\n";
    echo "• Report Generation\n";
    echo "• System Configuration\n\n";
} else if ($role === 'teacher') {
    echo "📚 TEACHER DASHBOARD\n";
    echo "• Lesson Planning\n";
    echo "• Student Progress Tracking\n";
    echo "• Assignment Submission\n";
    echo "• Gradebook Management\n\n";
} else {
    echo "🆔 MODERATOR PANEL\n";
    echo "• Community Moderation\n";
    echo "• Event Coordination\n";
    echo "• Resource Upload\n";
    echo "• User Support\n\n";
}

echo "✅ Authentication confirmed. You are now logged in as " . $role . "\n";