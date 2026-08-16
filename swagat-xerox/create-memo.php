<?php
require_once 'includes/config.php';
require_once "includes/auth.php";
require_login();

$memo_no = generate_memo_number();
$current_date = date('Y-m-d');

$logo_url = get_logo_url();
$signature_url = get_signature_url();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Memo - SWAGAT XEROX CENTER</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar hide-on-print">
        <div class="nav-brand">SWAGAT XEROX CENTER</div>
        <div class="nav-links">
            <a href="index.php">Dashboard</a>
            <a href="create-memo.php" class="active">New Memo</a>
            <a href="settings.php">Settings</a>
        </div>
    </nav>

    <div class="editor-layout">
        <!-- Editor Panel -->
        <div class="editor-panel hide-on-print">
            <form id="memoForm" method="POST" action="api/save-memo.php">
                <input type="hidden" name="memo_no" value="<?php echo htmlspecialchars($memo_no); ?>">

                <div class="card">
                    <h3>Customer Information</h3>
                    <div class="form-row">
                        <div class="form-group col-half">
                            <label>Date</label>
                            <input type="date" name="date" value="<?php echo $current_date; ?>" id="input-date">
                        </div>
                        <div class="form-group col-half">
                            <label>Payment Method</label>
                            <select name="payment_method" id="input-payment">
                                <option value="Cash">Cash</option>
                                <option value="UPI">UPI</option>
                                <option value="Card">Card</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Customer Name</label>
                        <input type="text" name="customer_name" id="input-customer" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" name="address" id="input-address" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label>Mobile Number</label>
                        <input type="text" name="mobile_number" id="input-mobile" autocomplete="off">
                    </div>
                </div>

                <div class="card">
                    <h3>Items</h3>
                    <div class="table-responsive">
                        <table class="item-entry-table" id="editorItemsTable">
                            <thead>
                                <tr>
                                    <th>Particulars</th>
                                    <th width="80">Qty</th>
                                    <th width="100">Rate (₹)</th>
                                    <th width="40"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <!-- JS will populate rows -->
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-secondary mt-10" id="addItemBtn">+ Add Item</button>
                </div>

                <div class="card totals-card">
                    <div class="form-row align-right">
                        <label>Subtotal (₹)</label>
                        <input type="number" name="subtotal" id="input-subtotal" readonly>
                    </div>
                    <div class="form-row align-right">
                        <label>Discount (₹)</label>
                        <input type="number" name="discount" id="input-discount" value="0" step="0.01">
                    </div>
                    <div class="form-row align-right">
                        <label><b>TOTAL (₹)</b></label>
                        <input type="number" name="total" id="input-total" readonly>
                    </div>
                </div>

                <div class="action-buttons sticky-bottom">
                    <button type="submit" class="btn btn-primary btn-lg w-100" id="saveBtn">Save Memo</button>
                    <button type="button" class="btn btn-secondary btn-lg w-100 mt-10" id="saveAndPrintBtn">Save & Print</button>
                </div>
            </form>
        </div>

        <!-- Live Preview Panel -->
        <div class="preview-panel">
            <h3 class="preview-title hide-on-print">Live Preview</h3>

            <!-- Actual Memo Design -->
            <div class="memo-box" id="memoPreview">
                <div class="memo-header">
                    <?php if ($logo_url): ?>
                        <img src="<?php echo htmlspecialchars($logo_url); ?>" alt="Logo" class="memo-logo">
                    <?php endif; ?>
                    <div class="memo-company-info">
                        <h1><?php echo htmlspecialchars(get_setting('company_name')); ?></h1>
                        <p class="subtitle"><?php echo htmlspecialchars(get_setting('company_subtitle')); ?></p>
                    </div>
                </div>

                <h2 class="memo-title">CASH MEMO / PAID</h2>

                <div class="memo-meta">
                    <div class="meta-left">
                        <div><b>Memo No:</b> <?php echo htmlspecialchars($memo_no); ?></div>
                        <div><b>Customer:</b> <span id="prev-customer"></span></div>
                        <div><b>Address:</b> <span id="prev-address"></span></div>
                        <div><b>Mobile:</b> <span id="prev-mobile"></span></div>
                    </div>
                    <div class="meta-right">
                        <div><b>Date:</b> <span id="prev-date"><?php echo date('d-m-Y', strtotime($current_date)); ?></span></div>
                        <div><b>Payment:</b> <span id="prev-payment">Cash</span></div>
                    </div>
                </div>

                <table class="memo-table">
                    <thead>
                        <tr>
                            <th width="10%">No.</th>
                            <th width="50%">Item / Particulars</th>
                            <th width="10%">Qty</th>
                            <th width="15%">Rate</th>
                            <th width="15%">Amount</th>
                        </tr>
                    </thead>
                    <tbody id="prev-items-body">
                        <!-- Preview rows injected here -->
                    </tbody>
                </table>

                <div class="memo-totals">
                    <div class="totals-row">
                        <span>Subtotal:</span>
                        <span>₹<span id="prev-subtotal">0.00</span></span>
                    </div>
                    <div class="totals-row">
                        <span>Discount:</span>
                        <span>₹<span id="prev-discount">0.00</span></span>
                    </div>
                    <div class="totals-row final-total">
                        <span>TOTAL:</span>
                        <span>₹<span id="prev-total">0.00</span></span>
                    </div>
                </div>

                <div class="memo-footer">
                    <div class="paid-stamp">PAID</div>
                    <div class="signature-box">
                        <?php if ($signature_url): ?>
                            <img src="<?php echo htmlspecialchars($signature_url); ?>" alt="Signature" class="memo-signature">
                        <?php endif; ?>
                        <div class="auth-text">Authorized Signature</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pass PHP data to JS -->
    <script>
        window.memoData = {
            defaultRows: <?php echo get_setting('default_item_rows') ?? 1; ?>
        };
    </script>
    <script src="assets/js/app.js"></script>
</body>
</html>
