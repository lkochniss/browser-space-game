#!/usr/bin/env python3
"""
Rebuilds .claude/tickets/README.md with new column schema:
  | File | Type | Epic | Domain | Status | Blocked By | Summary |

Reads each ticket file, extracts metadata, preserves existing Summary from
current README (or fallback to first non-empty line under Description).
"""
from __future__ import annotations

import re
import sys
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
TICKETS = REPO / ".claude" / "tickets"
README = TICKETS / "README.md"


def field(content: str, name: str) -> str:
    m = re.search(rf"^\*\*{re.escape(name)}:\*\*\s*(.+?)$", content, re.MULTILINE)
    return m.group(1).strip() if m else ""


def extract_id(filename: str) -> str:
    m = re.match(r"^(\d{3}[a-z]?)-", filename)
    return m.group(1) if m else ""


def read_existing_summaries() -> dict[str, str]:
    """Returns map filename → existing Summary from current README."""
    if not README.exists():
        return {}
    out: dict[str, str] = {}
    for line in README.read_text(encoding="utf-8").splitlines():
        # Match row like: | 001-foo.md | Feature | ... | summary |
        if not line.startswith("|") or "---" in line:
            continue
        cells = [c.strip() for c in line.strip("|").split("|")]
        if not cells or not cells[0].endswith(".md"):
            continue
        # last cell is summary
        out[cells[0]] = cells[-1]
    return out


def build_row(path: Path, fallback_summary: str) -> str:
    content = path.read_text(encoding="utf-8")
    ttype = field(content, "Type")
    epic = field(content, "Epic") or "None"
    domain = field(content, "Domain") or "—"
    status = field(content, "Status")
    blocked = field(content, "Blocked By") or "None"

    # Strip noise from status (e.g. "Done (Foundation; XYZ split)")
    status_short = re.sub(r"\s*\(.+\)\s*$", "", status).strip() or status

    # Type: keep only main label
    ttype_short = re.sub(r"\s*\(.+\)\s*$", "", ttype).strip() or ttype

    return f"| {path.name} | {ttype_short} | {epic} | {domain} | {status_short} | {blocked} | {fallback_summary} |"


def main() -> int:
    summaries = read_existing_summaries()
    rows: list[str] = []
    for f in sorted(TICKETS.glob("*.md")):
        if f.name == "README.md":
            continue
        summary = summaries.get(f.name, "—")
        rows.append(build_row(f, summary))

    header = (
        "# Tickets\n\n"
        "| File | Type | Epic | Domain | Status | Blocked By | Summary |\n"
        "|------|------|------|--------|--------|------------|---------|"
    )
    README.write_text(header + "\n" + "\n".join(rows) + "\n", encoding="utf-8")
    print(f"README rebuilt with {len(rows)} ticket rows.")
    return 0


if __name__ == "__main__":
    sys.exit(main())
