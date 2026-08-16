<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_login();

$logo_url = get_logo_url();
$signature_url = get_signature_url();
$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - SWAGAT XEROX CENTER</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">SWAGAT XEROX CENTER</div>
        <div class="nav-links">
            <a href="index.php">Dashboard</a>
            <a href="create-memo.php">New Memo</a>
            <a href="settings.php" class="active">Settings</a>
        </div>
    </nav>

    <div class="container" style="max-width: 800px; margin: 20px auto;">
        <h2>Settings</h2>

        <?php if($msg): ?>
            <div style="background: var(--success); color: white; padding: 10px; border-radius: 6px; margin-bottom: 15px;">
                <?php echo htmlspecialchars($msg); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="api/save-settings.php" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
            <div class="card">
                <h3>Company Details</h3>
                <div class="form-group">
                    <label>Company Name</label>
                    <input type="text" name="company_name" value="<?php echo htmlspecialchars(get_setting('company_name')); ?>">
                </div>
                <div class="form-group">
                    <label>Subtitle</label>
                    <input type="text" name="company_subtitle" value="<?php echo htmlspecialchars(get_setting('company_subtitle')); ?>">
                </div>
            </div>

            <div class="card">
                <h3>Branding Images</h3>

                <div class="form-row">
                    <div class="form-group col-half">
                        <label>Company Logo</label>
                        <?php if ($logo_url): ?>
                            <div class="current-image">
                                <img src="<?php echo htmlspecialchars($logo_url); ?>" alt="Logo" style="max-height: 80px;">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="logo" accept="image/png, image/jpeg, image/jpg, image/webp">
                        <div class="mt-10">
                            <label><input type="checkbox" name="remove_logo" value="1"> Remove current logo</label>
                        </div>
                        <div class="form-group mt-10">
                            <label>Logo Alignment</label>
                            <select name="logo_alignment">
                                <?php $l_align = get_setting('logo_alignment'); ?>
                                <option value="Left" <?php echo $l_align == 'Left' ? 'selected' : ''; ?>>Left</option>
                                <option value="Center" <?php echo $l_align == 'Center' ? 'selected' : ''; ?>>Center</option>
                                <option value="Right" <?php echo $l_align == 'Right' ? 'selected' : ''; ?>>Right</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group col-half">
                        <label>Digital Signature</label>
                        <?php if ($signature_url): ?>
                            <div class="current-image">
                                <img src="<?php echo htmlspecialchars($signature_url); ?>" alt="Signature" style="max-height: 80px;">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="signature" accept="image/png, image/jpeg, image/jpg, image/webp">
                        <div class="mt-10">
                            <label><input type="checkbox" name="remove_signature" value="1"> Remove current signature</label>
                        </div>
                        <div class="form-group mt-10">
                            <label>Signature Alignment</label>
                            <select name="signature_alignment">
                                <?php $s_align = get_setting('signature_alignment'); ?>
                                <option value="Left" <?php echo $s_align == 'Left' ? 'selected' : ''; ?>>Left</option>
                                <option value="Center" <?php echo $s_align == 'Center' ? 'selected' : ''; ?>>Center</option>
                                <option value="Right" <?php echo $s_align == 'Right' ? 'selected' : ''; ?>>Right</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Signature Max Width (px)</label>
                            <input type="number" name="signature_width" value="<?php echo htmlspecialchars(get_setting('signature_width') ?? 120); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <h3>Memo Settings</h3>
                <div class="form-row">
                    <div class="form-group col-half">
                        <label>Default Payment Method</label>
                        <select name="default_payment_method">
                            <?php $def_pay = get_setting('default_payment_method'); ?>
                            <option value="Cash" <?php echo $def_pay == 'Cash' ? 'selected' : ''; ?>>Cash</option>
                            <option value="UPI" <?php echo $def_pay == 'UPI' ? 'selected' : ''; ?>>UPI</option>
                            <option value="Card" <?php echo $def_pay == 'Card' ? 'selected' : ''; ?>>Card</option>
                            <option value="Other" <?php echo $def_pay == 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="form-group col-half">
                        <label>Default Empty Rows in New Memo</label>
                        <input type="number" name="default_item_rows" value="<?php echo htmlspecialchars(get_setting('default_item_rows')); ?>" min="1" max="20">
                    </div>
                </div>
            </div>

            <div class="card">
                <h3>Security</h3>
                <div class="form-row">
                    <div class="form-group col-half">
                        <label>New Password (leave blank to keep current)</label>
                        <input type="password" name="new_password">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100 mb-10">Save Settings</button>
        </form>

        <div class="card">
            <h3>Backup & Restore</h3>
            <p>Backup all memos, settings, and images to a ZIP file.</p>
            <a href="api/backup.php" class="btn btn-secondary w-100 mb-10 text-center">Download Backup (ZIP)</a>

            <hr style="margin: 20px 0;">

            <form method="POST" action="api/restore.php" enctype="multipart/form-data" onsubmit="return confirm('Warning: Restoring will overwrite current data. Continue?');">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                <div class="form-group">
                    <label>Restore from Backup (ZIP)</label>
                    <input type="file" name="backup_file" accept=".zip" required>
                </div>
                <button type="submit" class="btn btn-danger w-100">Restore Backup</button>
            </form>
        </div>
    </div>
</body>
</html>
