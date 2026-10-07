<?php

/**
 * Index Page - Admin Home & Dashboard
 * Main landing page with clear admin navigation
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

// Populate request parameters from session
$requestParams = $_SESSION['user'] ?? [];

// Check authentication status
if (!require_admin()) {
    echo "<h1>🔒 Unauthorized Access</h1>";
    echo "<p>Please log in to access this area.</p>";
    echo '<a href="/login">🔑 Sign In</a>';
    exit();
}

// Check user role
$userRole = getUserRole();
if (!$userRole) {
    echo "<h1>🔒 Insufficient Privileges</h1>";
    echo "<p>Your role doesn\'t have permission to view this page.</p>";
    echo '<a href="/login">🔑 Sign In</a>';
    exit();
}

// Display welcome message based on role
$welcomeMessage = sprintf(
    "<h1>👋 Welcome, %s!</h1><p>Your role: %s</p>",
    $userRole,
    $userRole
);

echo $welcomeMessage;

echo "<br><hr>\n";

// Clear admin home section
$adminHeader = <<<EOH
<h2>🏛️ Admin Home</h2>
<p>Welcome, <strong>{$userRole}</strong>! This is the administrative dashboard.</p>
<ul>
    <li>Manage Users</li>
    <li>View Course Materials</li>
    <li>Student Enrollment</li>
    <li>Report Generation</li>
    <li>System Configuration</li>
</ul>
EOH;

echo $adminHeader;

echo "<br><hr>\n";

// Dashboard section (for teachers/admins)
if (require_role('admin')) {
    echo "<h2>📊 Admin Dashboard</h2>";
    echo "<p>You have full administrative access. View all system controls.</p>";
} elseif (require_role('teacher')) {
    echo "<h2>📚 Teacher Dashboard</h2>";
    echo "<p>Teacher-specific controls and student management.</p>";
} else {
    echo "<h2>🔐 Moderator Area</h2>";
    echo "<p>Community moderation tools.</p>";
}

echo "<br><hr>\n";

echo "Current User: " . ($userRole ?? 'Unidentified') . " (" . ($requestParams['user_id'] ?? 'N/A') . ")\n";

// Example of protected route (would be in a real app via a controller)
if (require_role('admin')) {
    echo "<h2>🛡️ Protected Section - Admin Area</h2>";
    echo "<p>This section is only accessible to administrators.</p>";
} else {
    echo "<p>Access denied - you are not an administrator.</p>";
}

echo "<br><hr>";

// Example of another protected route
if (require_role('teacher')) {
    echo "<h2>📚 Teacher Dashboard</h2>";
    echo "<p>Teachers can access course management and student records.</p>";
} else {
    echo "<p>Access denied - teacher role not sufficient for this view.</p>";
}

echo "<br><hr>";

// Show current user info
if (isset($requestParams['user_id'])) {
    echo "<h3>Current User Info</h3>";
    echo "• User ID: " . ($requestParams['user_id'] ?? 'N/A') . "<br>";
    echo "• Role: " . ($userRole ?? 'Unknown') . "<br>";
}
