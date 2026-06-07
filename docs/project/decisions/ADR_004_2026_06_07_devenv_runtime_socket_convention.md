# ADR_004_2026_06_07: Use $DEVENV_RUNTIME for MariaDB Socket Path

**Date:** 2026-06-07
**Status:** Accepted
**Deciders:** CypherWhisperer

---

## Context

devenv computes a runtime directory dynamically at startup, using
`XDG_RUNTIME_DIR` (_on NixOS/systemd systems_) or `TMPDIR` (elsewhere) as
the base. The MariaDB socket is placed at `$DEVENV_RUNTIME/mysql.sock`.

The original devenv.nix scripts in both devlog and nixamp hardcoded the
socket path as `.devenv/state/mysql/mysql.sock` — *a path that reflected*
*an older devenv behaviour and does not match where devenv actually places the socket post-nixpkgs-update.*

This mismatch caused all MariaDB scripts (*dl-status, dl-db, dl-migrate*) to fail silently or report the database as unreachable, even though MariaDB was running correctly. The symptom was misdiagnosed as an ensureUsers failure for approximately two days before the socket path was identified as the actual cause via `find /run/user/1000 -name "*.sock"`.

---

## Decision

All devenv project scripts that reference the MariaDB socket must use `$DEVENV_RUNTIME/mysql.sock`. Hardcoded socket paths are prohibited.

---

## Reasoning

`$DEVENV_RUNTIME` is the only reliable reference because devenv's runtime
directory is computed dynamically and is not guaranteed to be stable across nixpkgs updates, machines, or environments.

Hardcoding the path produces silent failures that are difficult to diagnose — _the service appears broken when it is in fact reachable._

The environment variable is always set by devenv when scripts execute, so there is no availability concern.

---

## Alternatives Considered

### Hardcoded .devenv/state/mysql/mysql.sock

The original approach. Worked at some point but broke after a nixpkgs
update changed where devenv places the socket. Rejected because it is
fragile across updates and environments.

### config.devenv.root interpolation at eval time

Using `${config.devenv.root}/.devenv/state/mysql/mysql.sock` in the Nix
expression. Rejected for the same reason — _the path itself is wrong regardless of how it is constructed._

---

## Consequences

**Positive:**

- Scripts are robust across nixpkgs updates and machines
- Socket path is always correct regardless of devenv version or OS
- Convention is simple and easy to apply to new scripts

**Negative / Trade-offs:**

- `$DEVENV_RUNTIME` is only set inside a devenv shell; scripts run outside
  that context will fail. This is acceptable — _all dl-* scripts are devenv-internal by design._

**Neutral / Operational:**

- Apply to nixamp immediately (_same scripts, same fix_)
- All future devenv projects in this workspace adopt this convention from the start

---
## Amendment — 2026-06-07

Post-fix testing revealed that `ensureUsers` silently fails on MariaDB
11.x (the version now supplied by nixpkgs-unstable) even with the correct
socket path. The `devlog` user is not created on first init.

`dl-init` is added as an idempotent session-start script that creates the
user and database via root unix_socket auth, bypassing `ensureUsers`
entirely for the user-creation concern. `ensureUsers` is retained in
`devenv.nix` as a best-effort declaration but is no longer load-bearing.

The session convention (run `dl-init` after every `devenv up`) is the
operational fix. See `docs/source/devenv.nix.md` for the cheat sheet.
