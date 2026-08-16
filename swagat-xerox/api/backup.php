<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_login();

$zip = new ZipArchive();
$filename = "Swagat-Xerox-Backup-" . date('Y-m-d') . ".zip";
$filepath = sys_get_temp_dir() . '/' . $filename;

if ($zip->open($filepath, ZipArchive::CREATE) !== TRUE) {
    die("Cannot create zip file");
}

// Add memos
$memos = glob(MEMOS_DIR . '/*.json');
foreach ($memos as $memo) {
    $zip->addFile($memo, 'data/memos/' . basename($memo));
}

// Add settings
if (file_exists(SETTINGS_FILE)) {
    $zip->addFile(SETTINGS_FILE, 'data/settings.json');
}

// Add logo
$logos = glob(LOGO_DIR . '/*');
foreach ($logos as $logo) {
    $zip->addFile($logo, 'uploads/logo/' . basename($logo));
}

// Add signature
$signatures = glob(SIGNATURE_DIR . '/*');
foreach ($signatures as $sig) {
    $zip->addFile($sig, 'uploads/signature/' . basename($sig));
}

$zip->close();

header('Content-Type: application/zip');
header('Content-disposition: attachment; filename=' . $filename);
header('Content-Length: ' . filesize($filepath));
readfile($filepath);
unlink($filepath);
die();
?>
