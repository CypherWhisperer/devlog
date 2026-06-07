# devenv.nix
#
# ─────────────────────────────────────────────────────────────────────────────
# USAGE
# ─────────────────────────────────────────────────────────────────────────────
#
#   devenv up          → start Caddy + PHP-FPM + MariaDB + Adminer
#   devenv shell       → enter the shell with all tools in PATH
#   dl-status          → check whether all services are reachable
#   dl-db              → open a MariaDB CLI session against devlog
#   dl-logs            → tail the PHP-FPM error log
#   dl-php-info        → dump PHP build info to terminal
#   dl-migrate         → import schema.sql into the devlog database
#
#   Document root:  ./public/          (front controller — all requests → index.php)
#   Web:            http://localhost:8080
#   Adminer:        http://localhost:8081

{
  pkgs,
  config,
  ...
}:

{
  # ── PHP ───────────────────────────────────────────────────────────────────────
  languages.php = {
    enable = true;
    version = "8.3";

    # fileinfo added over nixamp: required by Milestone 5 (MIME validation on
    # file attachments). All other extensions carry over from nixamp unchanged.
    extensions = [
      "mysqli"
      "pdo"
      "pdo_mysql"
      "mbstring"
      "curl"
      "openssl"
      "tokenizer"
      "fileinfo"
    ];

    # Development ini values. display_errors = On is intentional here —
    # must be Off in production. Mirrors XAMPP development defaults.
    ini = ''
      display_errors = On
      error_reporting = E_ALL
      log_errors = On
      memory_limit = 256M
      upload_max_filesize = 64M
      post_max_size = 64M
    '';

    # FPM pool — Caddy forwards FastCGI requests to the Unix socket this
    # pool exposes. Socket path resolved at eval time via:
    #   config.languages.php.fpm.pools.web.socket
    fpm.pools.web = {
      settings = {
        "pm" = "dynamic";
        "pm.max_children" = 10;
        "pm.start_servers" = 2;
        "pm.min_spare_servers" = 1;
        "pm.max_spare_servers" = 5;
      };
    };
  };

  # ── Composer ──────────────────────────────────────────────────────────────────
  # Tier 3 (project-scoped) — DevLog requires vlucas/phpdotenv and cebe/markdown.
  # If PHP tooling grows across projects, migrate to a HM dev packages module.
  packages = [
    pkgs.php83Packages.composer
  ];

  # ── Caddy HTTP Server ─────────────────────────────────────────────────────────
  # Document root is ./public/ — all requests rewrite to index.php (front
  # controller pattern). Static assets (CSS, JS) are served directly.
  services.caddy = {
    enable = true;
    virtualHosts."http://localhost:8080" = {
      extraConfig = ''
        root * ${config.devenv.root}/public

        # PHP requests → FPM pool via FastCGI.
        php_fastcgi unix/${config.languages.php.fpm.pools.web.socket}

        # Static files served directly; everything else falls to index.php.
        file_server

        # Front controller rewrite: non-existent paths → index.php.
        try_files {path} /index.php?{query}
      '';
    };
  };

  # ── Adminer ───────────────────────────────────────────────────────────────────
  # Access at: http://localhost:8081
  # Login: server=127.0.0.1, user=devlog, password=devlog, database=devlog
  services.adminer = {
    enable = true;
    listen = "127.0.0.1:8081";
  };

  # ── MariaDB ───────────────────────────────────────────────────────────────────
  # initialDatabases and ensureUsers run only on first `devenv up` (when the
  # data directory does not yet exist). Re-running devenv up does not reset data.
  services.mysql = {
    enable = true;
    package = pkgs.mariadb;
    initialDatabases = [
      {
        name = "devlog";
      }
    ];

    ensureUsers = [
      {
        name = "devlog";
        password = "devlog";
        ensurePermissions = {
          "devlog.*" = "ALL PRIVILEGES";
        };
      }
    ];
  };

  # ── Scripts ───────────────────────────────────────────────────────────────────
  scripts.dl-init.exec = ''
      SOCK="$DEVENV_RUNTIME/mysql.sock"

      echo "Waiting for MariaDB socket..."
      for i in $(seq 1 30); do
        [ -S "$SOCK" ] && break
        sleep 1
      done
      [ ! -S "$SOCK" ] && echo "Timed out — is 'devenv up' running?" && exit 1

      echo "Ensuring devlog user and database exist..."
      ${pkgs.mariadb}/bin/mariadb --socket="$SOCK" --user=root <<SQL
        CREATE DATABASE IF NOT EXISTS \`devlog\`;
        CREATE USER IF NOT EXISTS 'devlog'@'localhost' IDENTIFIED BY 'devlog';
        GRANT ALL PRIVILEGES ON \`devlog\`.* TO 'devlog'@'localhost';
        FLUSH PRIVILEGES;
    SQL
      echo "Database and user ready."
  '';

  scripts.dl-status.exec = ''
    echo "=== DevLog Service Status ==="
    echo ""
    echo "[ Caddy / PHP ]"
    ${pkgs.curl}/bin/curl -s -o /dev/null -w "  HTTP Status: %{http_code}\n" \
      http://127.0.0.1:8080 \
      && echo "  Reachable:   YES" \
      || echo "  Reachable:   NO  (run 'devenv up')"
    echo ""
    echo "[ MariaDB ]"
    ${pkgs.mariadb}/bin/mysqladmin \
      --socket="$DEVENV_RUNTIME/mysql.sock" \
      --user=root \
      status 2>/dev/null \
      && echo "  Reachable:   YES" \
      || echo "  Reachable:   NO  (run 'devenv up')"
    echo ""
    echo "[ Adminer ]"
    ${pkgs.curl}/bin/curl -s -o /dev/null -w "  HTTP Status: %{http_code}\n" \
      http://127.0.0.1:8081 \
      && echo "  URL:         http://localhost:8081" \
      || echo "  Unreachable  (run 'devenv up')"
  '';

  scripts.dl-db.exec = ''
    ${pkgs.mariadb}/bin/mariadb \
      --socket="$DEVENV_RUNTIME/mysql.sock" \
      --user=devlog \
      --password=devlog \
      devlog
  '';

  scripts.dl-logs.exec = ''
    tail -f ${config.devenv.root}/.devenv/state/php-fpm/web.log 2>/dev/null \
      || echo "No FPM log yet — run 'devenv up' first"
  '';

  scripts.dl-php-info.exec = ''
    php -r "phpinfo();" | ${pkgs.gnugrep}/bin/grep -E \
      "PHP Version|Loaded Configuration|extension_dir|mysqli|pdo|mbstring|curl|openssl|fileinfo"
  '';

  scripts.dl-migrate.exec = ''
    echo "Importing schema.sql into devlog database..."
    ${pkgs.mariadb}/bin/mariadb \
      --socket="$DEVENV_RUNTIME/mysql.sock" \
      --user=devlog \
      --password=devlog \
      devlog < ${config.devenv.root}/database/schema.sql \
      && echo "Schema imported successfully." \
      || echo "Import failed — is 'devenv up' running?"
  '';

  # ── Shell entry banner ────────────────────────────────────────────────────────
  enterShell = ''
    mkdir -p ${config.devenv.root}/public/uploads
    echo ""
    echo "  ┌──────────────────────────────────────────────────┐"
    echo "  │   DevLog — LAMP Dev Environment                  │"
    echo "  │                                                  │"
    echo "  │   devenv up         → start all services         │"
    echo "  │   dl-init           → ensure DB user + database  │"
    echo "  │   dl-status         → check service health       │"
    echo "  │   dl-db             → MariaDB CLI                │"
    echo "  │   dl-logs           → PHP-FPM error log          │"
    echo "  │   dl-php-info       → PHP build info             │"
    echo "  │   dl-migrate        → import schema.sql          │"
    echo "  │                                                  │"
    echo "  │   Web:        http://localhost:8080              │"
    echo "  │   Adminer:    http://localhost:8081              │"
    echo "  │                                                  │"
    echo "  └──────────────────────────────────────────────────┘"
    echo ""
    echo "                    CHEAT SHEET:                                   "
    echo " FIRST TIME (fresh clone or after rm -rf .devenv/state/mysql)      "
    echo " ─────────────────────────────────────────────────────────────     "
    echo " devenv up        → start services (new terminal / background)     "
    echo " dl-init          → create user + database (safe to re-run)        "
    echo " dl-migrate       → import schema.sql                              "
    echo " dl-status        → confirm all green                              "
    echo ""
    echo ""
    echo "                EVERY SUBSEQUENT SESSION                           "
    echo " ─────────────────────────────────────────────────────────────     "
    echo " devenv up        → start services                                 "
    echo " dl-init          → ensure user/db exist (idempotent — always safe)"
    echo " dl-status        → confirm all green                              "
    echo " [work]                                                            "
    echo ""
    echo ""
    echo "          SCHEMA CHANGED (you edited database/schema.sql)          "
    echo " ─────────────────────────────────────────────────────────────     "
    echo " ⚠ dl-migrate drops and recreates tables — you will lose data.     "
    echo " Either: back up first with dl-dump (add this script when needed)  "
    echo " Or:     write a targeted ALTER and run it via dl-db               "
    echo " "
    echo " "
    echo "            NUCLEAR RESET (start completely fresh)                 "
    echo " ─────────────────────────────────────────────────────────────     "
    echo " devenv down (or Ctrl+C on devenv up)                              "
    echo " rm -rf .devenv/state/mysql                                        "
    echo " devenv up                                                         "
    echo " dl-init                                                           "
    echo " dl-migrate                                                        "
    echo ""
  '';
}
