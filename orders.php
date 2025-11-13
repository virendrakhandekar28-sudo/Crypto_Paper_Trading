<?php
// Start the session to check login status
require_once 'includes/session.php';

// Include necessary files (optional here, but good practice)
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if the user is logged in. If not, redirect to login page.
if (!isUserLoggedIn()) { // Using helper function from functions.php
    redirect('login.php');
}


// Get username from session for display
$username = isset($_SESSION['username']) ? sanitizeOutput($_SESSION['username']) : 'User';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - Crypto Paper Trader</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Add any CSS framework links here if you use one -->
    <style>
        /* Simple styling for the orders table */
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .orders-table th,
        .orders-table td {
            border: 1px solid #ddd;
            padding: 10px 12px;
            text-align: left;
            vertical-align: middle;
        }
        .orders-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .orders-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .orders-table tbody tr:hover {
            background-color: #f1f1f1;
        }
        .orders-table td.type-buy {
            color: #28a745; /* Green for Buy */
            font-weight: bold;
        }
         .orders-table td.type-sell {
            color: #dc3545; /* Red for Sell */
            font-weight: bold;
        }
        .orders-table td.status {
             font-style: italic;
             color: #6c757d; /* Grayish */
        }
        /* Loading/Error styles */
        .orders-container.loading #orders-history-list::after {
            content: 'Loading...';
            display: block;
            text-align: center;
            padding: 20px;
            font-style: italic;
        }
         #orders-history-list td.loading-placeholder,
         #orders-history-list td.error-placeholder {
             text-align: center;
             font-style: italic;
             color: #6c757d;
             padding: 20px;
         }
    </style>
</head>
<body style="background-image: url('assets/img/background.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">

    <header class="main-header">
        <h1>Crypto Paper Trader</h1>
        <nav>
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="trade.php">Trade</a></li>
                <li><a href="portfolio.php">Portfolio</a></li>
                <li><a href="orders.php" class="active">Orders</a></li>
                <li><a href="logout.php">Logout (<?php echo $username; ?>)</a></li>
            </ul>
        </nav>
    </header>

    <main class="container orders-container">
        <h2>Order History</h2>

        <section class="orders-section">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Type</th>
                        <th>Symbol</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="orders-history-list">
                    <!-- Order history rows will be loaded here by JavaScript -->
                    <tr>
                        <td colspan="7" class="loading-placeholder">Loading order history...</td>
                    </tr>
                    <!-- Example Row Structure (filled by JS):
                    <tr>
                        <td>2023-10-27 10:30:15</td>
                        <td class="type-buy">BUY</td>
                        <td>BTCUSDT</td>
                        <td>0.00500000</td>
                        <td>$65,123.45</td>
                        <td>$325.62</td>
                        <td class="status">FILLED</td>
                    </tr>
                     <tr>
                        <td>2023-10-26 15:05:00</td>
                        <td class="type-sell">SELL</td>
                        <td>ETHUSDT</td>
                        <td>0.10000000</td>
                        <td>$3,550.00</td>
                        <td>$355.00</td>
                        <td class="status">FILLED</td>
                    </tr>
                    -->
                     <!-- Error State Example (filled by JS):
                    <tr>
                        <td colspan="7" class="error-placeholder">Could not load order history.</td>
                    </tr>
                    -->
                </tbody>
            </table>
        </section>

    </main>

    <footer class="main-footer">
        <p>© <?php echo date('Y'); ?> Crypto Paper Trader. All rights reserved.</p>
    </footer>

    <!-- JavaScript files -->
    <script src="assets/js/script.js"></script>
    <!-- No specific inline script needed here if script.js handles loading based on element ID -->
    <script>
         // Ensure the loading function from script.js is called
         // (This is redundant if script.js checks for '#orders-history-list' on DOMContentLoaded)
         // document.addEventListener('DOMContentLoaded', function() {
         //    if (typeof loadOrderHistory === 'function') {
         //        loadOrderHistory('orders-history-list');
         //    }
         // });
    </script>

</body>
</html>