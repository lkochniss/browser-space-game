#!/usr/bin/env python3
"""Aggregates ticket status per Epic. Reads .claude/tickets/README.md."""
from __future__ import annotations
from collections import defaultdict
from pathlib import Path

README = Path(__file__).resolve().parents[2] / ".claude" / "tickets" / "README.md"

counts: dict[str, dict[str, int]] = defaultdict(lambda: defaultdict(int))
for line in README.read_text(encoding="utf-8").splitlines():
    if not line.startswith("|") or "---" in line or "File" in line:
        continue
    cells = [c.strip() for c in line.strip("|").split("|")]
    if len(cells) < 6 or not cells[0].endswith(".md"):
        continue
    epic = cells[2]
    status = cells[4]
    counts[epic][status] += 1

statuses = ["Ready", "Draft", "Done", "Blocked", "Open", "Superseded"]
header = f"{'Epic':<32} " + " ".join(f"{s:<9}" for s in statuses) + " total"
print(header)
print("-" * len(header))
for epic in sorted(counts):
    row = counts[epic]
    total = sum(row.values())
    cells = " ".join(f"{row.get(s, 0):<9}" for s in statuses)
    print(f"{epic:<32} {cells} {total}")
