# T-069 Forschungs-Lab Tier-Mechanik

**Type:** Feature
**Status:** Done (Foundation; Power-Hook bleibt T-065-Stub-NoOp)
**Effort:** M
**Depends on:** T-025 (Done), T-025c (Multi-Lab Effective-Lab, Done)
**Blocks:** T-117 (Allianz-Forschung)

## Beschreibung

T-025 enthält **Minimal-RESEARCH_LAB** (BuildingType, Cost, einfache Speed-
Multiplier-Curve). T-069 erweitert um:

1. **Tier-Gates:** Tech-Nodes mit `requiredLabLevel` — höhere Tier-Tech (z.B.
   FTL-Tier-3 oder Tier-3-Mining) braucht Lab L3+ oder L5+.
2. **Speed-Curve-Tuning:** finale Lab-Speed-Multiplier-Tabelle nach Demo-
   Erfahrung (T-025 startet mit Stub: L1=1.0, L2=0.85 etc.)
3. **Pop-Cost-Skalierung:** Lab-Höher-Tier braucht mehr Wissenschaftler-Pop.
4. **Power-Consumption** (sobald T-065 done): Lab L3+ braucht Reaktor.

## Acceptance Criteria

- [x] `ResearchNode::requiredLabLevel: int = 1` field
- [x] `StartResearchCommandService` validiert `effectiveLab >= requiredLabLevel`
      → `LabLevelTooLowException`
- [x] Tier-Mapping in ResearchTree:
      - Tier-1 (Foundation): basic_mining, metallurgy, astronomy, shipbuilding,
        advanced_mining, recycling, propulsion_hydrogen, construction_speed_1,
        logistics_1 → L1
      - Tier-2 (Mid-Game): propulsion_ion, propulsion_fusion, ftl_hyperdrive → L2
      - Tier-3 (Endgame): propulsion_antimatter, ftl_warp, ftl_jumpdrive → L3
- [x] Lab-Speed-Curve `pow(1.18, effectiveLab-1)` bereits finalisiert in
      `ResearchDurationConfig` (T-025) — keine Änderung nötig
- [x] Lab-Pop-Skalierung läuft bereits via `BuildingCostConfig::getCost(level)`
      mit `2^level × softCap` Multi — keine Änderung nötig
- [x] T-065-Hook (Power) — Stub-NoOp doc-Eintrag (T-065 Draft)
- [x] Tests: 4 Tier-Gate-IT-Tests + 1 ResearchTree-Tier-Mapping-Unit-Test
- [x] Doc `research.md` Tier-Gating-Sektion

## Out of Scope

- **Multi-Lab-Stacking** ist T-025c (Done) — Effective-Lab via Primary +
  Booster-Decay genutzt
- **RP-Pool-Pattern**: NICHT angedacht (T-025 ist Wallclock-basiert)
- **Power-Consumption-Wiring** (T-065 Draft) — Hook-Punkt benannt, Implementation
  bleibt in T-065
- **Specialist-Track Branch-Boost** (T-098) — eigener Hook in T-098

## Notes

- Foundation in T-025 + T-025c reicht — T-069 macht das Gating production-grade
- Booster-Lifting: Player ohne dedicated L3-Lab kann via 2×L2 (effective 3.0)
  trotzdem Tier-3 erforschen — strategischer Tradeoff via T-025c Cost
