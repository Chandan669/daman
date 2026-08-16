# Architecture Audit

## Current Architecture
The current application uses a monolithic, flat PHP structure with minimal routing.
- Routing is handled via simple string matching in `index.php`.
- Logic is mixed directly in route files (`app/public.php`, `app/admin.php`).
- Views are basic PHP includes.
- Hardcoded minimal configuration in `/config`.

## Required Architecture
- Needs a modular MVC pattern (Controllers, Models, Views).
- Service container or dependency injection for better testing.
- Implement middleware for Auth, CSRF, and Security Headers.

## Gaps
- No separation of concerns (Business logic in routing files).
- Missing model layer entirely (direct PDO queries in controllers).
- Incomplete config management.
