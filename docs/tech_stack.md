# Tech Stack

## Runtime: PHP 8.3

PHP is the course requirement. 8.3 is the current (_as of writting this 2026-06-06_) stable release and the version devenv resolves via `languages.php.version = "8.3"`. Features used from modern PHP: backed enums (8.1), `readonly` properties where applicable, named arguments, `str_starts_with`.

## Web Server: Caddy + PHP-FPM

The course context is LAMP, where Apache is traditional. Caddy replaces Apache for the following reasons:

- Zero configuration for HTTPS (_irrelevant locally, relevant if deployed_)
- Native `php_fastcgi` directive — _no module loading, no `.conf` files beyond the virtualHost block_
- Nix packaging is cleaner than Apache's module system
- `try_files` rewrite to `index.php` is a single line vs Apache's multi-line `mod_rewrite` block

PHP-FPM manages a pool of PHP worker processes. Caddy forwards `.php` requests via FastCGI to the pool's Unix socket. The socket path is resolved at Nix evaluation time — _no hardcoded paths._

Apache remains relevant: the `public/.htaccess` file is included for environments where Apache is the server, and the course covers Apache configuration. Caddy is the devenv server; Apache is the taught concept.

## Database: MariaDB via PDO

MariaDB is MySQL-compatible and is the default `services.mysql.package` in devenv's NixOS-based environment. All queries use PDO prepared statements — _no raw string interpolation._ PDO is configured with `ERRMODE_EXCEPTION` (_throws on error_) and `EMULATE_PREPARES = false` (_real prepared statements, not client-side emulation_).

**Why not SQLite?** The course covers MySQL/MariaDB specifically. SQLite would remove the server-client architecture, the user/permission model, and the connection pool concepts that are part of the curriculum.

## Package Management: Composer (_Tier 3_)

Composer is PHP's dependency manager. It is scoped to this project (_Tier 3 — devenv `packages` block_) rather than installed globally via Home Manager. This keeps the tool co-located with the project that needs it. If PHP tooling grows across multiple projects, Composer should migrate to a Home Manager dev packages module.

**Dependencies:**

|Package|Version|Purpose|
|---|---|---|
|`vlucas/phpdotenv`|^5.6|Load `.env` into `getenv()` / `$_ENV`|
|`cebe/markdown`|^1.2|Markdown → HTML rendering for entry bodies|

## Dev Environment: devenv + devenv.yaml

The local development environment is defined entirely in Nix via devenv. `devenv.yaml` declares the input pins (_nixpkgs, pre-commit-hooks_). `devenv.nix` defines the services (_Caddy, PHP-FPM, MariaDB, Adminer_) and shell scripts. `.envrc` uses `use devenv`, which devenv hooks into direnv automatically.

**Why devenv.yaml rather than an explicit flake.nix?**

An explicit `flake.nix` was the original approach (_see [ADR_001](./project/decisions/ADR_001_2026_06_04_dev_environment.md), [ADR_003](./project/decisions/ADR_003_2026_06_05_revert_to_devenv_yaml.md)_). It was reverted after the flake integration layer — _system Nix → nix-direnv → `use flake` → `devenv.lib.mkShell`_ — produced version-sensitive failures during bootstrap that were rooted in host tooling mismatches outside the project's control.

`devenv.yaml` is a simplified input declaration that devenv converts into an internal flake on the project's behalf. The functional outcome is identical: same services, same shell, same `devenv.nix`. The difference is that devenv manages the input pins internally via `devenv.lock` rather than the project owning a `flake.lock` directly. For a single-developer project on a known machine, this is the correct trade-off.

The explicit `flake.nix` approach remains the long-term target — _it offers transparent input ownership and positions the environment for LAMP template extraction._ It is deferred until the skill to manage the integration chain confidently is in place. See ADR_003 for the full decision record.

**Why devenv over a raw Nix flake devShell?**

devenv provides declarative service management (_`devenv up` starting Caddy, MariaDB, Adminer as supervised processes_) that a raw `devShell` does not. Writing process supervision from scratch in Nix is non-trivial. devenv's `services.*` module handles it cleanly.

**Why devenv over Docker Compose?**

Docker introduces a second dependency management system operating in parallel with Nix, with no benefit on a NixOS machine where devenv already solves the same problem. See ADR_001 for the full evaluation.

## Adminer

Lightweight single-file PHP database UI. Served by devenv at `localhost:8081`. Used for inspecting table state during development. Not part of the application.
