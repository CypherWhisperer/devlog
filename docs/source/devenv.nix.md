<!--
Deliberate annotation — forcing the self to articulate why every construct exists before moving past it. The payoff isn't the doc file; it's the cognitive forcing function. When you can't write a sentence explaining a block, you've found a gap.
-->

# devenv.nix — DevLog LAMP Stack Definition

> Declares the full local development environment for DevLog: PHP 8.3,
> Caddy, PHP-FPM, MariaDB, Adminer, and all project scripts.

**File:** `devenv.nix`
**Environment:** devenv (via `devenv.yaml` + direnv `use devenv`)
**Status:** Stable
**Last reviewed:** 2026-06-07

---

## Responsibility

**Does:**

- Declares all services required to run the DevLog LAMP stack locally
- Defines all `dl-*` helper scripts as devenv-managed shell commands
- Prints the shell entry banner and ensures `public/uploads/` exists on activation

**Does not:**

- Manage application schema — _that is `database/schema.sql` imported via `dl-migrate`_
- Handle production configuration — _this file is dev-only_
- Declare PHP application code or routing — _that is `public/index.php` and `src/`_

---

## Block Analysis

---

### Block 1 — `languages.php`

**What is this?** devenv's PHP language module. Enables PHP 8.3 with a
specific set of extensions and custom `php.ini` overrides.

**What does it do?** Installs the PHP 8.3 interpreter into the devenv
shell's PATH and activates the listed extensions. The `ini` block
injects additional directives into the effective `php.ini` at runtime.

**Why is it here?** DevLog is a PHP application. All extensions listed
are required by the application layer — _`mysqli` and `pdo_mysql` for database access, `mbstring` for string handling, `curl` and `openssl` for HTTP and crypto, `tokenizer` for Composer, `fileinfo` for MIME validation on file uploads (added over nixamp for Milestone 5)._

```nix
languages.php = {
  enable = true;
  version = "8.3";
  extensions = [
    "mysqli" "pdo" "pdo_mysql" "mbstring"
    "curl" "openssl" "tokenizer" "fileinfo"
  ];
  ini = ''
    display_errors = On
    error_reporting = E_ALL
    log_errors = On
    memory_limit = 256M
    upload_max_filesize = 64M
    post_max_size = 64M
  '';
  ...
};
```

> ⚠️ `display_errors = On` surfaces PHP errors in the browser. This is
> intentional in development but must be `Off` in production.

---

### Block 2 — `languages.php.fpm.pools.web`

**What is this?** Configuration for a PHP-FPM process pool named `web`.

**What does it do?** Spawns a pool of PHP worker processes managed by
FPM. Exposes a Unix socket that Caddy uses to forward FastCGI requests.
The socket path is resolved at Nix eval time via
`config.languages.php.fpm.pools.web.socket` — _it is never hardcoded._

**Why is it here?** Caddy does not execute PHP directly. It speaks
FastCGI to FPM, which executes PHP scripts in managed worker processes.
`pm = dynamic` is appropriate for local dev: workers spawn on demand
between the min/max bounds rather than being pre-forked.

```nix
fpm.pools.web = {
  settings = {
    "pm"                   = "dynamic";
    "pm.max_children"      = 10;
    "pm.start_servers"     = 2;
    "pm.min_spare_servers" = 1;
    "pm.max_spare_servers" = 5;
  };
};
```

---

### Block 3 — `packages`

**What is this?** A list of additional Nix packages to add to the
devenv shell's PATH.

**What does it do?** Makes `composer` available as a command in the
shell.

**Why is it here?** DevLog requires Composer for dependency management
(`vlucas/phpdotenv`, `cebe/markdown`). Composer is project-scoped
(Tier 3) rather than a global HM package because not every project
needs it. If Composer use grows across multiple projects, migrate to
a HM dev packages module.

```nix
packages = [ pkgs.php83Packages.composer ];
```

---

### Block 4 — `services.caddy`

**What is this?** devenv's Caddy service declaration.

**What does it do?** Starts a Caddy HTTP server on port 8080. Serves
static files directly from `./public/`. Forwards PHP requests to the
FPM pool via FastCGI. Rewrites all non-existent paths to `index.php`
(front controller pattern).

**Why is it here?** DevLog uses a front controller — all HTTP requests
enter through `public/index.php`, which dispatches to the appropriate
handler. The `try_files {path} /index.php?{query}` directive is what
makes clean URLs work. Port 8080 is used to avoid requiring root.

```nix
services.caddy = {
  enable = true;
  virtualHosts."http://localhost:8080" = {
    extraConfig = ''
      root * ${config.devenv.root}/public
      php_fastcgi unix/${config.languages.php.fpm.pools.web.socket}
      file_server
      try_files {path} /index.php?{query}
    '';
  };
};
```

> Note: `.htaccess` is an Apache-only feature. It has no effect under
> Caddy. All rewrite rules must be declared in `extraConfig` here.

---

### Block 5 — `services.adminer`

**What is this?** devenv's Adminer service declaration.

**What does it do?** Starts an Adminer instance on port 8081 — a
lightweight web-based database UI.

**Why is it here?** Provides a GUI alternative to the `dl-db` CLI for
inspecting and querying the database during development.

```nix
services.adminer = {
  enable = true;
  listen = "127.0.0.1:8081";
};
```

> Login details: server `127.0.0.1`, user `devlog`, password `devlog`,
> database `devlog`.

---

### Block 6 — `services.mysql`

**What is this?** devenv's MariaDB service declaration.

**What does it do?** Starts a MariaDB instance. On first `devenv up`
(when `.devenv/state/mysql/` does not yet exist), creates the `devlog`
database and the `devlog` user with full privileges on it.
`initialDatabases` and `ensureUsers` are one-time bootstrappers — they
do not re-run on subsequent starts and do not reset data.

**Why is it here?** DevLog requires a relational database. MariaDB is
the M in LAMP. `pkgs.mariadb` is specified explicitly to pin the
engine — devenv's default could change across nixpkgs updates.

```nix
services.mysql = {
  enable   = true;
  package  = pkgs.mariadb;
  initialDatabases = [ { name = "devlog"; } ];
  ensureUsers = [
    {
      name     = "devlog";
      password = "devlog";
      ensurePermissions = { "devlog.*" = "ALL PRIVILEGES"; };
    }
  ];
};
```

> ⚠️ **Socket path note**: ([INC_2026_06_05_001](../development/incidents/INC_2026_06_05_001_mariadb_socket_path_mismatch.md)): The MariaDB socket is
> placed at `$DEVENV_RUNTIME/mysql.sock` — a dynamically computed path.
> Never hardcode this path in scripts. Always reference
> `$DEVENV_RUNTIME/mysql.sock`. See [ADR_004](../project/decisions/ADR_004_2026_06_07_devenv_runtime_socket_convention.md).

---

### Block 7 — `scripts`

**What is this?** A set of named shell scripts exposed as commands in
the devenv shell.

**What does it do?** Each entry becomes a callable command when inside
the devenv shell. Scripts have access to `$DEVENV_RUNTIME` and all
devenv-managed paths.

**Why is it here?** Encodes operational knowledge as code. Instead of
remembering flags and paths, developers run named commands. Scripts also
serve as living documentation of how the services are connected.

| Script        | Purpose                                                  |
| ------------- | -------------------------------------------------------- |
| `dl-init`     | Idempotent bootstrap — creates user + database if absent |
| `dl-status`   | Health check for all three services                      |
| `dl-db`       | Interactive MariaDB CLI as the devlog user               |
| `dl-logs`     | Tail PHP-FPM error log                                   |
| `dl-php-info` | Dump PHP build info and loaded extensions                |
| `dl-migrate`  | Import `database/schema.sql` into devlog database        |

> `dl-status` uses `--user=root` for the mysqladmin ping — root
> authenticates via unix_socket (no password). The `devlog` user is
> used for all application-level script access (`dl-db`, `dl-migrate`).


> **Session convention:** run `dl-init` at the start of every session before any database work. It is fully idempotent and takes under a second if the user and database already exist. This compensates for `ensureUsers` silently failing on some nixpkgs configurations. See INC_2026_06_05_001.

---

### Block 8 — `enterShell`

**What is this?** A shell hook that runs once on devenv shell activation.

**What does it do?** Creates `public/uploads/` if it does not exist,
then prints the service reference banner.

**Why is it here?** `public/uploads/` is gitignored but required at
runtime for file attachment handling. Creating it here ensures it always
exists without committing it. The banner surfaces the most commonly
needed commands without requiring the developer to consult documentation.

```nix
enterShell = ''
  mkdir -p ${config.devenv.root}/public/uploads
  echo "..."
'';
```

---

## Dependencies

**devenv services used:**

- `languages.php` — PHP interpreter and FPM
- `services.caddy` — HTTP server
- `services.adminer` — database UI
- `services.mysql` — MariaDB

**nixpkgs packages required:**

- `pkgs.mariadb` — MariaDB server and client binaries
- `pkgs.php83Packages.composer` — PHP dependency manager
- `pkgs.curl` — used in dl-status health checks
- `pkgs.gnugrep` — used in dl-php-info

**Runtime environment variables:**

- `$DEVENV_RUNTIME` — set by devenv; base path for all service sockets
- `config.devenv.root` — resolved at Nix eval time; absolute path to project root
- `config.languages.php.fpm.pools.web.socket` — resolved at Nix eval time; FPM socket path

---

## Known Limitations

- `ensureUsers` only runs on first init. If the data directory already
  exists from a broken or incomplete previous run, wipe it with
  `rm -rf .devenv/state/mysql` and restart.
	- **`ensureUsers` silently fails on some nixpkgs/MariaDB 11.x configurations,leaving no user created after first init. `dl-init` is the workaround run it at the start of every session. See INC_2026_06_05_001 and ADR_004.**
- `display_errors = On` in `ini` is dev-only. There is no production
  configuration variant — this environment is not intended for deployment.
- `dl-logs` tails the FPM log only. Caddy logs are not surfaced by a
  dedicated script.

---

## Related

| Type      | Reference                                                                                    |
| --------- | -------------------------------------------------------------------------------------------- |
| ADR       | [ADR_004](../project/decisions/ADR_004_2026_06_07_devenv_runtime_socket_convention.md)      |
| Incident  | [INC_2026_06_05_001](../development/incidents/INC_2026_06_05_001_mariadb_socket_path_mismatch.md) |
| Schema    | `database/schema.sql`                                                                        |
| Companion | [`devenv.yaml.md`](./devenv.yaml.md)                                                         |

---

<!-- METADATA
File:    devenv.nix
Created: 2026-06-04
Updated: 2026-06-07
-->
