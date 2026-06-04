# Changelog

All notable changes to DevLog are recorded here.
Format loosely follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

---

## [Unreleased]

### Added
- `flake.nix` — Nix flake wrapping devenv; owns inputs, exposes `devShells.x86_64-linux.default`
- `devenv.nix` — LAMP stack: Caddy + PHP-FPM 8.3 + MariaDB + Adminer; evolved from nixamp with `fileinfo` extension, Composer, `dl-*` scripts, and `dl-migrate` schema import helper
- `composer.json` — `vlucas/phpdotenv ^5.6`, `cebe/markdown ^1.2`, PSR-4 autoload for `App\`
- `database/schema.sql` — `users`, `entries` (with FULLTEXT index + soft delete), `attachments` tables
- `src/Core/Database.php` — PDO singleton reading credentials from environment
- `src/Core/Router.php` — METHOD + URI dispatch
- `src/Core/View.php` — view renderer with layout wrapping
- `src/Enums/EntryStatus.php` — backed enum: Draft / Published / Archived with transition guard
- `src/Models/Entry.php` — create, findById, findByUser, update, search, softDelete
- `src/Models/User.php` — create, findByEmail, findById
- `src/Middleware/AuthMiddleware.php` — session-based auth guard
- `src/Controllers/AuthController.php` — login/logout/register skeletons
- `src/Controllers/EntryController.php` — index/create skeletons, TODO stubs for Milestone 4
- `src/Controllers/ApiController.php` — token auth scaffold, TODO stubs for Milestone 7
- `public/index.php` — front controller with exception handler, session hardening, router bootstrap
- `public/.htaccess` — rewrite all non-file requests to `index.php`
- `views/layout.php`, `views/auth/login.php`, `views/auth/register.php`, `views/entries/index.php`, `views/entries/create.php` — view skeletons
- `config/app.php` — environment-aware config array
- `.env.example` — documented defaults matching devenv
- `.gitignore` — devenv state, `.env`, `vendor/`, `public/uploads/*`, `scratch/*`
- Full `docs/` tree: `README.md`, `ROADMAP.md`, `CHANGELOG.md`, `docs/overview.md`, `docs/tech_stack.md`, ADRs, journal entry
- `scratch/` directory with `.gitkeep` — gitignored workspace for class exercises
