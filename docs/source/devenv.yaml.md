# devenv.yaml — DevLog Input Pins

> Declares the Nix input sources devenv uses to resolve packages and
> the devenv module system for this project.

**File:** `devenv.yaml`
**Status:** Stable
**Last reviewed:** 2026-06-07

---

## Responsibility

**Does:**

- Pins the nixpkgs channel devenv pulls packages from
- Declares pre-commit-hooks as an available input for future use

**Does not:**

- Configure services or packages — _that is `devenv.nix`_
- Control devenv's own version — _that is pinned in `devenv.lock`_

---

## Block Analysis

---

### Block 1 — `inputs.nixpkgs`

**What is this?** The nixpkgs flake input — _the package set devenv resolves `pkgs.*` references against._

**What does it do?** Points devenv at `nixos-unstable`, which provides rolling package updates. The resolved commit is locked in `devenv.lock`.

**Why is it here?** devenv requires an explicit nixpkgs input. `nixos-unstable`
is used rather than a stable channel to stay close to CypherOS's own
nixpkgs pin, reducing the risk of environment drift between the dev
environment and the host system.

> ⚠️ `nixos-unstable` means nixpkgs updates (`devenv update`) can change package versions. This is what caused the socket path mismatch incident ([INC_2026_06_05_001](../development/incidents/INC_2026_06_05_001_mariadb_socket_path_mismatch.md)) to surface — _a MariaDB version bump changed devenv's runtime layout._ The fix ([ADR_004](../project/decisions/ADR_004_2026_06_07_devenv_runtime_socket_convention.md)) is resilient to future updates.

```yaml
inputs:
  nixpkgs:
    url: github:NixOS/nixpkgs/nixos-unstable
```

---

### Block 2 — `inputs.pre-commit-hooks`

**What is this?** The cachix/pre-commit-hooks.nix flake input.

**What does it do?** Makes pre-commit hook integration available to
devenv. Not currently activated in `devenv.nix` — declared here for
future use.

**Why is it here?** Pre-commit hooks (linting, formatting checks on
`git commit`) are a planned quality gate for DevLog. Declaring the
input now avoids a `devenv update` cycle when hooks are activated.

```yaml
  pre-commit-hooks:
    url: github:cachix/pre-commit-hooks.nix
```

---

## Related

| Type      | Reference                                          |
| --------- | -------------------------------------------------- |
| Companion | [`devenv.nix.md`](./devenv.nix.md)                 |
| Lock file | `devenv.lock` (do not edit manually)               |
| ADR       | [ADR_003](../project/decisions/ADR_003_2026_06_05_revert_to_devenv_yaml.md) |

---

<!-- METADATA
File:    devenv.yaml
Created: 2026-06-05
Updated: 2026-06-07
-->
