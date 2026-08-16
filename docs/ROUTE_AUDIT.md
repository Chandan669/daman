# Route Audit

## Current Routes
- `/installer` (Handles install logic)
- `/admin/login` (Auth)
- `/admin/logout` (Auth)
- `/admin` (Dashboard)
- `/admin/posts` (CRUD)
- `/admin/categories` (CRUD)
- `/admin/settings` (CRUD)
- `/` (Home)
- `/article/{slug}`
- `/category/{slug}`
- `/search`
- `/sitemap.xml`
- `/rss.xml`

## Gaps
- Need modular route definitions.
- Missing API routes (`/api/v1/*`).
- Missing webhook endpoints.
- Admin routes are clustered into a single file.
