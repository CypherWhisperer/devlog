# 2026-06-05 — Dropping the Flake: Migrating to devenv.yaml

<!-- The journal is informal. This is the human layer on top of git history. Write like you're explaining the session to yourself six months from now. What happened, what you figured out, what you're still unsure about. Honest > polished. -->

**Date:** 2026-06-05
**Duration:** ~1 hour
**Repos touched:** `devlog`
**Files touched:** `flake.nix`, `flake.lock`, `devenv.yaml`, `.envrc`
**Milestone:** Environment stabilisation (_pre-Milestone 1_)

---

## What I Worked On

Abandoned the `flake.nix`-based devenv setup after successive failures rooted in host tooling version mismatches (_documented in the CypherOS journal entry for the same date_). Migrated DevLog's dev environment to `devenv.yaml` — _the same pattern nixamp uses successfully_ — to unblock development.

---

## What Got Done

- Removed `flake.nix` and `flake.lock` from the repository
- Created `devenv.yaml` with `nixpkgs` and `pre-commit-hooks` inputs
- Updated `.envrc` from `use flake` to `use devenv`
- Cleared stale `.direnv/` cache and re-ran `direnv allow`
- Confirmed devenv shell activates automatically on `cd` — _DevLog banner visible_
- Confirmed `composer install` succeeds inside the devenv shell
- Ran `dl-migrate` — schema imported into MariaDB successfully
- Ran `dl-status` — Caddy, MariaDB, and Adminer all reachable
- Smoke test passed: `http://localhost:8080` responds, PHP executes, front controller live
- Committed the migration; repository now in a clean, working initial state

---

## Key Decisions Made

Dropped `flake.nix` in favour of `devenv.yaml`. The decision is captured formally in [ADR_003](../../project/decisions/ADR_003_2026_06_05_revert_to_devenv_yaml.md). The short version: flakes are uncharted territory at this stage, the failures were in the host tooling integration layer (_not the flake itself_), and `devenv.yaml` has a proven track record via nixamp. Getting unblocked now is worth more than holding the line on a pattern that can't yet be maintained confidently.

The flake approach is not abandoned permanently — _it's deferred until the skill to manage it is in place._ See [ADR_003](../../project/decisions/ADR_003_2026_06_05_revert_to_devenv_yaml.md) for the full reasoning and the planned revisit trigger.

---

## Where I Got Stuck

The `nix flake update` after the first round of fixes introduced new evaluation errors rather than resolving the existing ones. Each fix opened a new failure mode — _`self` argument rejected by the pinned devenv version, then `--no-warn-dirty` rejected by the current Nix, then post-update evaluation errors after `nix flake update`_.

The pattern was clear: the flake integration layer between devenv, nix-direnv, and the system Nix has enough version-sensitive surface area that managing it confidently requires a deeper understanding of the flake input graph than is currently in place.

The decision to step back was the right call. Continuing to chase flake errors would have consumed the session with zero progress on the actual project.

---

## What I Learned

1. **`devenv.yaml` and `flake.nix` are not fundamentally different in what they produce.**
	- `devenv.yaml` is a simplified interface that devenv uses to generate an internal flake on your behalf.
	- The `devenv.nix` service definition is identical either way. The difference is ownership of the input pins and the integration surface with the host tooling.
	- For a single developer on a known machine, `devenv.yaml` is strictly simpler.

2. **The flake integration layer has meaningful version-sensitive surface area.**
	- The chain is: system Nix → nix-direnv → `use flake` → `devenv.lib.mkShell` → devenv's internal evaluation.
	- Each link in that chain has version requirements relative to the others.
	- When they drift — _as happened here with `nix-direnv` < 3.0.7 passing `--no-warn-dirty` to a Nix that no longer accepts it_ — the failure modes are cryptic and cascade.
	- Managing this confidently requires understanding the full chain.

3. **Know when to step back.**
	- The flake setup was conceptually correct.
	- The failures were real but solvable.
	- The question was whether solving them was the right use of time given current skill level and project priority. It wasn't.
	- Recognising that and pivoting rather than continuing to chase the errors is a better engineering decision than stubbornness about the "right" approach.

4. **`nix develop --impure` as a diagnostic boundary.**
	- If `nix develop --impure` works but `direnv allow` fails, the flake is correct and the problem is in the host tooling integration.
	- This boundary is worth remembering — _it cleanly separates "flake problem" from "host tooling problem"._

---

## Open Questions

- At what point does it make sense to revisit the flake approach?
	- The likely trigger is: working across multiple machines with different system states, or contributing to a project where someone else needs to reproduce the environment without nixamp as a prior reference.
	- At that point the reproducibility guarantees of a owned `flake.lock` become worth the complexity.

- The `devenv.yaml` approach means devenv manages the input pins internally. How do you pin a specific nixpkgs version in `devenv.yaml` if a future nixpkgs update breaks something? Worth understanding before that becomes urgent.

- Is there a `devenv lock` equivalent — a lockfile that pins the `devenv.yaml` inputs the same way `flake.lock` pins flake inputs? (`devenv.lock` exists — confirm it behaves as expected and commit it.)

---
## Next Session

Environment is stable and Milestone 1 is complete. Move to Milestone 2: Auth.

- `src/Controllers/AuthController.php` — _full `register()` implementation with validation and duplicate check_
- `src/Models/User.php` — _already scaffolded, verify `create()` and `findByEmail()`_
- `views/auth/register.php` and `login.php` — _functional forms with error display_
- Session hardening: confirm `session_regenerate_id(true)` fires on login
- End-to-end test: register a user, log in, see the entries index, log out

---

<!--
Commit range (fill in after session):
devlog: [migration commit hash] → [short hash]
-->
