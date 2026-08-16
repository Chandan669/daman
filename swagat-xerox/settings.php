<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_login(); // Commented out for initial easy testing

$logo_url = get_logo_url();
$signature_url = get_signature_url();
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

        <form method="POST" action="api/save-settings.php" enctype="multipart/form-data">
            <div class="card">
                <h3>Security</h3>
                <div class="form-row">
                    <div class="form-group col-half">
                        <label>New Password (leave blank to keep current)</label>
                        <input type="password" name="new_password">
                    </div>
                </div>
            </div>

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
                <h3>Security</h3>
                <div class="form-row">
                    <div class="form-group col-half">
                        <label>New Password (leave blank to keep current)</label>
                        <input type="password" name="new_password">
                    </div>
                </div>
            </div>

            <div class="card">
                <h3>Branding Images</h3>

                <div class="form-row">
                    <div class="form-group col-half">
                        <label>Company Logo (Optional)</label>
                        <?php if ($logo_url): ?>
                            <div class="current-image">
                                <img src="<?php echo htmlspecialchars($logo_url); ?>" alt="Logo" style="max-height: 80px;">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="logo" accept="image/png, image/jpeg, image/jpg, image/webp">
                        <div class="checkbox-group mt-10">
                            <label>
                                <input type="checkbox" name="remove_logo" value="1"> Remove current logo
                            </label>
                        </div>
                    </div>

                    <div class="form-group col-half">
                        <label>Digital Signature (Optional)</label>
                        <?php if ($signature_url): ?>
                            <div class="current-image">
                                <img src="<?php echo htmlspecialchars($signature_url); ?>" alt="Signature" style="max-height: 80px;">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="signature" accept="image/png, image/jpeg, image/jpg, image/webp">
                        <div class="checkbox-group mt-10">
                            <label>
                                <input type="checkbox" name="remove_signature" value="1"> Remove current signature
                            </label>
                        </div>
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
                <div class="form-group">
                    <label>Default Print Layout (Memos per A4 page)</label>
                    <select name="default_print_layout">
                        <?php $def_layout = get_setting('default_print_layout'); ?>
                        <option value="1" <?php echo $def_layout == 1 ? 'selected' : ''; ?>>1 - Large Memo</option>
                        <option value="2" <?php echo $def_layout == 2 ? 'selected' : ''; ?>>2 - Half Page (Vertical)</option>
                        <option value="4" <?php echo $def_layout == 4 ? 'selected' : ''; ?>>4 - Quarter Page (2x2 Grid)</option>
                        <option value="6" <?php echo $def_layout == 6 ? 'selected' : ''; ?>>6 - Six per page (2x3 Grid)</option>
                        <option value="9" <?php echo $def_layout == 9 ? 'selected' : ''; ?>>9 - Nine per page (3x3 Grid)</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100">Save Settings</button>
        </form>
    </div>
</body>
</html>
