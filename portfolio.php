<?php
// portfolio.php

// Start the session to check login status
require_once 'includes/session.php';
require_once 'includes/config.php';
require_once 'includes/functions.php'; // Includes isUserLoggedIn()

// Check if the user is logged in. If not, redirect to login page.
if (!isUserLoggedIn()) {
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
    <title>Portfolio - Crypto Paper Trader</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Add any CSS framework links here -->
    <style>
        /* Styling specific to portfolio page - Keep existing styles or adjust */

        /* Styles from previous portfolio.php for summary and table */
        .portfolio-summary-box {
            background-color: #e9ecef; padding: 15px 20px; border-radius: 5px;
            margin-bottom: 25px; display: flex; justify-content: space-around;
            flex-wrap: wrap; border: 1px solid #ced4da;
        }
        .summary-item { margin: 5px 15px; text-align: center; }
        .summary-item h4 {
            margin-bottom: 5px; color: #495057; font-size: 0.95em;
            text-transform: uppercase; font-weight: normal; /* Make header normal weight */
        }
        .summary-item span { font-size: 1.4em; font-weight: bold; color: #495057; }
        .summary-item span.loading { font-size: 1em; font-style: italic; color: #6c757d; font-weight: normal; }

        .portfolio-table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.95em; }
        .portfolio-table th, .portfolio-table td {
            border: 1px solid #dee2e6; padding: 10px 12px; text-align: left; vertical-align: middle;
        }
        .portfolio-table th { background-color: #e9ecef; font-weight: 600; color: #495057; }
        .portfolio-table tbody tr:nth-child(even) { background-color: #f8f9fa; }
        .portfolio-table tbody tr:hover { background-color: #e9ecef; }
        .portfolio-table td.positive { color: #28a745; } /* Green */
        .portfolio-table td.negative { color: #dc3545; } /* Red */

        /* Placeholder styles */
         #portfolio-full-details td.loading-placeholder,
         #portfolio-full-details td.error-placeholder,
         #portfolio-full-details td.no-holdings-placeholder { /* Combined placeholder styles */
             text-align: center; font-style: italic; color: #6c757d; padding: 25px !important;
         }

        /* Style for the action button */
        .action-position-btn {
            color: white; border: none; padding: 5px 10px; border-radius: 4px;
            cursor: pointer; font-size: 0.85em; transition: background-color 0.2s ease;
            min-width: 80px; /* Adjusted width */ text-align: center;
        }
        .action-position-btn.exit-long { background-color: #dc3545; } /* Red */
        .action-position-btn.exit-short { background-color: #28a745; } /* Green */
        .action-position-btn:hover { filter: brightness(90%); }
        .action-position-btn:disabled { background-color: #cccccc !important; cursor: not-allowed; }

        /* Style for the action message area */
         #portfolio-action-message {
             margin-bottom: 15px; padding: 10px; border-radius: 4px;
             display: none; text-align: center;
        }
         #portfolio-action-message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; display: block; }
         #portfolio-action-message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; display: block; }

         /* Style for displaying SHORT positions */
         .portfolio-table td .position-short {
             color: #dc3545; /* Red text for SHORT label */
             font-weight: bold;
             font-size: 0.9em;
             margin-left: 5px;
         }
         .portfolio-table td .position-long { /* Optional: style for LONG */
             /* color: #28a745; */ /* Maybe don't color the LONG label */
             font-weight: bold;
             font-size: 0.9em;
             margin-left: 5px;
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
                <li><a href="portfolio.php" class="active">Portfolio</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="logout.php">Logout (<?php echo $username; ?>)</a></li>
            </ul>
        </nav>
    </header>

    <main class="container portfolio-container">
        <h2>Your Portfolio</h2>

        <section class="portfolio-summary-box">
            <!-- Updated labels for Equity -->
            <div class="summary-item">
                <h4>Cash Balance</h4>
                <span id="portfolio-cash-balance"><span class="loading">Loading...</span></span>
            </div>
            <div class="summary-item">
                <h4>Crypto Net Equity</h4>
                 <span id="portfolio-crypto-value"><span class="loading">Loading...</span></span>
            </div>
             <div class="summary-item">
                <h4>Total Portfolio Equity</h4>
                <span id="portfolio-total-value"><span class="loading">Loading...</span></span>
            </div>
        </section>

        <section class="portfolio-section">
             <h3>Open Positions</h3> <!-- Changed header -->
             <div id="portfolio-action-message"></div> <!-- Message Area -->

            <table class="portfolio-table">
                <thead>
                    <tr>
                        <th>Symbol</th>
                        <th>Position</th> <!-- Shows Qty + Direction -->
                        <th>Avg. Entry Price</th>
                        <th>Current Price</th>
                        <th>Notional Value</th> <!-- Current Value of Position -->
                        <th>Unrealized P/L (%)</th> <!-- Profit/Loss -->
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="portfolio-full-details">
                     <!-- Initial loading row -->
                     <tr>
                        <td colspan="7" class="loading-placeholder">Loading portfolio details...</td> <!-- Colspan updated to 7 -->
                    </tr>
                    <!-- Rows will be dynamically generated by script.js -->
                    <!-- Example Long Row:
                    <tr>
                        <td>BTC</td>
                        <td>0.50000000 <span class="position-long">(Long)</span></td>
                        <td>$60,000.00</td>
                        <td>$65,123.45</td>
                        <td>$32,561.73</td>
                        <td class="positive">$2,561.73 (8.54%)</td>
                        <td><button class="action-position-btn exit-long" data-position-id="1" ...>Sell to Close</button></td>
                    </tr>
                    -->
                     <!-- Example Short Row:
                    <tr>
                        <td>ETH</td>
                        <td>1.50000000 <span class="position-short">(Short)</span></td>
                        <td>$3,800.00</td>
                        <td>$3,550.00</td>
                        <td>$5,325.00</td> <!-- Always positive Notional Value Display -->
                        <td class="positive">$375.00 (6.58%)</td>
                        <td><button class="action-position-btn exit-short" data-position-id="2" ...>Buy to Close</button></td>
                    </tr>
                    -->
                </tbody>
            </table>
        </section>

    </main>

    <footer class="main-footer">
        <p>© <?php echo date('Y'); ?> Crypto Paper Trader. All rights reserved.</p>
    </footer>

    <!-- JavaScript file -->
    <script src="assets/js/script.js"></script>
    <!-- No inline script needed - DOMContentLoaded in script.js handles initialization -->

</body>
</html>