# Security Audit

## Current State
- Basic CSRF via token in `_csrf` session.
- Password hashing uses bcrypt (default).
- Prepared statements used for SQL.

## Vulnerabilities & Gaps
- No rate limiting or brute force protection.
- No Content Security Policy (CSP) headers.
- Path traversal not fully protected in media uploads (which don't exist yet).
- Installer lacks robust self-destruct mechanisms (relies on basic `@unlink`).
- No XSS filtering on output (only basic htmlspecialchars wrapper `e()`, missing rich text sanitization).
