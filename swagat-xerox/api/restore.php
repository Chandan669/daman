<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_login();
verify_csrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request");
}

if (!isset($_FILES['backup_file']) || $_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {
    die("Please upload a valid backup zip file.");
}

$tmp_name = $_FILES['backup_file']['tmp_name'];
$zip = new ZipArchive();

if ($zip->open($tmp_name) === TRUE) {
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $stat = $zip->statIndex($i);
        $filename = $stat['name'];

        if (strpos($filename, '..') !== false || substr($filename, 0, 1) === '/') {
            continue;
        }

        $content = $zip->getFromIndex($i);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (strpos($filename, 'data/memos/') === 0 && $ext === 'json') {
            $dest = MEMOS_DIR . '/' . basename($filename);
            file_put_contents($dest, $content);
        } elseif ($filename === 'data/settings.json') {
            $dest = SETTINGS_FILE;
            file_put_contents($dest, $content);
        } elseif (strpos($filename, 'uploads/logo/') === 0 && in_array($ext, ['png', 'jpg', 'jpeg', 'webp'])) {
            $dest = LOGO_DIR . '/' . basename($filename);
            file_put_contents($dest, $content);
        } elseif (strpos($filename, 'uploads/signature/') === 0 && in_array($ext, ['png', 'jpg', 'jpeg', 'webp'])) {
            $dest = SIGNATURE_DIR . '/' . basename($filename);
            file_put_contents($dest, $content);
        }
    }

    $zip->close();
    header("Location: ../settings.php?msg=" . urlencode("Restore successful"));
    die();
} else {
    die("Failed to open the zip file.");
}
?>
