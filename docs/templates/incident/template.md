# INC-YYYY-MM-DD-NNN: [Short Title]

<!--
An incident is any event that broke something meaningful, revealed a wrong assumption, cost significant time due to a flaw in the system, or resulted in a decision that changed the project's direction.

Incidents are first-class citizens. They get their own document, their own ID, and they are LINKED FROM every other document they affected — changelog entries, ADRs, journal entries.

This is what makes the project's history navigable.

Naming convention: INC_YYYY_MM_DD_NNN_short_descriptive_title.md
Location:          docs/development/incidents/
-->

**ID:** INC_YYYY_MM_DD_NNN
**Date:** YYYY-MM-DD
**Severity:** [Low · Medium · High · Critical]
**Status:** [Open · Resolved · Monitoring]
**Reported by:** CypherWhisperer

---

## Summary

<!-- REQUIRED: Two to three sentences. What happened? What was the impact? What was the resolution? -->

_TODO_

---

## Timeline

<!-- REQUIRED: Chronological sequence of events. Even approximate times help. Add rows as needed. -->

| Time             | Event                                  |
| ---------------- | -------------------------------------- |
| YYYY-MM-DD HH:MM | _TODO: First sign something was wrong_ |
| YYYY-MM-DD HH:MM | _TODO: What was investigated_          |
| YYYY-MM-DD HH:MM | _TODO: Root cause identified_          |
| YYYY-MM-DD HH:MM | _TODO: Resolution applied_             |

---

## Impact

<!-- REQUIRED: What broke, how badly, for how long. -->

- **Components affected:** _TODO_
- **Data affected:** _TODO (or "None")_
- **Time lost:** _TODO (approximate)_
- **Work affected:** _TODO (e.g. "Milestone 2 blocked", "devenv unusable")_

---

## Root Cause

<!-- REQUIRED: The actual underlying reason this happened. Not the symptom — the cause. "It crashed" is a symptom. "The socket path was hardcoded to a location devenv no longer uses" is a cause. Be specific. -->

_TODO_

---

## Resolution

<!-- REQUIRED: What was done to fix it. Be concrete. -->

_TODO_

### Changes Made

<!-- Link every artifact that changed because of this incident.
Remove rows that don't apply. -->

| Type    | Reference                                           | Description                       |
| ------- | --------------------------------------------------- | --------------------------------- |
| Commit  | `abc1234`                                           | _TODO: what the commit fixed_     |
| ADR     | [ADR_NNN](../../project/decisions/ADR_NNN_title.md) | _TODO: decision made in response_ |
| Runbook | [runbook](../../runbooks/runbook-name.md)           | _TODO: if a runbook was created_  |

---

## Contributing Factors

<!-- OPTIONAL but valuable: What conditions made this incident possible?
These are systemic factors, not blame. Think: missing conventions, silent failure modes, misleading error messages, untested assumptions. -->

- _TODO_

---

## Prevention

<!-- REQUIRED: What changes to process, code, or documentation prevent
recurrence? Be specific — "be more careful" is not a prevention measure. -->

- _TODO_

---

## Lessons Learned

<!-- REQUIRED: What does this incident teach beyond fixing the immediate
problem? Be honest. The value compounds over time. -->

_TODO_

---

<!-- METADATA
Opened:  YYYY-MM-DD
Resolved: YYYY-MM-DD
Related journal entry: [YYYY_MM_DD_title](../journal/YYYY_MM_DD_title.md)
-->
