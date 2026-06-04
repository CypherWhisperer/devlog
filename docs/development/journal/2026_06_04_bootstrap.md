# 2026-06-04 — Project Bootstrap: Environment, Scaffold, and Initial Documentation

**Date:** 2026-06-04
**Duration:** ~3 hours
**Repos touched:** `devlog`
**Modules touched:** `flake.nix`, `devenv.nix`, full `src/` skeleton, full `docs/` tree
**Phase:** Phase 0 — Bootstrap

---

## What I Worked On

Initial project setup for DevLog — a multi-user developer journal application that serves as the capstone project for the university internet programming course. The session covered environment architecture decisions, full repository scaffolding, PHP skeleton files, and all initial documentation.

---

## What Got Done

- Decided on `flake.nix` + `devenv.nix` as the environment pattern — own the flake, `use flake` in `.envrc`, `devenv.nix` stays as a portable module
- Created the devlog repository at `/home/cypher-whisperer/DATA/FILES/PROJECTS/PUBLIC/PERSONAL/WEB_APP_DEV/PERSONAL/DEVLOG`
- `flake.nix` — wraps devenv, pins nixpkgs and devenv inputs, exposes `devShells.x86_64-linux.default`
- `devenv.nix` — evolved from nixamp: `public/` document root, `devlog` database, Composer added as Tier 3, `fileinfo` extension, `dl-*` scripts including `dl-migrate`
- `.gitignore` — devenv state, `.env`, `vendor/`, uploads, `scratch/*` with `.gitkeep` exception
- `.env.example` — documented defaults matching devenv MariaDB config
- `composer.json` — `vlucas/phpdotenv ^5.6`, `cebe/markdown ^1.2`, PSR-4 autoload for `App\`
- `database/schema.sql` — users, entries (FULLTEXT index, soft delete), attachments
- `src/Core/Database.php` — PDO singleton
- `src/Core/Router.php` — METHOD + URI dispatch
- `src/Core/View.php` — layout-wrapping view renderer
- `src/Enums/EntryStatus.php` — backed enum with `canTransitionTo()` guard
- `src/Models/Entry.php` — create, findById, findByUser, update, search, softDelete
- `src/Models/User.php` — create, findByEmail, findById
- `src/Middleware/AuthMiddleware.php` — session auth guard
- `src/Controllers/` — Auth, Entry, Api skeletons with TODO stubs for future milestones
- `public/index.php` — front controller: dotenv, session hardening, exception handler, router bootstrap
- `public/.htaccess` — Apache rewrite rules (course compatibility)
- `views/` — layout, auth (login, register), entries (index, create) skeletons
- `config/app.php` — environment-aware config array
- `scratch/` with `.gitkeep` — gitignored workspace
- Full `docs/` tree: README, ROADMAP, CHANGELOG, overview.md, tech_stack.md, ADR_001 (dev environment), ADR_002 (document root), this journal entry

---

## Key Decisions Made

**Separate repo for DevLog** — nixamp is a course exercise repo; DevLog is an application project. Conflating them would mix commit histories and require conditional logic in a shared `devenv.nix`. Clean separation now.

**Explicit `flake.nix` over `devenv.yaml`** — devenv.yaml wraps an internal flake devenv manages on your behalf. Writing `flake.nix` gives ownership of input pins, aligns with `use flake` conventions, and puts the environment in a shape suitable for future template extraction. The `devenv.nix` module stays unchanged — the flake is just the outer wrapper.

**Template extraction deferred** — designing a LAMP template from one instance would over-fit to DevLog. Tracked as a ROADMAP item; the flake structure was designed with extraction in mind.

**`public/` as document root** — vendor/, src/, config/, .env are outside the web root by construction. Security by structure, not by .htaccess guards.

**`scratch/` for class exercises** — a gitignored directory inside the repo for course exercises and experiments. The `.gitkeep` exception keeps the directory present after cloning while ignoring all content inside it.

**Composer at Tier 3** — project-scoped via devenv `packages`, not global HM. Revisit when PHP tooling spans multiple projects.

---

## Where I Got Stuck

Nothing technically blocked. The main deliberation was the environment architecture question — whether to start with a flake immediately or with `devenv.yaml` and extract later. The resolution: a self-contained `flake.nix` for DevLog is not the same work as designing a shared template. The flake is ~30 lines of boilerplate; the template extraction is a separate design problem deferred until there are two or more instances to learn from.

---

## What I Learned

- `devenv.yaml` and an explicit `flake.nix` are functionally equivalent for a single project — the difference is ownership and composability. When the goal is future template extraction, the flake is strictly better even though it looks like more work upfront.
- The `nixpkgs.follows` pattern in the flake inputs (making devenv's nixpkgs follow the top-level nixpkgs pin) is the same evaluation context alignment pattern as the CypherOS HM/NixOS split — same class of problem.
- Front controller pattern: all HTTP goes through one file. This is what `try_files {path} /index.php?{query}` in Caddy does — not a redirect, a server-side rewrite before PHP sees the request.
- `IF NOT EXISTS` on all `CREATE TABLE` statements makes `dl-migrate` idempotent — safe to re-run without dropping and recreating.

---

## Open Questions

- Will Caddy's `try_files` interact cleanly with the `php_fastcgi` directive when both are in the same virtualHost block? To verify on first `devenv up`.
- `session_regenerate_id(true)` is in `AuthController::login()` — confirm the `true` parameter (delete old session) is correct for the security model.
- The `ApiController` token auth reads `$_SERVER['HTTP_AUTHORIZATION']` — verify this header is forwarded by Caddy (some servers strip it). May need `header_up Authorization {http.request.header.Authorization}` in the Caddy config.

---

## Next Session

Milestone 1 completion: `composer install`, `devenv up`, `dl-migrate`, smoke test that the browser hits `/` and PHP executes without fatal errors. Then move to Milestone 2 (Auth — register, login, session hardening).

---

<!--
Commit range (fill in after session):
devlog: [initial commit hash] → [short hash]
-->
