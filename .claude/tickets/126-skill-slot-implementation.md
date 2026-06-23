# T-126 Skill-Slot-Implementation (Folge zu T-123)

**Type:** Feature
**Epic:** Player Progression
**Domain:** Player
**Blocked By:** T-123
**Status:** Draft
**Effort:** L (TBD)
**Depends on:** T-123 (Player-XP / Karriere)
**Blocks:** —

## Beschreibung

T-123 reserviert pro Level-Up einen Skill-Slot, aber die eigentliche Mechanik (welche Skills wählbar, welche Effekte) ist offen. Dieses Ticket finalisiert das.

Ansatz-Vorschläge:
- **Permanente Mini-Boni**: pro Skill +1-2% auf eine Stat (z.B. +1% Mining-Output, +2% Shield-Capacity, +1 Build-Queue-Slot)
- **Skill-Tree mit Branches**: Combat / Economy / Diplomacy / Exploration — Spieler verteilt 100 Skill-Punkte (Cap durch Level 100)
- **Limited Re-Spec**: 1× pro Quartal mit Cost?

## Acceptance Criteria

- [ ] TBD: Skill-Catalog (~30 Skills initial?)
- [ ] TBD: Effekt-Resolver-Integration in Production/Battle/UI
- [ ] TBD: Re-Spec-Mechanik decision
- [ ] TBD: UI Skill-Tree-View

## Open Questions

- Konflikt mit T-098 Specialist-Tracks (PERMANENT)? Skills additiv oder konkurrierend?
- Power-Creep-Risiko: bei 100 Levels × 1% Boni = +100% irgendwo. Cap-Mechanik nötig?
- Skill-Tree-Diversität: forcieren oder freie Wahl?

## Notes

- "Long-Time-Spieler"-Decision (Cluster D) → Skill-Slots sollen Long-Term-Engagement honorieren
- Konsistent zu Anti-Steamroller: keine Skills die andere Spieler direkt beeinträchtigen
