<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_login();

$memo_no = generate_memo_number();
$current_date = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
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

    <div class="container">
        <form id="memoForm" method="POST" action="api/save-memo.php">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
            <input type="hidden" name="memo_no" value="<?php echo htmlspecialchars($memo_no); ?>">

            <div class="card">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <h3 style="margin:0;">Memo: <?php echo htmlspecialchars($memo_no); ?></h3>
                    <div style="font-weight:bold;"><?php echo date('d-m-Y'); ?></div>
                </div>
            </div>

            <div class="card">
                <h3>Customer Information</h3>
                <div class="form-row">
                    <div class="form-group col-half">
                        <label>Date</label>
                        <input type="date" name="date" value="<?php echo $current_date; ?>" id="input-date">
                    </div>
                    <div class="form-group col-half">
                        <label>Customer Name</label>
                        <input type="text" name="customer_name" id="input-customer" autocomplete="off" placeholder="Optional">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-half">
                        <label>Mobile Number</label>
                        <input type="tel" name="mobile_number" id="input-mobile" autocomplete="off" placeholder="Optional" inputmode="numeric">
                    </div>
                    <div class="form-group col-half">
                        <label>Address</label>
                        <input type="text" name="address" id="input-address" autocomplete="off" placeholder="Optional">
                    </div>
                </div>
            </div>

            <div class="card">
                <h3>Items</h3>
                <table class="item-entry-table" id="editorItemsTable">
                    <thead>
                        <tr>
                            <th width="40%">Item</th>
                            <th width="20%">Qty</th>
                            <th width="25%">Rate</th>
                            <th width="15%"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <!-- JS injects rows -->
                    </tbody>
                </table>
                <button type="button" class="btn btn-secondary w-100 btn-lg mt-10" id="addItemBtn">+ Add Item</button>
            </div>

            <div class="card">
                <div class="form-row">
                    <div class="form-group col-half">
                        <label>Subtotal (₹)</label>
                        <input type="number" name="subtotal" id="input-subtotal" readonly>
                    </div>
                    <div class="form-group col-half">
                        <label>Discount (₹)</label>
                        <input type="number" name="discount" id="input-discount" value="0" step="0.01" inputmode="decimal">
                    </div>
                </div>
                <div class="form-group">
                    <label style="font-size:1.2rem; color:var(--primary);">TOTAL (₹)</label>
                    <input type="number" name="total" id="input-total" readonly style="font-size:1.5rem; font-weight:bold; color:var(--primary); background:#eef5ff;">
                </div>
                <div class="form-group">
                    <label>Payment Method</label>
                    <select name="payment_method" id="input-payment" style="font-size:1.2rem; padding:15px;">
                        <?php $def_pay = get_setting('default_payment_method'); ?>
                        <option value="Cash" <?php echo $def_pay == 'Cash' ? 'selected' : ''; ?>>Cash</option>
                        <option value="UPI" <?php echo $def_pay == 'UPI' ? 'selected' : ''; ?>>UPI</option>
                        <option value="Card" <?php echo $def_pay == 'Card' ? 'selected' : ''; ?>>Card</option>
                        <option value="Other" <?php echo $def_pay == 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
            </div>

            <div class="card" style="position: sticky; bottom: 0; z-index: 100; box-shadow: 0 -2px 10px rgba(0,0,0,0.2); padding: 15px;">
                <div class="form-row">
                    <button type="button" class="btn btn-primary btn-lg col-half" id="saveAndExportBtn">Save & Export</button>
                    <button type="submit" class="btn btn-secondary btn-lg col-half" id="saveBtn">Save Only</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Minimal JS to handle rows on mobile, export logic moved to export.php -->
    <script>
        window.memoData = {
            defaultRows: <?php echo get_setting('default_item_rows') ?? 1; ?>
        };
    </script>
    <script src="assets/js/app.js"></script>
    <script>
        document.getElementById('saveAndExportBtn').addEventListener('click', function() {
            const form = document.getElementById('memoForm');
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'action';
            input.value = 'print'; // mapped to export in save-memo.php later
            form.appendChild(input);
            form.submit();
        });
    </script>
</body>
</html>
