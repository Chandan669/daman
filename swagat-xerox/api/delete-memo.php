<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_login(); // Ensure only logged in users can delete

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request");
}

$memo_no = $_POST['memo_no'] ?? '';

if (empty($memo_no) || !preg_match('/^[a-zA-Z0-9]+$/', $memo_no)) {
    die("Invalid memo number");
}

$file_path = MEMOS_DIR . '/' . $memo_no . '.json';

if (file_exists($file_path)) {
    unlink($file_path);
}

header("Location: ../index.php");
die();
?>
