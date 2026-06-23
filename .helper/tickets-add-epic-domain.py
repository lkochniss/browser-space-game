#!/usr/bin/env python3
"""
T-Refine: Adds Epic, Domain, Blocked By fields to all ticket files.

Idempotent: skips tickets that already have Epic field. Operates only on
.claude/tickets/, never touches src/.

Mapping is hard-coded below. Epic & Domain per ticket-ID prefix.
Blocked-By derived from existing 'Depends on:' line.
"""
from __future__ import annotations

import re
import sys
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
TICKETS = REPO / ".claude" / "tickets"

# ---------------------------------------------------------------------------
# Domain + Epic mapping per ticket-ID (or prefix).
# Order matters: exact > prefix > range.
# ---------------------------------------------------------------------------

# Exact ID → (Domain, Epic)
EXACT: dict[str, tuple[str, str]] = {
    "001": ("Resource", "Foundation: Resources"),
    "002": ("Resource", "Foundation: Resources"),
    "003": ("Resource", "Foundation: Resources"),
    "004": ("Planet", "Foundation: Population"),
    "005": ("Planet", "Foundation: Population"),
    "006": ("Building", "Foundation: Buildings"),
    "007": ("SolarSystem", "Foundation: Galaxy"),
    "008": ("Planet", "Foundation: Planet Types"),
    "009": ("Building", "Foundation: Buildings"),
    "010": ("Building", "Foundation: Buildings"),
    "011": ("Building", "Ships & Fleet"),
    "012": ("Ship", "Ships & Fleet"),
    "013": ("Probe", "Exploration & Probes"),
    "014": ("Ship", "Ships & Fleet"),
    "015": ("Ship", "Ships & Fleet"),
    "015b": ("Ship", "Ships & Fleet"),
    "015c": ("Ship", "Ships & Fleet"),
    "016": ("Ship", "Ships & Fleet"),
    "017": ("Fleet", "Ships & Fleet"),
    "017b": ("Fleet", "Ships & Fleet"),
    "018": ("Probe", "Exploration & Probes"),
    "019": ("POI", "POI System"),
    "020": ("POI", "POI System"),
    "021": ("POI", "POI System"),
    "022": ("POI", "POI System"),
    "023": ("POI", "POI System"),
    "023b": ("POI", "POI System"),
    "024": ("Ship", "Combat & Battle"),  # superseded
    "025": ("Research", "Research & Tech-Tree"),
    "025b": ("Research", "Research & Tech-Tree"),
    "025c": ("Research", "Research & Tech-Tree"),
    "026": ("Research", "Research & Tech-Tree"),
    "026b": ("Research", "Research & Tech-Tree"),
    "026c": ("Ship", "Ships & Fleet"),
    "027": ("Research", "Research & Tech-Tree"),
    "028": ("Planet", "Tech-Debt & Cleanup"),
    "029": ("Building", "Tech-Debt & Cleanup"),
    "030": ("Resource", "Tech-Debt & Cleanup"),
    "031": ("Common", "Tech-Debt & Cleanup"),
    "032": ("Common", "Tech-Debt & Cleanup"),
    "033": ("Planet", "Tech-Debt & Cleanup"),
    "034": ("User", "Web Layer & Auth"),
    "035": ("User", "Web Layer & Auth"),
    "036": ("User", "Web Layer & Auth"),
    "037": ("User", "Web Layer & Auth"),
    "038": ("User", "Web Layer & Auth"),
    "039": ("User", "Web Layer & Auth"),
    "040": ("User", "Web Layer & Auth"),
    "041": ("User", "Web Layer & Auth"),
    "042": ("User", "Web Layer & Auth"),
    "043": ("User", "Web Layer & Auth"),
    "044": ("Tick", "Web Layer & Auth"),
    "045": ("UI", "Game UI"),
    "046": ("User", "Web Layer & Auth"),
    "047": ("UI", "Game UI"),
    "048": ("Common", "Web Layer & Auth"),
    "049": ("Common", "Web Layer & Auth"),
    "049a": ("SolarSystem", "Foundation: Galaxy"),
    "050": ("UI", "Web Layer & Auth"),
    "051": ("Common", "Web Layer & Auth"),
    "052": ("User", "Multiplayer"),
    "053": ("User", "Multiplayer"),
    "054": ("User", "Multiplayer"),
    "055": ("User", "Web Layer & Auth"),
    "056": ("Common", "Web Layer & Auth"),
    "057": ("Common", "Web Layer & Auth"),
    "058": ("Common", "Tech-Debt & Cleanup"),
    "059": ("Planet", "Tech-Debt & Cleanup"),
    "060": ("Tick", "Tech-Debt & Cleanup"),
    "061": ("Planet", "Storage Vision"),
    "062": ("Building", "Foundation: Buildings"),
    "063": ("Planet", "Foundation: Planet Types"),
    "064": ("Research", "Research & Tech-Tree"),
    "064b": ("Building", "Building System"),
    "065": ("Building", "Energy System"),
    "066": ("Resource", "Storage Vision"),
    "067": ("Resource", "Resources Tier-2/3"),
    "068": ("Building", "Combat & Defense"),
    "069": ("Research", "Research & Tech-Tree"),
    "070": ("Building", "Pop QoL"),
    "070b": ("Building", "Pop QoL"),
    "071": ("Building", "Energy System"),
    "072": ("Building", "Resources Tier-2/3"),  # superseded
    "073": ("Faction", "NPC Factions"),
    "074": ("Faction", "NPC Factions"),
    "075": ("POI", "NPC Factions"),
    "076": ("SolarSystem", "Galaxy Events"),
    "077": ("Faction", "NPC Factions"),
    "078": ("Faction", "NPC Factions"),
    "079": ("Faction", "NPC Factions"),
    "080": ("Faction", "NPC Factions"),
    "081": ("Planet", "Game Balance"),
    "081b": ("Planet", "Game Balance"),
    "082": ("Demo", "Demo CLI"),
    "082b": ("Demo", "Demo CLI"),
    "082c": ("Demo", "Demo CLI"),
    "082d": ("Demo", "Demo CLI"),
    "082e": ("Demo", "Demo CLI"),
    "082f": ("Demo", "Demo CLI"),
    "084": ("Faction", "Endgame"),
    "085": ("POI", "POI System"),
    "086": ("POI", "POI System"),
    "087": ("Probe", "Exploration & Probes"),
    "088": ("Resource", "Combat & Battle"),
    "089": ("Resource", "Resources Tier-2/3"),
    "090": ("Resource", "Resources Tier-2/3"),
    "091": ("Resource", "Resources Tier-2/3"),
    "092": ("Resource", "Resources Tier-2/3"),
    "093": ("POI", "Multiplayer"),
    "094": ("Building", "Building System"),
    "094b": ("Building", "Building System"),
    "094c": ("Building", "Building System"),
    "094d": ("Building", "Building System"),
    "095": ("Trade", "Trade & Economy"),
    "096": ("Player", "Player Progression"),
    "096b": ("Player", "Player Progression"),
    "097": ("Building", "Pop QoL"),
    "097a": ("Building", "Pop QoL"),
    "098": ("Player", "Player Progression"),
    "099": ("Faction", "NPC Factions"),
    "100": ("Building", "Trade & Economy"),
    "101": ("Planet", "Game Balance"),
    "101b": ("Planet", "Game Balance"),
    "102": ("Ship", "Combat & Battle"),
    "103": ("Ship", "Combat & Battle"),
    "104a": ("Ship", "Combat & Battle"),
    "104b": ("Ship", "Combat & Battle"),
    "104c": ("Ship", "Combat & Battle"),
    "105": ("Ship", "Ships & Fleet"),
    "106": ("Building", "Resources Tier-2/3"),
    "107": ("Building", "Resources Tier-2/3"),
    "108": ("Building", "Resources Tier-2/3"),
    "109": ("Building", "Storage Vision"),
    "110": ("Trade", "Trade & Economy"),
    "111": ("Trade", "Trade & Economy"),
    "112": ("Trade", "Trade & Economy"),
    "113": ("Trade", "Trade & Economy"),
    "114": ("Ship", "Trade & Economy"),
    "115": ("Resource", "Resources Tier-2/3"),
    "116": ("Mega", "Mega Structures"),
    "117": ("Research", "Multiplayer"),
    "118": ("Trade", "Trade & Economy"),
    "119": ("Mega", "Mega Structures"),
    "120": ("Quest", "Quests & Engagement"),
    "121": ("Crusade", "Endgame"),
    "122": ("Player", "Player Progression"),
    "122b": ("Player", "Player Progression"),
    "123": ("Player", "Player Progression"),
    "124": ("Mega", "Mega Structures"),
    "125": ("Mega", "Mega Structures"),
    "126": ("Player", "Player Progression"),
    "127": ("Research", "Research & Tech-Tree"),
    "128": ("Research", "Research & Tech-Tree"),
    "129": ("Research", "Research & Tech-Tree"),
    "130": ("User", "Multiplayer"),
    "131": ("Faction", "NPC Factions"),
    "132": ("Research", "Multiplayer"),
    "133": ("User", "Multiplayer"),
    "134": ("Research", "Research & Tech-Tree"),
    "135": ("Research", "Research & Tech-Tree"),
    "136": ("Research", "Research & Tech-Tree"),
    "137": ("Research", "Research & Tech-Tree"),
    "138": ("Research", "Research & Tech-Tree"),
    "139": ("Research", "Research & Tech-Tree"),
    "140": ("Quest", "Quests & Engagement"),
    "141": ("Player", "Quests & Engagement"),
    "142": ("Player", "Quests & Engagement"),
    "143": ("Player", "Player Progression"),
    "150": ("Player", "Game Balance"),
    "150b": ("Player", "Game Balance"),
    "151": ("Common", "Game Balance"),
    "152": ("Player", "Game Balance"),
    "153": ("User", "Multiplayer"),
    "160": ("UI", "Game UI"),
    "161": ("UI", "Game UI"),
    "162": ("Building", "Game UI"),
    "163": ("UI", "Game UI"),
    "164": ("UI", "Game UI"),
    "165": ("UI", "Game UI"),
    "166": ("UI", "Game UI"),
    "167": ("Common", "Tech-Debt & Cleanup"),
    "168": ("Demo", "Tech-Debt & Cleanup"),
    "169": ("Demo", "Tech-Debt & Cleanup"),
    "170": ("Research", "Research & Tech-Tree"),
    "171": ("Building", "Building System"),
    "172": ("Building", "Building System"),
    "173": ("Building", "Building System"),
    "174": ("POI", "Tech-Debt & Cleanup"),
    "175": ("POI", "NPC Factions"),
    "176": ("POI", "NPC Factions"),
    "177": ("Planet", "Storage Vision"),
    "178": ("Ship", "Storage Vision"),
    "179": ("Planet", "Storage Vision"),
    "180": ("Resource", "Storage Vision"),
    "181": ("Resource", "Storage Vision"),
    "182": ("Building", "Tech-Debt & Cleanup"),
    "183": ("POI", "Storage Vision"),
}


def extract_id(filename: str) -> str:
    """Returns ID without leading zeros stripped (e.g. '015b', '082f', '094')."""
    m = re.match(r"^(\d{3}[a-z]?)-", filename)
    return m.group(1) if m else ""


def extract_blocked_by(content: str) -> list[str]:
    """Extracts ticket IDs from Depends on: line (e.g. 'T-180, T-177' → ['180', '177'])."""
    m = re.search(r"\*\*Depends on:\*\*\s*(.+)$", content, re.MULTILINE)
    if not m:
        return []
    line = m.group(1)
    # Extract T-NNN[letter] tokens
    ids = re.findall(r"T-(\d{3}[a-z]?)", line)
    return ids


def has_epic_field(content: str) -> bool:
    return bool(re.search(r"^\*\*Epic:\*\*", content, re.MULTILINE))


def insert_fields(content: str, epic: str, domain: str, blocked_by: list[str]) -> str:
    """Inserts Epic, Domain, Blocked By after the **Type:** line."""
    if has_epic_field(content):
        return content  # idempotent

    bb = "None" if not blocked_by else ", ".join(f"T-{i}" for i in blocked_by)

    # Find **Type:** line; insert new fields right after it
    new_block = (
        f"**Epic:** {epic}\n"
        f"**Domain:** {domain}\n"
        f"**Blocked By:** {bb}\n"
    )

    def replace_after_type(match: re.Match) -> str:
        return match.group(0) + new_block

    new_content = re.sub(
        r"^(\*\*Type:\*\*[^\n]*\n)",
        lambda m: m.group(1) + new_block,
        content,
        count=1,
        flags=re.MULTILINE,
    )
    if new_content == content:
        # No Type line — fallback: insert at top after title
        new_content = re.sub(
            r"^(#[^\n]*\n\n?)",
            lambda m: m.group(1) + new_block + "\n",
            content,
            count=1,
        )
    return new_content


def process_ticket(path: Path) -> str:
    tid = extract_id(path.name)
    if not tid:
        return f"SKIP no-id {path.name}"
    if tid not in EXACT:
        return f"WARN no-mapping {tid}"

    content = path.read_text(encoding="utf-8")
    if has_epic_field(content):
        return f"SKIP already-tagged {tid}"

    domain, epic = EXACT[tid]
    blocked_by = extract_blocked_by(content)
    new_content = insert_fields(content, epic, domain, blocked_by)
    path.write_text(new_content, encoding="utf-8")
    return f"OK {tid} epic={epic!r} domain={domain} blocked_by={blocked_by}"


def main() -> int:
    files = sorted(TICKETS.glob("*.md"))
    skipped = 0
    updated = 0
    warned = 0
    for f in files:
        if f.name == "README.md":
            continue
        result = process_ticket(f)
        print(result)
        if result.startswith("OK"):
            updated += 1
        elif result.startswith("SKIP"):
            skipped += 1
        elif result.startswith("WARN"):
            warned += 1
    print(f"\n--- updated={updated} skipped={skipped} warned={warned} ---")
    return 0 if warned == 0 else 1


if __name__ == "__main__":
    sys.exit(main())
