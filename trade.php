<?php
// (PHP code at the top remains the same - session, includes, symbol logic)
require_once 'includes/session.php';
require_once 'includes/config.php';
require_once 'includes/functions.php';
if (!isUserLoggedIn()) { redirect('login.php'); }
$username = isset($_SESSION['username']) ? sanitizeOutput($_SESSION['username']) : 'User';
// $initial_symbol will be used to set the default dropdown value
$initial_symbol = isset($_GET['symbol']) ? strtoupper(sanitizeOutput($_GET['symbol'])) : 'BTCUSDT';
if (!preg_match('/^[A-Z0-9]{4,}$/', $initial_symbol)) { $initial_symbol = 'BTCUSDT'; }
// We'll recalculate these in JS when symbol changes
$base_asset = ''; $quote_asset = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Title will be updated by JS -->
    <title>Trade - Crypto Paper Trader</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script type="text/javascript" src="https://s3.tradingview.com/tv.js"></script>

    <style>
        /* --- Layout Overrides (same as before) --- */
        html, body { height: 100%; overflow: hidden; }
        body { display: flex; flex-direction: column; }
        .main-header, .main-footer { flex-shrink: 0; }
        main.container.trade-page-container {
            flex-grow: 1; padding: 0; margin: 0; width: 100%; max-width: 100%;
            display: flex; flex-direction: column; box-shadow: none; border-radius: 0;
            background-color: transparent;
        }
        .chart-section { width: 100%; height: 75vh; flex-shrink: 0; }
        #tv_chart_container { width: 100%; height: 100%; }
        .trade-form-section {
            width: 100%; min-height: 25vh; height: auto; flex-grow: 1;
            background-color: #f8f9fa; border-top: 2px solid #dee2e6;
            padding: 15px 20px; overflow-y: auto; display: flex;
            flex-direction: column;
        }

        /* --- Internal Form Layout Adjustments --- */
         /* NEW: Style for symbol select */
        .symbol-price-row {
             display: flex;
             justify-content: space-between;
             align-items: center;
             margin-bottom: 10px;
             flex-wrap: wrap;
             gap: 15px;
        }
         #symbol-select {
            padding: 8px 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1.2em; /* Make symbol selector prominent */
            font-weight: bold;
            background-color: #fff;
            cursor: pointer;
            min-width: 180px;
            flex-grow: 1; /* Allow some growth */
            max-width: 300px; /* Limit width */
         }
          #symbol-select:disabled {
             cursor: not-allowed;
             background-color: #e9ecef;
         }
         .price-display { font-size: 1.5em; margin: 0; text-align: right; flex-grow: 1;}
         .balances { font-size: 0.9em; margin: 0; border-bottom: none; padding-bottom: 0; justify-content: flex-end; gap: 20px; width: 100%; order: 3; /* Move below form row on wrap */}
         .balances div { margin-bottom: 0; text-align: right;}
         .balances span { display: inline; margin-top: 0; margin-left: 5px;}

         .trade-form-row { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 20px; width: 100%; margin-top: 5px; order: 2; }
         .trade-form-column { flex: 1; min-width: 180px; }
         .trade-form-column.actions { flex-basis: 220px; flex-grow: 0; }
         .form-group { margin-bottom: 0; } /* Remove bottom margin as row has gap */
         .estimated-total { text-align: right; margin-top: 5px; padding: 0; border: none; min-height: auto; font-size: 0.9em;}
         .trade-button { margin-top: 0; font-size: 1em; padding: 10px 12px; width: 100%; } /* Button takes full column width */
         .trade-message { margin-top: 10px; font-size: 0.9em; order: 4; width: 100%;}
         .trade-form label { margin-bottom: 4px; font-size: 0.9em; }
         .trade-form .radio-group { margin-bottom: 0; } /* Remove bottom margin */
         .trade-form .radio-group label { margin: 0 10px 0 0; }

    </style>
</head>
<body style="background-image: url('assets/img/background.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">

    <header class="main-header">
        <h1>Crypto Paper Trader</h1>
        <nav>
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="trade.php" class="active">Trade</a></li>
                <li><a href="portfolio.php">Portfolio</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="logout.php">Logout (<?php echo $username; ?>)</a></li>
            </ul>
        </nav>
    </header>

    <main class="container trade-page-container">
        <!-- Hidden inputs for state -->
        <input type="hidden" id="trade-symbol" value="<?php echo sanitizeOutput($initial_symbol); ?>">
        <input type="hidden" id="base-asset" value=""> <!-- Will be set by JS -->
        <input type="hidden" id="quote-asset" value=""> <!-- Will be set by JS -->

        <!-- Chart Section -->
        <section class="chart-section">
            <div id="tv_chart_container"></div>
        </section>

        <!-- Trade Form Section -->
        <section class="trade-form-section">

            <!-- Symbol Select / Price Row -->
            <div class="symbol-price-row">
                <select id="symbol-select" name="symbol_select" disabled>
                     <option value="">Loading Symbols...</option>
                     <!-- Options populated by JS -->
                </select>
                <div class="price-display">
                     <span id="current-price-display"><span class="loading">Loading...</span></span>
                </div>
            </div>

             <!-- Form and Balances Row -->
            <form id="trade-form" action="#" method="POST">
                 <!-- Hidden input still needed for form submission -->
                 <input type="hidden" id="trade-symbol-form" name="symbol" value="<?php echo sanitizeOutput($initial_symbol); ?>">

                 <div class="trade-form-row">

                     <div class="trade-form-column"> <!-- Type -->
                         <div class="form-group radio-group">
                            <label style="display: block; margin-bottom: 4px;">Order Type:</label>
                             <label for="type_buy">
                                 <input type="radio" id="type_buy" name="type" value="BUY" checked> Buy
                             </label>
                             <label for="type_sell">
                                 <input type="radio" id="type_sell" name="type" value="SELL"> Sell
                             </label>
                        </div>
                     </div>

                     <div class="trade-form-column"> <!-- Quantity -->
                         <div class="form-group">
                            <label for="trade-quantity">Quantity (<span id="asset-label-form">...</span>):</label>
                            <input type="number" id="trade-quantity" name="quantity" step="any" min="0" placeholder="e.g., 0.01" required>
                         </div>
                     </div>

                     <div class="trade-form-column actions"> <!-- Total / Button -->
                          <div class="estimated-total">
                            ~ <span id="estimated-total">0.00</span> <span id="quote-asset-total-label">...</span>
                         </div>
                         <button type="submit" id="trade-button" class="trade-button buy">Place Buy Order</button>
                    </div>

                 </div>
                 <!-- Balances moved below form row visually -->
                 <div class="balances">
                    <div>Cash (<span id="quote-asset-label">...</span>):<span id="available-cash"><span class="loading">Loading...</span></span></div>
                    <div><span id="asset-label">...</span>:<span id="available-asset"><span class="loading">Loading...</span></span></div>
                </div>
                 <div id="trade-message" class="trade-message">
                    <!-- Messages appear full width below row -->
                 </div>
            </form>

        </section>
    </main>

    <footer class="main-footer">
        <p>© <?php echo date('Y'); ?> Crypto Paper Trader. All rights reserved.</p>
    </footer>

    <!-- JavaScript files -->
    <script src="assets/js/script.js"></script> <!-- Contains apiCall, formatters, loadTradeBalances etc. -->
    <script src="assets/js/chart_init.js"></script> <!-- Contains TradingView init logic -->

    <!-- Inline JS for Trade Page Logic -->
     <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- Element References ---
            const symbolSelect = document.getElementById('symbol-select');
            const symbolInputHidden = document.getElementById('trade-symbol'); // Hidden input watched by chart_init.js
            const symbolFormInput = document.getElementById('trade-symbol-form'); // Hidden input for form POST
            const baseAssetInputHidden = document.getElementById('base-asset');
            const quoteAssetInputHidden = document.getElementById('quote-asset'); // Added hidden input for quote
            const assetLabel = document.getElementById('asset-label');
            const assetLabelForm = document.getElementById('asset-label-form');
            const quoteAssetLabel = document.getElementById('quote-asset-label');
            const quoteAssetTotalLabel = document.getElementById('quote-asset-total-label');
            const quantityInput = document.getElementById('trade-quantity');
            const priceDisplay = document.getElementById('current-price-display');
            const estimatedTotalDisplay = document.getElementById('estimated-total');
            const tradeButton = document.getElementById('trade-button');
            const buyRadio = document.getElementById('type_buy');
            const sellRadio = document.getElementById('type_sell');
            const tradeForm = document.getElementById('trade-form'); // Get form reference

            let currentPrice = 0;
            let currentSymbol = symbolInputHidden.value || 'BTCUSDT'; // Get initial symbol

            // --- Function to Update UI for New Symbol ---
            function updateUIForNewSymbol(newSymbol) {
                if (!newSymbol) return;
                console.log(`Updating UI for symbol: ${newSymbol}`);
                currentSymbol = newSymbol.toUpperCase();

                // Update hidden inputs
                symbolInputHidden.value = currentSymbol;
                symbolFormInput.value = currentSymbol;

                // Trigger change event on hidden input for chart_init.js to detect
                symbolInputHidden.dispatchEvent(new Event('change'));

                // Parse base/quote assets
                let base = 'UNK'; let quote = 'UNK';
                const commonQuotes = ['USDT', 'BUSD', 'USDC', 'TUSD', 'DAI', 'BTC', 'ETH', 'BNB']; // Keep this list updated
                for (const q of commonQuotes) {
                    if (currentSymbol.endsWith(q)) { base = currentSymbol.substring(0, currentSymbol.length - q.length); quote = q; break; }
                }
                if (base === 'UNK' && currentSymbol.length > 3) { base = currentSymbol.substring(0, 3); quote = currentSymbol.substring(3); } // Fallback

                // Update hidden asset inputs and labels
                baseAssetInputHidden.value = base;
                quoteAssetInputHidden.value = quote;
                if(assetLabel) assetLabel.textContent = base;
                if(assetLabelForm) assetLabelForm.textContent = base;
                if (quoteAssetLabel) quoteAssetLabel.textContent = quote;
                if (quoteAssetTotalLabel) quoteAssetTotalLabel.textContent = quote;

                 // Restart price updates
                 if (typeof startPriceUpdates === 'function') {
                     startPriceUpdates(currentSymbol, 'current-price-display');
                 } else { console.error("startPriceUpdates function not found."); }

                 // Reload balances
                 if (typeof loadTradeBalances === 'function') {
                      loadTradeBalances('available-cash', 'available-asset', base); // Pass base asset
                 } else { console.error("loadTradeBalances function not found."); }

                 // Update page title
                 document.title = `Trade ${currentSymbol} - Crypto Paper Trader`;

                 // Recalculate estimated total (price update will trigger this via observer)
                 updateTradeFormUI();
            }


            // --- Function to Update Form Button/Total ---
            function updateTradeFormUI() {
                 const quantity = parseFloat(quantityInput.value) || 0;
                 const priceText = priceDisplay.textContent.replace(/[^0-9.]/g, '');
                 currentPrice = parseFloat(priceText) || 0;
                 const total = quantity * currentPrice;
                 estimatedTotalDisplay.textContent = total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 8 });
                 if (buyRadio.checked) {
                     tradeButton.textContent = `Place Buy Order`; tradeButton.className = 'trade-button buy';
                 } else {
                     tradeButton.textContent = `Place Sell Order`; tradeButton.className = 'trade-button sell';
                 }
            }

            // --- Populate Symbol Dropdown ---
            async function populateSymbolDropdown(selectElementId, initialSymbol) {
                const selectEl = document.getElementById(selectElementId);
                if (!selectEl) return;

                console.log("Populating symbol dropdown...");
                try {
                    // Fetch symbols - get more, filter for USDT, sort
                    const allSymbolsData = await apiCall('api/search_symbols.php?limit=1000'); // Fetch a large list

                    if (allSymbolsData && Array.isArray(allSymbolsData)) {
                         console.log(`Fetched ${allSymbolsData.length} symbols.`);
                         // Filter for USDT pairs initially (optional)
                         const filteredSymbols = allSymbolsData.filter(s => s.symbol && s.symbol.endsWith('USDT'));
                         // You can remove the filter above to show all pairs

                         // Sort alphabetically
                         filteredSymbols.sort((a, b) => a.symbol.localeCompare(b.symbol));

                        selectEl.innerHTML = ''; // Clear loading message

                        // Add placeholder/default option?
                        // const placeholder = document.createElement('option');
                        // placeholder.value = "";
                        // placeholder.textContent = "-- Select Symbol --";
                        // selectEl.appendChild(placeholder);

                        filteredSymbols.forEach(symbolInfo => {
                            const option = document.createElement('option');
                            option.value = symbolInfo.symbol;
                            option.textContent = symbolInfo.symbol;
                            // Set initial selection
                            if (symbolInfo.symbol === initialSymbol) {
                                option.selected = true;
                            }
                            selectEl.appendChild(option);
                        });
                        selectEl.disabled = false; // Enable dropdown
                        console.log("Symbol dropdown populated.");

                         // Trigger initial UI update after populating
                         updateUIForNewSymbol(selectEl.value);

                    } else {
                        selectEl.innerHTML = '<option value="">Error loading</option>';
                        console.error("Failed to load symbols or data format incorrect.");
                    }
                } catch (error) {
                    console.error("Error populating symbol dropdown:", error);
                    selectEl.innerHTML = '<option value="">Error loading</option>';
                }
            }

            // --- Event Listeners ---
            quantityInput.addEventListener('input', updateTradeFormUI);
            const observer = new MutationObserver(() => updateTradeFormUI()); // Updates total on price change
            if(priceDisplay) observer.observe(priceDisplay, { childList: true, characterData: true, subtree: true });
            buyRadio.addEventListener('change', updateTradeFormUI);
            sellRadio.addEventListener('change', updateTradeFormUI);

            // *** Listen for Dropdown Changes ***
            symbolSelect.addEventListener('change', function(event) {
                 const newSymbol = event.target.value;
                 if (newSymbol) {
                     updateUIForNewSymbol(newSymbol);
                 }
            });

            // Attach submit handler to form (assumes handleTradeSubmit exists in script.js)
            if (tradeForm && typeof handleTradeSubmit === 'function') {
                tradeForm.addEventListener('submit', handleTradeSubmit);
            } else {
                 console.error("Trade form or handleTradeSubmit function not found.");
            }

            // --- Initial Load ---
            populateSymbolDropdown('symbol-select', currentSymbol);
            // Initial price/balance loads are now triggered by populateSymbolDropdown -> updateUIForNewSymbol

             updateTradeFormUI(); // Initial call for button text etc.

        }); // End DOMContentLoaded
    </script>

</body>
</html>