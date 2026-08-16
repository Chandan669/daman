<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_login();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request']);
    die();
}

$input = json_decode(file_get_contents('php://input'), true);
$ids = $input['ids'] ?? [];

if (!is_array($ids) || empty($ids)) {
    echo json_encode(['error' => 'No IDs provided']);
    die();
}

$memos = [];
foreach ($ids as $id) {
    if (preg_match('/^[a-zA-Z0-9]+$/', $id)) {
        $file_path = MEMOS_DIR . '/' . $id . '.json';
        if (file_exists($file_path)) {
            $json = file_get_contents($file_path);
            $memo = json_decode($json, true);
            if ($memo) {
                $memos[] = $memo;
            }
        }
    }
}

echo json_encode([
    'memos' => $memos,
    'logo_url' => get_logo_url() ? '../' . get_logo_url() : null,
    'signature_url' => get_signature_url() ? '../' . get_signature_url() : null,
    'settings' => $settings
]);
?>
