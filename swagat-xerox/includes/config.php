<?php
define('BASE_DIR', realpath(__DIR__ . '/..'));
define('DATA_DIR', BASE_DIR . '/data');
define('MEMOS_DIR', DATA_DIR . '/memos');
define('UPLOADS_DIR', BASE_DIR . '/uploads');
define('LOGO_DIR', UPLOADS_DIR . '/logo');
define('SIGNATURE_DIR', UPLOADS_DIR . '/signature');
define('SETTINGS_FILE', DATA_DIR . '/settings.json');

// Ensure directories exist
if (!is_dir(MEMOS_DIR)) mkdir(MEMOS_DIR, 0755, true);
if (!is_dir(LOGO_DIR)) mkdir(LOGO_DIR, 0755, true);
if (!is_dir(SIGNATURE_DIR)) mkdir(SIGNATURE_DIR, 0755, true);

// Default settings
$settings = [
    'company_name' => 'SWAGAT XEROX CENTER',
    'company_subtitle' => 'XEROX • PRINT • COPY • SCAN • BINDING',
    'admin_password' => password_hash('admin123', PASSWORD_DEFAULT),
    'memo_counter' => 0,
    'default_payment_method' => 'Cash',
    'default_item_rows' => 5,
    'default_print_layout' => 4
];

if (file_exists(SETTINGS_FILE)) {
    $json = file_get_contents(SETTINGS_FILE);
    $saved_settings = json_decode($json, true);
    if (is_array($saved_settings)) {
        $settings = array_merge($settings, $saved_settings);
    }
}

function get_setting($key) {
    global $settings;
    return $settings[$key] ?? null;
}

function save_settings($new_settings) {
    global $settings;
    $settings = array_merge($settings, $new_settings);
    file_put_contents(SETTINGS_FILE, json_encode($settings, JSON_PRETTY_PRINT));
}

// Helper functions to get paths for URLs
function get_logo_url() {
    $files = glob(LOGO_DIR . '/*');
    if ($files && count($files) > 0) {
        return 'uploads/logo/' . basename($files[0]);
    }
    return null;
}

function get_signature_url() {
    $files = glob(SIGNATURE_DIR . '/*');
    if ($files && count($files) > 0) {
        return 'uploads/signature/' . basename($files[0]);
    }
    return null;
}

function generate_memo_number() {
    global $settings;
    $settings['memo_counter']++;
    save_settings([]); // Just save the updated counter
    return 'M' . str_pad($settings['memo_counter'], 5, '0', STR_PAD_LEFT);
}
?>
