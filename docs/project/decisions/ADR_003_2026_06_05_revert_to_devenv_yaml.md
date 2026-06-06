# ADR_003_2026_06_05: Revert Dev Environment from flake.nix to devenv.yaml

**Date:** 2026-06-05 **Status:** Accepted **Deciders:** CypherWhisperer

<!-- Supersedes: ADR_001 (partially — ADR_001's reasoning for devenv over Docker still holds; only the flake vs devenv.yaml choice is reversed here) -->

---

## Context

ADR_001 established `flake.nix` as the dev environment wrapper for DevLog, with `devenv.nix` as the service definition module and `use flake` in `.envrc`. The reasoning was sound: explicit input ownership, auditable `flake.lock`, and a structure positioned for future LAMP template extraction.

In practice, the flake integration layer produced three successive evaluation failures during the bootstrap session:

1. `devenv was not able to determine the current directory` — devenv's `mkShell` needs `self` in newer versions to resolve the project root, but the initially pinned devenv version (`f693b472`) predated that API and rejected `self` as an unexpected argument.
2. After `nix flake update` resolved the devenv version mismatch, a host-side failure emerged: `hm-nix-direnv.sh:41: --no-warn-dirty: command not found`. The `nix-direnv` package provided by CypherOS's HM configuration was older than 3.0.7, which removed `--no-warn-dirty` from its internal Nix calls. Current system Nix no longer accepts that flag; the evaluation aborted before the devenv shell could build.
3. A further `nix flake update` on CypherOS to resolve the `nix-direnv` version lag introduced new evaluation errors.

Each fix opened a new failure mode. The pattern revealed that managing the flake integration chain — system Nix → nix-direnv → `use flake` → `devenv.lib.mkShell` → devenv's internal evaluation — requires confident understanding of how these version constraints interact. That understanding is not yet in place.

Meanwhile, nixamp — which uses `devenv.yaml` and `use devenv` — works correctly on the same machine with zero integration issues. The approach is proven.

---

## Decision

DevLog's dev environment is reverted to `devenv.yaml` + `use devenv`. `flake.nix` and `flake.lock` are removed from the repository. `devenv.nix` is unchanged. `.envrc` uses `use devenv`.

---

## Reasoning

The flake approach was blocked not by a flaw in the flake itself — `nix develop --impure` proved the flake evaluates correctly — but by version mismatches in the host tooling integration layer that sit outside the project's control. Resolving them requires both CypherOS maintenance work and a deeper understanding of the flake input graph than is currently available.

`devenv.yaml` sidesteps the entire integration layer. devenv manages the internal flake on the project's behalf, `use devenv` is a simpler and more stable direnv hook, and the pattern is already proven via nixamp on the same machine. The functional outcome — _Caddy, PHP-FPM, MariaDB, Adminer, `dl-*` scripts, devenv shell — is identical._ Nothing about `devenv.nix` changes.

The cost is loss of explicit input ownership. `devenv.yaml` pins inputs via `devenv.lock` (_devenv's own lockfile_), which devenv manages. This is less transparent than a `flake.lock` the project owns directly, but it is sufficient for a single-developer project on a known machine.

The flake approach is not abandoned permanently. It is deferred until the skill to manage the integration chain confidently is in place and there is a concrete motivation — _such as needing to reproduce the environment on a machine without nixamp as a prior reference._

---

## Alternatives Considered

### Keep flake.nix and fix the CypherOS nix-direnv version lag

The CypherOS fix is `nix flake update && sudo nixos-rebuild switch`. This is the correct long-term fix regardless and should still be done. However, each `nix flake update` on CypherOS introduced new evaluation errors rather than cleanly resolving the prior ones, suggesting the version constraint surface is wider than a single update resolves. Continuing to chase these errors during the DevLog bootstrap session would have consumed time with no progress on the actual project.

Rejected for now; remains the right fix for CypherOS independently of this decision.

### Docker + Docker Compose

Evaluated and rejected in ADR_001. That reasoning stands unchanged — _Docker introduces a second dependency management system operating in parallel with Nix, with no benefit on a NixOS machine where devenv already solves the same problem._ This option remains the fallback if devenv itself proves unworkable, but devenv.yaml resolves the immediate issue without requiring Docker.

### devenv shell (_manual, without direnv_)

`nix develop --impure` or `devenv shell` entered manually works correctly. This was used to confirm the environment is functional during debugging. Rejected as the primary workflow because it requires manual invocation on every terminal session — the `cd`-and-it-activates behaviour that direnv provides is worth having.

---

## Consequences

**Positive:**

- Unblocks development immediately — _same pattern as nixamp, proven on this machine_
- Eliminates the host tooling version mismatch surface: `use devenv` does not go through `nix-direnv`'s flake evaluation path
- `devenv.nix` is unchanged — _all service definitions, scripts, and shell configuration carry over exactly_
- `devenv.lock` still pins inputs; reproducibility within devenv's own locking mechanism is maintained

**Negative / Trade-offs:**

- Input pins are managed by devenv internally rather than owned explicitly by the project; less transparent than `flake.lock`
- `devenv.nix` is no longer trivially embeddable into a parent flake — _it would need the `devenv.yaml` → `flake.nix` migration redone when the time comes_
- The LAMP template extraction planned in ROADMAP becomes slightly more work when revisited, since there is no `flake.nix` to start from

**Neutral / Operational:**

- `devenv.lock` should be committed alongside `devenv.yaml` — _it is the equivalent of `flake.lock` within devenv's own locking model_
- The flake approach remains the long-term target; the planned revisit trigger is: working across machines with divergent system states, or a second developer needing to reproduce the environment
- ADR_001's decision to use devenv over Docker is unaffected; only the flake vs devenv.yaml choice is reversed here
