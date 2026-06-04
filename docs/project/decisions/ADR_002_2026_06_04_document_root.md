# ADR_002_2026_06_04: public/ as the Web Server Document Root

**Date:** 2026-06-04
**Status:** Accepted
**Deciders:** CypherWhisperer

---

## Context

A PHP application's document root is the directory the web server treats as the filesystem root for HTTP requests. Any file inside the document root is potentially accessible over HTTP. Any file outside it is not.

The simplest possible layout puts all PHP files in one directory that is also the document root. This is common in beginner LAMP setups and in the nixamp instance used for course exercises (`www/`). It means `vendor/`, `config/`, `.env`, and source files are inside the document root — accessible over HTTP if misconfigured.

DevLog has sensitive files that must never be web-accessible: `.env` (credentials), `vendor/` (Composer dependencies, potentially containing files with known paths), `config/app.php`, and all `src/` files.

---

## Decision

The web server document root is `public/`. All files outside `public/` are inaccessible over HTTP by construction — not by `.htaccess` rules, not by server configuration, but because the web server root does not include them.

The `public/index.php` front controller is the single entry point for all HTTP requests.

---

## Reasoning

Security by construction is stronger than security by configuration. A misconfigured server, a missing `.htaccess` rule, or an `AllowOverride None` directive cannot expose `vendor/` or `.env` if those paths are not inside the document root to begin with.

The front controller pattern also clarifies the application's request lifecycle: every HTTP request enters through one file, which loads the environment, boots the router, and dispatches. There is no way to bypass this entry point by guessing a file path.

The `www/` layout used in nixamp is appropriate for isolated class exercises where each script is a standalone file. It is not appropriate for an application with a dependency tree, credentials, and shared infrastructure code.

---

## Alternatives Considered

### www/ layout (all files in document root, like nixamp)

Rejected. Vendor files, config, and `.env` would be inside the document root. Preventing access requires correct `.htaccess` rules on every sensitive path — a fragile arrangement that breaks silently if `AllowOverride` is disabled or rules are misconfigured.

### Flat layout with .htaccess guards on sensitive paths

Rejected for the same reason. Defense-in-depth is valid but should not be the primary mechanism. The correct primary mechanism is not placing sensitive files in the document root.

---

## Consequences

**Positive:**
- `vendor/`, `src/`, `config/`, `.env`, `database/` are structurally inaccessible over HTTP
- Explicit, single request entry point (`public/index.php`)
- Consistent with Laravel, Symfony, and other PHP framework conventions — transferable pattern

**Negative / Trade-offs:**
- Caddy virtualHost config must point to `public/`, not the repo root
- `try_files {path} /index.php?{query}` rewrite must be correct for the front controller to receive all non-static requests
- Slightly more configuration than a flat `www/` layout

**Neutral / Operational:**
- `public/.htaccess` is included for Apache compatibility (course teaches Apache), even though Caddy is the devenv server
- `public/uploads/` is inside the document root intentionally — uploaded files must be web-accessible for display
