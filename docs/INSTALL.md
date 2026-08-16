# Ghost News & Magazine - Installation Guide

## Requirements
- PHP 8.2+
- MySQL 8.x / MariaDB compatible
- Apache with `mod_rewrite` enabled
- HTTPS (SSL Certificate)
- PHP Extensions: `pdo_mysql`, `curl`, `mbstring`, `fileinfo`, `intl`, `exif`

## Installation Steps

1. **Upload Files:** Upload the entire contents of the release ZIP to your server's public document root (e.g., `public_html`).
2. **Permissions:** Ensure the `storage/` directory and all its subdirectories are writable by the web server (e.g., `chmod -R 775 storage`).
3. **Run Installer:** Navigate to your website domain in a browser. The installer will automatically start.
4. **License Verification:** Enter your CodeCanyon Purchase Code. This verifies the purchase and binds it to your domain.
5. **Database Configuration:** Enter your MySQL database credentials (Host, Port, Database Name, Username, Password).
6. **Admin Account:** Set up your primary administrator email and password.
7. **Complete:** The installer will automatically build the database schema and finalize settings. Once complete, the installer file is securely removed.
