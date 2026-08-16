<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_login();
verify_csrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request");
}

$memo_no = $_POST['memo_no'] ?? '';
$is_edit = isset($_POST['is_edit']) && $_POST['is_edit'] == '1';

if (empty($memo_no)) {
    die("Memo number is required");
}

// Security: Make sure memo_no only contains alphanumeric chars to prevent path traversal
if (!preg_match('/^[a-zA-Z0-9]+$/', $memo_no)) {
    die("Invalid memo number format");
}

$file_path = MEMOS_DIR . '/' . $memo_no . '.json';

// Do not overwrite existing unless it's an edit
if (!$is_edit && file_exists($file_path)) {
    die("Memo already exists.");
}

$items = [];
$subtotal = 0;

if (isset($_POST['item_desc']) && is_array($_POST['item_desc'])) {
    for ($i = 0; $i < count($_POST['item_desc']); $i++) {
        $desc = trim($_POST['item_desc'][$i]);
        if (!empty($desc)) {
            $qty = floatval($_POST['item_qty'][$i]);
            $rate = floatval($_POST['item_rate'][$i]);
            $amt = $qty * $rate;
            $subtotal += $amt;

            $items[] = [
                'desc' => $desc, // Removed htmlspecialchars
                'qty' => $qty,
                'rate' => $rate,
                'amount' => $amt
            ];
        }
    }
}

$discount = floatval($_POST['discount'] ?? 0);
$total = $subtotal - $discount;

$memo_data = [
    'memo_no' => $memo_no,
    'date' => $_POST['date'] ?? date('Y-m-d'),
    'payment_method' => $_POST['payment_method'] ?? 'Cash',
    'customer_name' => trim($_POST['customer_name'] ?? ''),
    'address' => trim($_POST['address'] ?? ''),
    'mobile_number' => trim($_POST['mobile_number'] ?? ''),
    'items' => $items,
    'subtotal' => $subtotal,
    'discount' => $discount,
    'total' => $total,
    'created_at' => date('Y-m-d H:i:s')
];

if (file_put_contents($file_path, json_encode($memo_data, JSON_PRETTY_PRINT))) {
    if (isset($_POST['action']) && $_POST['action'] === 'print') {
        header("Location: ../export.php?id[]=" . urlencode($memo_no));
    } else {
        header("Location: ../index.php");
    }
    die();
} else {
    die("Failed to save memo");
}
?>
