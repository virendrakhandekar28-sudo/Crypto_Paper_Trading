<?php
// Start the session FIRST
require_once 'includes/session.php'; // Must contain session_start()

// Include necessary files
require_once 'includes/config.php';    // For potential default settings (like starting balance)
require_once 'includes/database.php';  // For the $pdo connection object
require_once 'includes/auth.php';      // To call registration functions (registerUser, isUsernameTaken, isEmailTaken)
require_once 'includes/functions.php'; // For potential extra validation/formatting functions

// --- Redirect if already logged in ---
// Redirect logged-in users away from registration
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// --- Initialize Variables ---
$username = '';
$email = '';
$error_messages = []; // Array to hold validation errors
$success_message = '';

// --- Handle Registration Form Submission ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get and sanitize input data
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';

    // --- Validation ---
    // Username validation
    if (empty($username)) {
        $error_messages['username'] = 'Username is required.';
    } elseif (strlen($username) < 3 || strlen($username) > 30) {
         $error_messages['username'] = 'Username must be between 3 and 30 characters.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
         $error_messages['username'] = 'Username can only contain letters, numbers, and underscores.';
    } elseif (isUsernameTaken($pdo, $username)) { // Requires function in auth.php
         $error_messages['username'] = 'Username is already taken.';
    }

    // Email validation
    if (empty($email)) {
        $error_messages['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_messages['email'] = 'Invalid email format.';
    } elseif (isEmailTaken($pdo, $email)) { // Requires function in auth.php
         $error_messages['email'] = 'Email is already registered.';
    }

    // Password validation
    if (empty($password)) {
        $error_messages['password'] = 'Password is required.';
    } elseif (strlen($password) < 8) { // Enforce minimum length
        $error_messages['password'] = 'Password must be at least 8 characters long.';
    } elseif ($password !== $password_confirm) {
        $error_messages['password_confirm'] = 'Passwords do not match.';
    }

    // --- If No Errors, Proceed with Registration ---
    if (empty($error_messages)) {
        // Hash the password securely
        $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Use default bcrypt algorithm

        // Define starting virtual cash balance (could be in config.php)
        $starting_cash = defined('DEFAULT_STARTING_CASH') ? DEFAULT_STARTING_CASH : 100000; // Example: $100,000

        // Attempt to register the user using function from auth.php
        // This function should handle inserting into 'users' table and setting initial balance.
        $registration_result = registerUser($pdo, $username, $email, $hashed_password, $starting_cash);

        if ($registration_result) {
            // Registration successful!
            $success_message = 'Registration successful! You can now <a href="login.php">log in</a>.';
            // Optionally: Clear form fields on success
            $username = '';
            $email = '';
        } else {
            // Database error or other registration failure
            $error_messages['general'] = 'Registration failed due to a server error. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Crypto Paper Trader</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Add any CSS framework links here -->
    <style>
        /* Basic styling for registration form - similar to login */
        .register-container {
            max-width: 450px; /* Slightly wider for more fields potentially */
            margin: 50px auto;
            padding: 30px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        .register-container h1 {
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
            box-sizing: border-box;
        }
        .register-button {
            width: 100%;
            padding: 12px;
            background-color: #28a745; /* Green color for registration */
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .register-button:hover {
            background-color: #218838;
        }
        .error-message { /* Style for individual field errors */
            color: #dc3545;
            font-size: 0.9em;
            margin-top: 3px;
        }
        .general-error-message { /* Style for general failure message */
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            text-align: center;
        }
        .success-message {
            color: #155724; /* Dark green */
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            text-align: center;
        }
        .login-link {
            text-align: center;
            margin-top: 15px;
        }
        /* Style to highlight fields with errors */
        .input-error {
            border-color: #dc3545 !important;
        }
    </style>
</head>
<body style="background-image: url('assets/img/background.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">

    <div class="register-container">
        <h1>Register</h1>

        <?php if (!empty($success_message)): ?>
            <div class="success-message"><?php echo $success_message; // Allow the link HTML ?></div>
        <?php endif; ?>

        <?php if (isset($error_messages['general'])): ?>
            <div class="general-error-message"><?php echo htmlspecialchars($error_messages['general']); ?></div>
        <?php endif; ?>

        <form action="register.php" method="POST" novalidate>
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required class="<?php echo isset($error_messages['username']) ? 'input-error' : ''; ?>">
                <?php if (isset($error_messages['username'])): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error_messages['username']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required class="<?php echo isset($error_messages['email']) ? 'input-error' : ''; ?>">
                 <?php if (isset($error_messages['email'])): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error_messages['email']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">Password (min 8 characters):</label>
                <input type="password" id="password" name="password" required class="<?php echo isset($error_messages['password']) ? 'input-error' : ''; ?>">
                <?php if (isset($error_messages['password'])): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error_messages['password']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password_confirm">Confirm Password:</label>
                <input type="password" id="password_confirm" name="password_confirm" required class="<?php echo isset($error_messages['password_confirm']) ? 'input-error' : ''; ?>">
                <?php if (isset($error_messages['password_confirm'])): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error_messages['password_confirm']); ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="register-button">Register</button>
        </form>

        <div class="login-link">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>

    <footer class="main-footer">
        <p>© <?php echo date('Y'); ?> Crypto Paper Trader. All rights reserved. Data from Binance API.</p>
    </footer>

</body>
</html>