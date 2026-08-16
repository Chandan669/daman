# CodeCanyon Release Guidelines

This package is prepared specifically for distribution on CodeCanyon.

## Preparation Checklist
1. Ensure `GHOST_PRODUCT_ITEM_ID` matches the assigned Envato Item ID in your private license server.
2. The `license-server` directory **must not** be included in the public ZIP provided to customers. It must be hosted privately by the author.
3. Configure the `GHOST_LICENSE_SERVER` variable in the installation package to point to your hosted license server URL.
4. Ensure `.git`, `.env` (with real credentials), and testing caches are removed before zipping.
