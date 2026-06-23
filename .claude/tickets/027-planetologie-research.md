# T-027: Planetologie-Forschung

**Type:** Feature
**Epic:** Research & Tech-Tree
**Domain:** Research
**Blocked By:** T-013, T-025
**Status:** Open
**FX:** No
**MIG:** No
**Depends on:** T-013, T-025

## Description

`docs/Planetologie.md`: Forschung verbessert Sondendetail. Ab Stufe X Rohstoff-Vorkommen schätzbar. Hohe Stufe schaltet Terraforming frei.

## AC

- [ ] ResearchNode `PLANETOLOGY` (mehrere Levels)
- [ ] Sonden-Scan-Result variiert nach Player-Planetologie-Level (z.B. Lvl 0: Type+Size, Lvl 2: Deposits geschätzt, Lvl 5: exakt)
- [ ] Terraforming als separater Lock (oder eigenes Ticket) ab Lvl X
- [ ] T-013 Probe-Result respektiert Player-Research-Level

## Affected

- `src/Research/Service/ResearchTree.php`
- `src/Probe/...` Scan-Result-Logik

## Open Questions

1. Levels-Skala 0–5 oder 0–10?
2. Terraforming als eigenes Feature-Ticket jetzt notieren oder später?
3. Scan-Result-Granularität pro Level: Vorschlag genügt — bestätigen?
