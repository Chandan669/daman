# Ghost License Server

Deploy this folder on a private HTTPS endpoint controlled by the author.

Configure environment variables:
- `GHOST_ENVATO_TOKEN` — your Envato Personal Token with the minimum permissions required to verify sales.
- `GHOST_PRODUCT_ITEM_ID` — your published CodeCanyon item ID.
- `GHOST_DB_DSN`, `GHOST_DB_USER`, `GHOST_DB_PASS` — license database.

The endpoint `/v1/activate` accepts `purchase_code`, `domain`, and `item_id`, calls Envato server-side, confirms the sale belongs to the configured item, and binds the purchase code to the first activated domain. Add a secure admin/deactivation workflow before production.

Important: never put your Envato Personal Token inside the downloadable customer package.
