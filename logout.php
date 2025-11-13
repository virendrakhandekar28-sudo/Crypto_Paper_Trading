<?php
/**
 * logout.php
 *
 * Logs the user out by destroying their session.
 */

// 1. Start the session
// We need to access the session to destroy it.
// Ensure session configuration is loaded if necessary (e.g., cookie params)
require_once 'includes/session.php'; // This file contains session_start() and configuration

// 2. Unset all session variables
// Optional but good practice to clear the global $_SESSION array.
$_SESSION = [];

// 3. Destroy the session cookie
// This step helps ensure the client browser removes the session cookie immediately.
// Get session cookie parameters set in session.php or defaults.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, // Set expiry time in the past
        $params["path"],
        $params["domain"],
        $params["secure"], // Ensure this matches your session.php settings (true if HTTPS)
        $params["httponly"] // Ensure this matches your session.php settings
    );
}

// 4. Destroy the session data on the server
// This invalidates the session ID and removes the session file/data.
session_destroy();

// 5. Redirect to the login page
// Use the redirect function if available, or basic header/exit.
require_once 'includes/functions.php'; // Assuming redirect() is here
redirect('login.php?loggedout=1'); // Add a parameter for potential feedback message on login page

// Fallback redirect if functions.php isn't included or redirect() fails:
// header('Location: login.php?loggedout=1');
// exit;

?>