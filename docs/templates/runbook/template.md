# Runbook: [Procedure Name]

**Last verified:** YYYY-MM-DD
**Environment:** `devlog` devenv (Caddy + PHP-FPM + MariaDB)
**Trigger:** `[Scheduled | Reactive]`
**Estimated time:** ~N minutes

---

## When To Use This Runbook

_TODO: One or two sentences. What situation triggers this procedure?_

---

## Prerequisites

- devenv shell active (`direnv allow` in project root)
- `devenv up` running (all services healthy — verify with `dl-status`)
- _TODO: any additional prerequisites_

---

## Procedure

### Step 1 — [Action]

```bash
# command here
```

Expected output: _TODO_
If this fails: _TODO_

---

### Step N — Verify

```bash
dl-status
```

Expected result: all services reachable.

---

## Troubleshooting

### [Problem scenario]

_TODO: Symptom and resolution._

---

## Rollback

_TODO or "This procedure is not reversible."_

---

## Related

- ADR: _TODO or remove_
- Runbook: _TODO or remove_
