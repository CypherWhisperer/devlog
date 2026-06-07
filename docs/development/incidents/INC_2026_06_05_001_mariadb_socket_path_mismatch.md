# INC-2026-06-05-001: MariaDB Reported Unreachable Due to Socket Path Mismatch

**ID:** INC_2026_06_05_001
**Date:** 2026-06-05
**Severity:** Medium
**Status:** Resolved
**Reported by:** CypherWhisperer

---

## Summary

All devlog MariaDB scripts reported the database as unreachable after a
nixpkgs update.

The failure was misdiagnosed as an `ensureUsers` breakage in devenv's mysql-configure process. The actual cause was a hardcoded socket path in every script pointing to a location devenv no longer uses.

---

## Timeline

| Time           | Event                                                                  |
| -------------- | ---------------------------------------------------------------------- |
| 2026-06-05 eve | `dl-status` reports MariaDB unreachable after nixpkgs update           |
| 2026-06-05 eve | Scripts updated from TCP to socket connection — _no improvement_       |
| 2026-06-06     | Root assumed to be ensureUsers silent failure; research begins         |
| 2026-06-06     | Nix GC run, .devenv wiped, full rebuild — _problem persists_           |
| 2026-06-06     | initialDatabases schema workaround attempted — _devenv rejects option_ |
| 2026-06-07     | find /run/user/1000 -name "*.sock" run — _actual socket located_       |
| 2026-06-07     | Scripts updated to $DEVENV_RUNTIME/mysql.sock — _all services green_   |

---

## Impact

- **Components affected:** `dl-status, dl-db, dl-migrate, devenv.nix`
- **Data affected:** None
- **Time lost:** ~2 days
- **Work affected:** Milestone 2 (Auth) blocked; dev environment considered
  unstable for the duration

---

## Root Cause

devenv places the MariaDB socket at `$DEVENV_RUNTIME/mysql.sock`. The
scripts hardcoded `.devenv/state/mysql/mysql.sock`, a path that reflected
older devenv behaviour.

After a nixpkgs update, the socket moved. The scripts could not find it and reported connection failure. MariaDB was running correctly throughout.

---

## Resolution

Replaced all hardcoded socket paths in devenv.nix scripts with
`$DEVENV_RUNTIME/mysql.sock`. Switched `dl-status` mysqladmin check to
`--user=root` (_unix_socket auth, no password_), which is the correct
credential for admin pings.

### Changes Made

| Type   | Reference                                                                      | Description                            |
| ------ | ------------------------------------------------------------------------------ | -------------------------------------- |
| Commit | _(fill in)_                                                                    | Socket path fix in devenv.nix          |
| ADR    | [ADR_004](../decisions/ADR_004_2026_06_07_devenv_runtime_socket_convention.md) | $DEVENV_RUNTIME convention established |

---

## Contributing Factors

- No existing convention for socket paths in devenv projects; each script was written with a path that seemed reasonable at the time
- Silent failure mode: scripts exited with a generic "unreachable" message rather than _"socket not found at path X"_, making the path mismatch invisible
- The symptom (_unreachable DB_) closely resembled a known devenv/ensureUsers reliability issue, which sent diagnosis in the wrong direction

---

## Prevention

- All devenv project scripts referencing MariaDB socket must use
  `$DEVENV_RUNTIME/mysql.sock` — _enforced by ADR_004_
- When a service reports unreachable, first verify the socket exists with `find /run/user/1000 -name "*.sock"` before investigating the service itself
- New devenv projects should include a `dl-socket` or equivalent diagnostic script that prints `$DEVENV_RUNTIME` and lists sockets found there

---

## Lessons Learned

The gap between _"service is broken"_ and _"I cannot reach the service"_ is meaningful and worth checking explicitly before going deep on the former. A ten-second find command would have resolved this in Session 3.

Hardcoded paths in dev tooling fail silently and are difficult to diagnose because the error message describes the symptom (_can't connect_) rather than the cause (_wrong path_). Prefer environment variables that devenv guarantees to set.

---

<!-- METADATA
Opened: 2026-06-05
Resolved: 2026-06-07
Related journal entry: [2026_06_07_mariadb_socket_path_mismatch](../journal/2026_06_07_mariadb_socket_path_mismatch.md)
-->
