# Roadmap

## Current Focus — Milestone 1: Bootstrap

Get the project to a state where PHP executes, the database is reachable, Composer autoloading works, and environment variables load. Everything else builds on this.

---

## Milestone Breakdown

### Milestone 1 — Bootstrap
- [x] Repository structure
- [x] `flake.nix` + `devenv.nix` — LAMP stack with Nix flake
- [x] `composer.json` — `phpdotenv`, `cebe/markdown`
- [x] `database/schema.sql` — users, entries, attachments
- [x] `src/Core/Database.php` — PDO singleton
- [x] `public/.htaccess` + `public/index.php` — front controller skeleton
- [ ] `composer install` and verify autoloading
- [ ] `dl-migrate` and verify schema import
- [ ] End-to-end smoke test: browser hits `/`, PHP executes, no fatal errors

### Milestone 2 — Auth
- [ ] `src/Models/User.php` — `create()`, `findByEmail()`, `findById()`
- [ ] `src/Controllers/AuthController.php` — full register/login/logout
- [ ] `views/auth/login.php` and `register.php` — functional forms
- [ ] `src/Middleware/AuthMiddleware.php` — redirect unauthenticated requests
- [ ] Session hardening: `session_regenerate_id` on login

### Milestone 3 — Router + View
- [ ] `src/Core/Router.php` — METHOD + URI dispatch
- [ ] `src/Core/View.php` — render views wrapped in layout
- [ ] `views/layout.php` — base HTML layout with nav
- [ ] Register all routes in `public/index.php`

### Milestone 4 — Entries CRUD
- [ ] `src/Enums/EntryStatus.php` — backed enum: Draft / Published / Archived
- [ ] `src/Models/Entry.php` — full CRUD + soft delete + search
- [ ] `src/Controllers/EntryController.php` — index, show, create, store, edit, update, destroy
- [ ] All views in `views/entries/`
- [ ] Markdown rendering on `show.php` via `cebe/markdown`

### Milestone 5 — File Attachments
- [ ] `attachments` table already in schema
- [ ] `handleUpload()` with MIME validation via `fileinfo` extension
- [ ] Display attachments on entry show page

### Milestone 6 — Search & Filter
- [ ] `$_GET` form on entries index: `?q=&status=&sort=`
- [ ] `Entry::search()` — dynamic WHERE clause with prepared statements

### Milestone 7 — JSON API
- [ ] `src/Controllers/ApiController.php` — full CRUD
- [ ] Routes: `GET /api/entries`, `POST /api/entries`, `PUT /api/entries/{id}`, `DELETE /api/entries/{id}`
- [ ] Token auth via `Authorization: Bearer <token>` header

### Milestone 8 — Polish
- [ ] CSRF protection (token in session, verified on POST)
- [ ] Global exception handler (already in `index.php` skeleton)
- [ ] Pagination on entries index
- [ ] `composer audit` — dependency vulnerability check

---

## Planned — Infrastructure

### Flake Template Extraction
Once DevLog is stable, extract the LAMP devenv pattern into a reusable Nix flake template. DevLog (and nixamp) would become instantiations of the template rather than independent copies. This is the correct long-term architecture; deliberately deferred until there are enough instances to design the template from.

- [ ] Identify common parameters (PHP version, extensions, database name, document root, port)
- [ ] Design template `outputs` schema
- [ ] Migrate DevLog and nixamp to template consumers
- [ ] Publish template to a `lamp-devenv-template` repo

---

## Won't Do (in scope of this project)

- Multi-tenant SaaS architecture
- Email verification / password reset flow
- Public-facing user profiles
- Real-time features (WebSockets)
