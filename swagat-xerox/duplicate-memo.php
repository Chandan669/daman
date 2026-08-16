<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_login();

$id = $_GET['id'] ?? '';
if (!$id) {
    die("Memo ID required.");
}

$file_path = MEMOS_DIR . '/' . $id . '.json';
if (!file_exists($file_path)) {
    die("Memo not found.");
}

$json = file_get_contents($file_path);
$memo = json_decode($json, true);

$new_memo_no = generate_memo_number();
$current_date = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Duplicate Memo - SWAGAT XEROX CENTER</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar hide-on-print">
        <div class="nav-brand">SWAGAT XEROX CENTER</div>
        <div class="nav-links">
            <a href="index.php">Dashboard</a>
            <a href="create-memo.php">New Memo</a>
            <a href="settings.php">Settings</a>
        </div>
    </nav>

    <div class="container">
        <form id="memoForm" method="POST" action="api/save-memo.php">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
            <input type="hidden" name="memo_no" value="<?php echo htmlspecialchars($new_memo_no); ?>">

            <div class="card">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <h3 style="margin:0; color:var(--primary);">New Memo: <?php echo htmlspecialchars($new_memo_no); ?></h3>
                    <div style="font-size:0.8rem; color:#666;">(Duplicated from <?php echo htmlspecialchars($memo['memo_no']); ?>)</div>
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
                        <input type="text" name="customer_name" id="input-customer" autocomplete="off" value="<?php echo htmlspecialchars($memo['customer_name'] ?? ''); ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-half">
                        <label>Mobile Number</label>
                        <input type="tel" name="mobile_number" id="input-mobile" autocomplete="off" value="<?php echo htmlspecialchars($memo['mobile_number'] ?? ''); ?>" inputmode="numeric">
                    </div>
                    <div class="form-group col-half">
                        <label>Address</label>
                        <input type="text" name="address" id="input-address" autocomplete="off" value="<?php echo htmlspecialchars($memo['address'] ?? ''); ?>">
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
                        <input type="number" name="discount" id="input-discount" value="<?php echo htmlspecialchars($memo['discount'] ?? '0'); ?>" step="0.01" inputmode="decimal">
                    </div>
                </div>
                <div class="form-group">
                    <label style="font-size:1.2rem; color:var(--primary);">TOTAL (₹)</label>
                    <input type="number" name="total" id="input-total" readonly style="font-size:1.5rem; font-weight:bold; color:var(--primary); background:#eef5ff;">
                </div>
                <div class="form-group">
                    <label>Payment Method</label>
                    <select name="payment_method" id="input-payment" style="font-size:1.2rem; padding:15px;">
                        <option value="Cash" <?php echo ($memo['payment_method'] ?? '') == 'Cash' ? 'selected' : ''; ?>>Cash</option>
                        <option value="UPI" <?php echo ($memo['payment_method'] ?? '') == 'UPI' ? 'selected' : ''; ?>>UPI</option>
                        <option value="Card" <?php echo ($memo['payment_method'] ?? '') == 'Card' ? 'selected' : ''; ?>>Card</option>
                        <option value="Other" <?php echo ($memo['payment_method'] ?? '') == 'Other' ? 'selected' : ''; ?>>Other</option>
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

    <script>
        window.memoData = {
            isEdit: true,
            items: <?php echo json_encode($memo['items'] ?? []); ?>
        };
    </script>
    <script src="assets/js/app.js"></script>
    <script>
        document.getElementById('saveAndExportBtn').addEventListener('click', function() {
            const form = document.getElementById('memoForm');
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'action';
            input.value = 'print';
            form.appendChild(input);
            form.submit();
        });
    </script>
</body>
</html>
