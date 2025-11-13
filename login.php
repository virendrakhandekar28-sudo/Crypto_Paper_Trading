<?php
// Start the session FIRST
require_once 'includes/session.php'; // Must contain session_start()

// Include necessary files
require_once 'includes/config.php';
require_once 'includes/database.php'; // Needed for the auth functions
require_once 'includes/auth.php';     // Contains loginUser function

// --- Redirect if already logged in ---
// Check if the user is already logged in. If yes, redirect to the dashboard.
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// --- Handle Login Form Submission ---
$error_message = ''; // Initialize error message variable
$username_or_email = ''; // Keep username/email in field after failed attempt

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Basic validation/sanitization (you might want more robust validation)
    $username_or_email = isset($_POST['username_or_email']) ? trim($_POST['username_or_email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Check if fields are empty
    if (empty($username_or_email) || empty($password)) {
        $error_message = 'Please enter both username/email and password.';
    } else {
        // Attempt to log the user in using the function from auth.php
        // The loginUser function should connect to the DB (using $pdo from database.php)
        // and verify credentials. It should return user data (e.g., array with id, username) on success, false on failure.
        $user = loginUser($pdo, $username_or_email, $password); // $pdo should be available from database.php

        if ($user) {
            // Login successful!
            // Regenerate session ID for security
            session_regenerate_id(true);

            // Store user information in the session
            $_SESSION['user_id'] = $user['id']; // Assuming loginUser returns an array with 'id'
            $_SESSION['username'] = $user['username']; // Assuming loginUser returns an array with 'username'
            // Store other relevant user info if needed

            // Redirect to the dashboard
            header('Location: index.php');
            exit; // Important: Stop script execution after redirect

        } else {
            // Login failed
            $error_message = 'Invalid username/email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Crypto Paper Trader</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Add any CSS framework links here if you use one -->
    <style>
        /* Basic styling for login form */
        .login-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 30px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        .login-container h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box; /* Include padding in width */
        }
        .login-button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .login-button:hover {
            background-color: #0056b3;
        }
        .error-message {
            color: #dc3545; /* Red color for errors */
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            text-align: center;
        }
        .register-link {
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>
<body style="background-image: url('assets/img/background.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">

    <div class="login-container">
        <h1>Login</h1>

        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username_or_email">Username or Email:</label>
                <input type="text" id="username_or_email" name="username_or_email" value="<?php echo htmlspecialchars($username_or_email); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="login-button">Login</button>
        </form>

        <div class="register-link">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>

    <footer class="main-footer">
        <p>© <?php echo date('Y'); ?> Crypto Paper Trader. All rights reserved. Data from Binance API.</p>
    </footer>

</body>
</html>