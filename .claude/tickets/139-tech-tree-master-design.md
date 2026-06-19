# T-139 Tech-Tree-Master-Design (Meta-Ticket)

**Type:** Feature
**Status:** Draft
**Effort:** S (TBD)
**Depends on:** T-025, T-026, T-027, T-127, T-128, T-129, T-134, T-135, T-136, T-137, T-138
**Blocks:** —

## Beschreibung

Meta-Koordinations-Ticket für den **kompletten Tech-Tree** über alle 10 Branches:

| # | Branch | Ticket | Specialist-Track-Lock (Tier 4-5) |
|---|--------|--------|-----------------------------------|
| 1 | Antrieb | T-026 | EXPLORATION |
| 2 | Planetologie | T-027 | (Cross) |
| 3 | Mining/Industrie | T-127 | INDUSTRY |
| 4 | Schiffbau | T-128 | MILITARY |
| 5 | Energie | T-129 | (Cross, Industry-affinity) |
| 6 | Kybernetik | T-134 | RESEARCH |
| 7 | Diplomatie | T-135 | DIPLOMACY |
| 8 | Logistik | T-136 | (Cross) |
| 9 | Defense | T-137 | (Cross, Military-affinity) |
| 10 | Xenobiologie | T-138 | EXPLORATION (sekundär) |

Cross-Track-Branches (Planetologie, Energie, Logistik, Defense, Xenobiology) sind für alle Specialist-Tracks Tier 1-3 voll zugänglich, Tier 4-5 nur affiliated Specialist.

## Acceptance Criteria

- [ ] TBD: Tech-Tree-Konstanten-Service (alle Nodes-Map zentral)
- [ ] TBD: Branch-Lock-Mechanik konsistent in allen 10 Branch-Services
- [ ] TBD: UI: Tech-Tree-Browser zeigt alle 10 Branches mit eigenen Track-Locks
- [ ] TBD: Forschung-Cost-Skalierung pro Tier konsistent
- [ ] TBD: Total RP-Investment für komplette Specialist-Track-Branch (Tier 1-5) = approx. 6-12 Wochen Solo-Forschung

## Open Questions

- Total Node-Anzahl: ~100 Nodes über 10 Branches (10/Branch)? Mehr Tiefe (15-20 pro Branch)?
- Cross-Branch-Voraussetzungen (z.B. AI-Core-Recipe braucht T-134 Tier 4 + T-127 Tier 4)?
- Forschungs-Reset-Mechanik: kann ein Tech "vergessen" werden? (Decision: nein, Forschung ist persistent)

## Notes

- Master-Design dokumentiert die globale Forschungs-Welt
- Implementation-Reihenfolge: T-025 Foundation → einzelne Branches schrittweise
- Tier 5 in jeder Branch ist Endgame-Anker für Long-Time-Spieler
