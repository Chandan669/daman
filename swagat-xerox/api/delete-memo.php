<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_login();
verify_csrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request");
}

$ids = $_POST['id'] ?? [];
if (!is_array($ids)) {
    // Fallback if someone sends a single 'memo_no' parameter
    $memo_no = $_POST['memo_no'] ?? '';
    if ($memo_no) {
        $ids = [$memo_no];
    }
}

if (empty($ids)) {
    header("Location: ../index.php");
    die();
}

foreach ($ids as $id) {
    if (preg_match('/^[a-zA-Z0-9]+$/', $id)) {
        $file_path = MEMOS_DIR . '/' . $id . '.json';
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
}

header("Location: ../index.php");
die();
?>
