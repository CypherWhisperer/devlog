# Tech Stack

## Runtime: PHP 8.3

PHP is the course requirement. 8.3 is the current stable release and the version devenv resolves via `languages.php.version = "8.3"`. Features used from modern PHP: backed enums (8.1), `readonly` properties where applicable, named arguments, `str_starts_with`.

## Web Server: Caddy + PHP-FPM

The course context is LAMP, where Apache is traditional. Caddy replaces Apache for the following reasons:

- Zero configuration for HTTPS (irrelevant locally, relevant if deployed)
- Native `php_fastcgi` directive — no module loading, no `.conf` files beyond the virtualHost block
- Nix packaging is cleaner than Apache's module system
- `try_files` rewrite to `index.php` is a single line vs Apache's multi-line `mod_rewrite` block

PHP-FPM manages a pool of PHP worker processes. Caddy forwards `.php` requests via FastCGI to the pool's Unix socket. The socket path is resolved at Nix evaluation time — no hardcoded paths.

Apache remains relevant: the `public/.htaccess` file is included for environments where Apache is the server, and the course covers Apache configuration. Caddy is the devenv server; Apache is the taught concept.

## Database: MariaDB via PDO

MariaDB is MySQL-compatible and is the default `services.mysql.package` in devenv's NixOS-based environment. All queries use PDO prepared statements — no raw string interpolation. PDO is configured with `ERRMODE_EXCEPTION` (throws on error) and `EMULATE_PREPARES = false` (real prepared statements, not client-side emulation).

**Why not SQLite?** The course covers MySQL/MariaDB specifically. SQLite would remove the server-client architecture, the user/permission model, and the connection pool concepts that are part of the curriculum.

## Package Management: Composer (Tier 3)

Composer is PHP's dependency manager. It is scoped to this project (Tier 3 — devenv `packages` block) rather than installed globally via Home Manager. This keeps the tool co-located with the project that needs it. If PHP tooling grows across multiple projects, Composer should migrate to a Home Manager dev packages module.

**Dependencies:**

| Package | Version | Purpose |
|---|---|---|
| `vlucas/phpdotenv` | ^5.6 | Load `.env` into `getenv()` / `$_ENV` |
| `cebe/markdown` | ^1.2 | Markdown → HTML rendering for entry bodies |

## Dev Environment: Nix Flake + devenv

The local development environment is defined entirely in Nix. `flake.nix` owns the input pins (nixpkgs, devenv). `devenv.nix` defines the services (Caddy, PHP-FPM, MariaDB, Adminer) and shell scripts.

**Why a flake rather than `devenv.yaml`?**

`devenv.yaml` is a simplified interface over the same underlying flake that devenv uses internally. Writing `flake.nix` explicitly means:

- Inputs are pinned to exact nixpkgs commits (reproducible across machines)
- The environment can be composed into a future shared template
- `use flake` in `.envrc` is the standard nix-direnv pattern, consistent with CypherOS conventions

`devenv.nix` remains the service definition file — the flake is purely the outer wrapper. This means the service configuration is portable and can be embedded into any flake that chooses to wrap it.

**Why devenv over a raw Nix flake `devShell`?**

devenv provides declarative service management (`devenv up` starting Caddy, MariaDB, Adminer as supervised processes) that a raw `devShell` does not. Writing process supervision from scratch in Nix is non-trivial. devenv's `services.*` module handles it cleanly.

## Adminer

Lightweight single-file PHP database UI. Served by devenv at `localhost:8081`. Used for inspecting table state during development. Not part of the application.
