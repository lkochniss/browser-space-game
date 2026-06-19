# T-026: Antriebstechnologie-Tree

**Type:** Feature
**Status:** Done (Foundation: Nodes + Inter-System-Travel-Lock; PropulsionType+Speed/Fuel via Folge)
**FX:** No
**MIG:** No
**Depends on:** T-025

## Description

`docs/Antriebstechnologie.md`: 4 Standardantriebe (Wasserstoff/Ionen/Fusion/Antimaterie) + 3 FTL (Hyperraum/Warp/Sprung). Progression durch Forschung. Sondengeschwindigkeit (T-013) durch Wasserstoff verbessert.

## Decisions (2026-06-19)

1. **Scope:** Foundation only — 7 Nodes + Inter-System-Travel-Lock. PropulsionType-Enum
   + Speed/Fuel-Mechanik in Folge-Tickets.
2. **Inter-System-Lock:** ftl_hyperdrive L1 reicht für Foundation. Wormhole-spezifische
   Tech-Lock (Wormhole.requiredTechSlug='ftl_warp') ist data-only — Validation kommt
   mit Folge-Ticket.

## AC (Foundation)

- [x] 7 ResearchNodes: propulsion_hydrogen → ion → fusion → antimatter,
      ftl_hyperdrive → ftl_warp → ftl_jumpdrive (mit SHIPYARD-L1 Prereq + Cascade)
- [x] Wormhole.requiredTechSlug: 'ftl_tier_2' → 'ftl_warp' (4 Stellen)
- [x] `MoveFleetCommandService` validiert Inter-System: braucht ftl_hyperdrive L1+,
      sonst `InterSystemTravelLockedException`
- [x] Tests: existing inter-system-Test seedet FTL; neuer block-Test
- [x] Suite grün (498/498)

## Out of Scope (Folge-Tickets)

- **T-026b PropulsionType-Enum** + Ship.propulsion-Field, Speed-Modifier
- **T-026c Fuel-Verbrauch** (Folge zu T-066 Treibstoff-Draft)
- **Wormhole-Travel-Routing**: ftl_warp + Wormhole-Pair als Fast-Travel
- **Sondengeschwindigkeits-Boost** durch propulsion_hydrogen (T-013-Hook)
