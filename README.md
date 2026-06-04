# DevLog

A multi-user developer journal application built on a LAMP stack.

---

## What Is DevLog?

DevLog is a private, self-hosted web application where developers write and manage journal entries about their work — bugs they fixed, decisions they made, things they learned, things they're still unsure about.

Think of it as a structured engineering notebook. Not a public blog. Not a task tracker. A personal record of the technical journey, accessible from a browser, backed by a real database, and owned entirely by the person running it.

Each user gets their own isolated journal. Entries support Markdown, move through a lifecycle (Draft → Published → Archived), can carry file attachments, and are fully searchable. A JSON API exposes the same data for programmatic access — scripted exports, integrations, whatever the user needs.

---

## Why Does It Exist?

DevLog is the capstone project for a university internet programming course covering PHP backend development and relational databases. It is designed to touch every significant concept in the curriculum — not as isolated exercises but as a single coherent application where the pieces depend on each other.

The secondary purpose is practical: a working developer journal is genuinely useful. The project is intentionally scoped so that finishing it produces something worth keeping and running, not just something worth submitting.

---

## Who Is It For?

The primary user is a single developer (or a small team on a shared server) who wants:

- A private place to record technical decisions and working notes
- Search and status filtering across a growing history of entries
- Markdown rendering for entries that include code blocks, lists, and headings
- File attachments for screenshots, logs, and reference documents
- An API for scripted access to their own journal data

It is not designed for public readership, social features, or multi-tenant SaaS deployment. It is a personal tool.

---

## Daily Use Case

A typical session looks like this:

1. Open `http://localhost:8080` (or wherever the server is deployed)
2. Log in
3. Create a new entry — give it a title, write in Markdown, save as Draft
4. Attach a screenshot or log file if relevant
5. Promote to Published when the entry is complete
6. Later: search by keyword or filter by status to find old entries
7. Archive entries that are no longer relevant but shouldn't be deleted

The JSON API (`/api/entries`) allows fetching your entries from a script — for example, a weekly export or a dashboard integration.

---

## Tech Stack

| Layer       | Technology                              |
|-------------|-----------------------------------------|
| Runtime     | PHP 8.3                                 |
| Web server  | Caddy (FastCGI → PHP-FPM)               |
| Database    | MariaDB (via PDO + prepared statements) |
| DB UI       | Adminer                                 |
| Packages    | Composer (`vlucas/phpdotenv`, `cebe/markdown`) |
| Dev env     | Nix flake + devenv                      |

See [`docs/tech_stack.md`](docs/tech_stack.md) for rationale and alternatives considered.

---

## Project Structure

```
devlog/
├── public/           ← Caddy document root; all HTTP requests enter here
│   ├── index.php     ← Front controller
│   ├── .htaccess     ← Rewrite rules (Apache fallback)
│   └── assets/       ← CSS and JS
├── src/
│   ├── Controllers/  ← Request handling logic
│   ├── Models/       ← Database interaction (PDO)
│   ├── Middleware/   ← Auth guard
│   ├── Core/         ← Router, View renderer, Database singleton
│   └── Enums/        ← EntryStatus backed enum
├── views/            ← PHP view templates
├── config/           ← app.php (environment-aware config)
├── database/         ← schema.sql
├── docs/             ← All project documentation
├── scratch/          ← Gitignored; class exercises and experiments
├── devenv.nix        ← LAMP stack service definitions
├── flake.nix         ← Nix flake; owns devenv inputs
├── composer.json
├── .env.example
└── .gitignore
```

---

## Getting Started

### Prerequisites

- Nix with flakes enabled
- `direnv` with `nix-direnv`

### Setup

```bash
# 1. Clone the repository
git clone git@github.com:CypherWhisperer/devlog.git
cd devlog

# 2. Allow direnv to load the flake environment
direnv allow

# 3. Start all services (Caddy + PHP-FPM + MariaDB + Adminer)
devenv up

# 4. (New terminal, inside the project directory)
# Install PHP dependencies
composer install

# 5. Copy and configure environment file
cp .env.example .env
# .env is pre-configured for the devenv defaults; no changes needed locally

# 6. Import the database schema
dl-migrate

# 7. Open the application
# Web:     http://localhost:8080
# Adminer: http://localhost:8081
```

### Available Scripts (inside devenv shell)

| Command         | Effect                                |
|-----------------|---------------------------------------|
| `devenv up`     | Start Caddy, PHP-FPM, MariaDB, Adminer |
| `dl-status`     | Check whether all services are reachable |
| `dl-db`         | Open a MariaDB CLI session            |
| `dl-logs`       | Tail the PHP-FPM error log            |
| `dl-php-info`   | Dump PHP build info                   |
| `dl-migrate`    | Import `database/schema.sql`          |

---

## Development Milestones

See [`ROADMAP.md`](ROADMAP.md) for the full breakdown.

| Milestone | Focus                        | Status      |
|-----------|------------------------------|-------------|
| 1         | Bootstrap (env, DB, autoload)| In Progress |
| 2         | Auth (register, login, session) | Planned  |
| 3         | Router + View renderer       | Skeleton    |
| 4         | Entries CRUD                 | Skeleton    |
| 5         | File attachments             | Planned     |
| 6         | Search & filter              | Skeleton    |
| 7         | JSON API                     | Skeleton    |
| 8         | Polish (CSRF, pagination, audit) | Planned |

---

## Documentation

All project documentation lives in [`docs/`](docs/index.md).

- [`docs/overview.md`](docs/overview.md) — architecture and design summary
- [`docs/tech_stack.md`](docs/tech_stack.md) — stack decisions and rationale
- [`docs/project/decisions/`](docs/project/decisions/) — Architecture Decision Records
- [`docs/development/journal/`](docs/development/journal/) — session journal

---

## License

Personal/educational project. Not licensed for redistribution.
