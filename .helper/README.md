# Helper Scripts

Read-only / metadata helpers for `.claude/` and git. **MUST NOT touch `src/`**.

| Script | Purpose |
|--------|---------|
| `tickets-add-epic-domain.py` | One-shot migration: adds `Epic`, `Domain`, `Blocked By` fields to every ticket file. Idempotent — skips tickets that already have an `Epic` field. Mapping is hard-coded inside the script (ID → Domain + Epic). |
| `tickets-readme-rebuild.py` | Rebuilds `.claude/tickets/README.md` index with new column schema (`File / Type / Epic / Domain / Status / Blocked By / Summary`). Preserves existing Summary text from current README; pulls fresh metadata from each ticket file's header. |

Run via `python3 .helper/<script>.py` from repo root.
