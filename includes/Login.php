<?php

/**
 * Login Page - Handles user authentication
 * Displays login form and processes credentials
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/AuthController.php';

// Handle POST request for login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Attempt to authenticate
    if (handle_login()) {
        // Successful login - redirect to dashboard
        header("Location: /dashboard");
        exit();
    } else {
        // Invalid credentials
        echo "❌ Invalid username or password.\n";
    }
}

// Render login form
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Al Amin's Math Care - Login</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 400px; margin: 50px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="password"] { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background-color: #0056b3; }
        .error { color: red; margin-top: 10px; }
    </style>
</head>
<body>
    <h2>🔐 Login to Al Amin's Math Care</h2>
    
    <?php if (isset($error)): ?><p class="error"><?php echo htmlspecialchars($error); ?></p>\n<?php endif;?>
    
    <form method="POST" action="?">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required placeholder="Enter your username">
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required placeholder="Enter your password">
        </div>
        <button type="submit">Sign In</button>
    </form>
    
    <p style="text-align:center; color:#666;">
        Don't have an account? <a href="/login">Register</a>
    </p>
</body>
</html>
