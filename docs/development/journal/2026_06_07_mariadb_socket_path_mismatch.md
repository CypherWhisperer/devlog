# [2026-06-07] The MariaDB That Was Never Broken

**Date:** 2026-06-07
**Duration:** ~3 hours (_this session_); ~2 days total across sessions 3–4
**Repos touched:** `devlog`
**Files touched:** [`devenv.nix`]
**Milestone:** Dev Environment Stabilisation

---

## What I Worked On

Continued hunting the MariaDB connection failure that had persisted since Session 3.

The symptom was `dl-status` consistently reporting MariaDB unreachable, `ensureUsers` silently failing, and no devlog user or database accessible after `devenv up`.

---

## What Got Done

- Identified the actual root cause: _socket path mismatch_
- Updated all three scripts (`_dl-status, dl-db, dl-migrate_`) to use
  `$DEVENV_RUNTIME/mysql.sock`
- Switched `dl-status` mysqladmin check to `--user=root`
- Confirmed all services green via dl-status
- Reverted devenv.nix back to ensureUsers (_it was working all along_)
- Established nixamp carries the same fix (_same root cause, same change_)

---

## Key Decisions Made

- Reverted the ADR_004 direction (_dropping ensureUsers for initialDatabases schema_) — not necessary, `ensureUsers` was never the problem
- Adopted `$DEVENV_RUNTIME` as the canonical socket reference in all scripts going forward; never hardcode socket paths in devenv projects

---

## Where I Got Stuck

- Two days. The scripts were reporting MariaDB as unreachable, which led to the assumption that ensureUsers was failing post-nixpkgs-update. That sent the session down a long detour: researching devenv's mysql-configure process, MariaDB 11.x binary renames, initialDatabases schema workarounds, initialScript (_which doesn't exist in devenv's module_), and various upstream bug threads.

- None of it was relevant. The database was running. The scripts just couldn't find the socket because the path changed and was hardcoded.

- The actual fix was two characters: replacing the hardcoded path with an environment variable.

---

## What I Learned

1. Always verify the socket exists and is reachable before assuming a service is broken. `find /run/user/1000 -name "*.sock"` took ten seconds and immediately showed the socket at `$DEVENV_RUNTIME/mysql.sock` — a path the scripts had never been pointed at.

2. Hardcoded paths in dev tooling are silent time bombs. `$DEVENV_RUNTIME` is the correct reference because devenv computes its runtime dir dynamically (_based on XDG_RUNTIME_DIR or TMPDIR_), and that path changes across sessions and environments.

3. The gap between "_service is broken_" and "_I cannot reach the service_" is worth pausing on before going deep on the former.

---

## Open Questions

- Does `nixamp` have any other hardcoded paths beyond the socket that could surface the same class of issue?

---

## Next Session

- Apply the socket path fix to nixamp
- Run dl-migrate on devlog to import schema.sql and confirm the full stack is working end to end
- Begin Milestone 2 (Auth)

---

<!--
Commit range (fill in after session):
devlog: [short hash] → [short hash]
-->
