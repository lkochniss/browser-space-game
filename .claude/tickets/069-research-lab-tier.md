# T-069 Forschungs-Lab Tier-Mechanik

**Type:** Feature
**Status:** Draft (rewritten 2026-06-19 nach T-025-Decisions)
**Effort:** M
**Depends on:** T-025 (Forschungs-Framework, Wallclock-based)
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

- [ ] `ResearchNode::requiredLabLevel: int` field, validated in
      `StartResearchCommandService`
- [ ] Lab-Speed-Curve in `BuildingType::RESEARCH_LAB::getResearchSpeedMultiplier($level)`
      finalisiert
- [ ] Lab-Pop-Skalierung in BuildingCostConfig (höhere Levels brauchen mehr Pop)
- [ ] T-065-Hook (Power) — wenn done; sonst stub-NoOp
- [ ] Tests: Tier-Gate-Validation, Speed-Curve-Steigung, Pop-Skalierung

## Out of Scope

- **Multi-Lab-Stacking** → **T-025b**
- **RP-Pool-Pattern**: NICHT angedacht (T-025 ist Wallclock-basiert)

## Notes

- Foundation in T-025 reicht für Demo, T-069 macht es Production-grade.
- Specialist-Track (T-098) gibt Lab-Speed-Multiplier auf bestimmten Branches —
  separater Hook im T-098-Ticket.
