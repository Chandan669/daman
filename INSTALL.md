# Ghost News & Magazine — Installation Guide

## Requirements
- PHP 8.2+
- MySQL 5.7/8.x or MariaDB equivalent
- PHP extensions: cURL, Fileinfo, Mbstring, Intl, PDO/MySQL, JSON, OpenSSL
- HTTPS strongly recommended; required for production licensing.

## 1. Configure the author license endpoint
Before distributing the customer ZIP, configure `config/config.php`:
- `GHOST_LICENSE_SERVER` -> your HTTPS license-server URL
- `GHOST_PRODUCT_ITEM_ID` -> your CodeCanyon item ID after publication.

Keep the Envato Personal Token only on your private license server. Do not ship it to buyers. Envato documents that API applications need an API key/token and that commercial use has specific terms. See https://build.envato.com/api/ and the Envato API Terms.

## 2. Upload
Upload the ZIP contents to the hosting document root. Ensure PHP can write to `storage/` during installation.

## 3. Install
Open `/installer/`.
1. Enter the buyer's CodeCanyon purchase code.
2. The installer sends the code to your private license server.
3. Your license server verifies the sale with Envato and checks the product item ID.
4. Enter MySQL credentials and the first admin account.
5. The schema and starter content are created.
6. The installer writes `storage/.installed` and removes `installer/index.php`.

## 4. Admin
Open `/admin` and sign in with the administrator account created during installation.

## 5. Production hardening
- Use HTTPS.
- Restrict the license-server directory and its data storage.
- Keep the Envato token outside the downloadable package.
- Add a private license-admin/deactivation endpoint to support domain migration.
- Configure SMTP, AI provider keys, backups, CDN/object storage, and cron jobs before a production launch.

## Important licensing note
The purchase-code flow is designed as a server-side verification architecture. A self-contained customer ZIP cannot securely hide an author API token. For that reason Ghost uses a private author-controlled license server. The included license server calls Envato from the server, verifies the item ID, and binds a purchase code to a domain. This is a practical licensing implementation, not a guarantee that software can never be modified by a technically sophisticated licensee.
