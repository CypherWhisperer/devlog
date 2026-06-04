# ADR_001_2026_06_04: Nix Flake + devenv as the Development Environment

**Date:** 2026-06-04
**Status:** Accepted
**Deciders:** CypherWhisperer

---

## Context

DevLog is a PHP/LAMP application developed on NixOS. The development environment needs to provide Caddy, PHP-FPM, MariaDB, and Adminer as local services with no system-level installation. The university course context means the environment must be reproducible (the same PHP version, extensions, and service config across all working sessions) and low-friction to enter (a single `cd` via direnv).

An existing nixamp instance (a generic LAMP devenv) already solves this problem for course exercises. The question was whether DevLog should live inside nixamp, inherit from a shared template, or have its own independent environment.

A secondary concern: the environment should be structured in a way that eventually supports extraction into a reusable Nix flake template, since DevLog is the first of what will likely be multiple LAMP projects.

---

## Decision

DevLog gets its own `flake.nix` + `devenv.nix`. It does not share the nixamp repository. The flake owns input pins; `devenv.nix` defines services. `.envrc` uses `use flake`.

---

## Reasoning

Sharing nixamp would conflate two concerns: throwaway class exercises and a structured application project with its own dependencies, database, git history, and documentation. A single `devenv.nix` serving both would require conditional logic and produce a mixed commit history.

Extracting a shared template immediately (before having multiple instances to learn from) would require designing the template's parameter surface from a single data point. Templates designed from one instance tend to over-fit to that instance and require breaking changes when the second project arrives.

A self-contained `flake.nix` for DevLog gives clean project boundaries now and is already in the correct shape for future template extraction — the inputs are explicit, the service config is in a separate `devenv.nix` module, and the flake output schema is standard.

`use flake` over `use devenv` is consistent with NixOS/CypherOS conventions and makes the input pinning explicit and auditable.

---

## Alternatives Considered

### Extend nixamp (single repo, shared devenv.nix)

Rejected. Conflates class exercise work with application development. Mixed git history. Document root, database name, and service scripts would all require conditional logic to serve two purposes. The `scratch/` gitignore pattern partially mitigates the concern but does not resolve the architectural problem.

### Shared Nix flake template (DevLog and nixamp as consumers)

Rejected for this stage. Template design requires multiple data points. One LAMP project is insufficient to know which parameters belong in the template interface vs. which are project-specific. Planned as a future milestone in ROADMAP after DevLog is stable and a second LAMP project exists.

### Docker Compose

Rejected. CypherOS uses Nix as the single source of truth for development tooling. Docker introduces a second dependency manager, a separate image pull step, and a layer of abstraction over the host that is inconsistent with the project's environment philosophy. The devenv LAMP stack already solves the same problem with tighter Nix integration.

### devenv.yaml (without explicit flake.nix)

Rejected. `devenv.yaml` wraps an internal flake that devenv manages on the project's behalf. Writing `flake.nix` explicitly gives ownership of input pins, allows the environment to be composed into a future template, and aligns with `use flake` rather than `use devenv` — the pattern used across CypherOS tooling.

---

## Consequences

**Positive:**
- Clean project boundaries: DevLog's git history, documentation, and environment are entirely self-contained
- Input pins are explicit and auditable in `flake.lock`
- `devenv.nix` is a portable module — it can be embedded into a future template flake without modification
- Consistent with `use flake` conventions from CypherOS

**Negative / Trade-offs:**
- Some duplication between nixamp's `devenv.nix` and DevLog's `devenv.nix` until template extraction is done
- Two `flake.lock` files to update when nixpkgs advances

**Neutral / Operational:**
- Template extraction is tracked as a ROADMAP item; DevLog's `flake.nix` structure was designed with extraction in mind
- `devenv.nix` service names use `dl-` prefix to distinguish DevLog scripts from nixamp's `lamp-` prefix
