<?php
// Start the session to check login status
require_once 'includes/session.php'; // Must contain session_start()

// Check if the user is logged in. If not, redirect to login page.
// Assumes 'user_id' is set in the session upon successful login.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit; // Stop script execution after redirect
}

// Include necessary files (optional here if data is loaded via API, but good practice)
require_once 'includes/config.php';
require_once 'includes/functions.php'; // For potential helper functions like formatting

// Get username from session for display (ensure it's stored during login)
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Crypto Paper Trader</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Add any CSS framework links here if you use one (e.g., Bootstrap) -->
</head>
<body style="background-image: url('assets/img/background.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;"></body>

    <header class="main-header">
        <h1>Crypto Paper Trader</h1>
        <nav>
            <ul>
                <li><a href="index.php" class="active">Dashboard</a></li>
                <li><a href="trade.php">Trade</a></li>
                <li><a href="portfolio.php">Portfolio</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="logout.php">Logout (<?php echo $username; ?>)</a></li>
            </ul>
        </nav>
    </header>

    <main class="container" >
        <h2>Welcome back, <?php echo $username; ?>!</h2>

        <section id="dashboard-overview">

            <div id="market-overview" class="dashboard-section">
                <h3>Market Overview</h3>
                <p>Loading market data...</p>
                <!-- Market data (top coins, prices) will be loaded here by JavaScript -->
                <!-- Example Structure (to be filled by JS):
                <ul id="market-list">
                    <li>BTC/USDT: $ L O A D I N G ... </li>
                    <li>ETH/USDT: $ L O A D I N G ... </li>
                </ul>
                -->
            </div>

            <!-- Inside index.php -->

            <div id="portfolio-summary" class="dashboard-section">
                <h3>Portfolio Summary</h3>
                <div id="portfolio-summary-content"> <!-- <<< ADD THIS INNER DIV -->
                    <p class="loading">Loading portfolio summary...</p> <!-- <<< Move loading message inside -->
                </div>
            </div>
            <?php include 'includes/cash_form.php'; ?>

        </section>

    </main>

    <footer class="main-footer">
        <p>© <?php echo date('Y'); ?> Crypto Paper Trader. All rights reserved. Data from Binance API.</p>
    </footer>

    <!-- JavaScript files -->
    <!-- Include jQuery or other libraries if needed before script.js -->
    <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
    <script src="assets/js/script.js"></script>

</body>
</html>