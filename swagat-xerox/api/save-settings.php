<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request");
}

$new_settings = [
    'company_name' => trim($_POST['company_name'] ?? get_setting('company_name')),
    'company_subtitle' => trim($_POST['company_subtitle'] ?? get_setting('company_subtitle')),
    'default_payment_method' => $_POST['default_payment_method'] ?? 'Cash',
    'default_item_rows' => (int)($_POST['default_item_rows'] ?? 5),
    'default_print_layout' => (int)($_POST['default_print_layout'] ?? 4)
];

if (!empty($_POST["new_password"])) {
    $new_settings["admin_password"] = password_hash($_POST["new_password"], PASSWORD_DEFAULT);
}

save_settings($new_settings);

// Handle Logo Upload/Removal
if (!empty($_POST['remove_logo'])) {
    $files = glob(LOGO_DIR . '/*');
    foreach ($files as $file) {
        if (is_file($file)) unlink($file);
    }
} elseif (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
    // Clear old logo
    $files = glob(LOGO_DIR . '/*');
    foreach ($files as $file) {
        if (is_file($file)) unlink($file);
    }

    $tmp_name = $_FILES['logo']['tmp_name'];
    $name = basename($_FILES['logo']['name']);
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

    if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp'])) {
        $safe_name = 'logo_' . time() . '.' . $ext;
        move_uploaded_file($tmp_name, LOGO_DIR . '/' . $safe_name);
    }
}

// Handle Signature Upload/Removal
if (!empty($_POST['remove_signature'])) {
    $files = glob(SIGNATURE_DIR . '/*');
    foreach ($files as $file) {
        if (is_file($file)) unlink($file);
    }
} elseif (isset($_FILES['signature']) && $_FILES['signature']['error'] === UPLOAD_ERR_OK) {
    // Clear old signature
    $files = glob(SIGNATURE_DIR . '/*');
    foreach ($files as $file) {
        if (is_file($file)) unlink($file);
    }

    $tmp_name = $_FILES['signature']['tmp_name'];
    $name = basename($_FILES['signature']['name']);
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

    if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp'])) {
        $safe_name = 'sig_' . time() . '.' . $ext;
        move_uploaded_file($tmp_name, SIGNATURE_DIR . '/' . $safe_name);
    }
}

header("Location: ../settings.php");
die();
?>
