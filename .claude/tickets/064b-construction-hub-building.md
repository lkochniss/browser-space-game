# T-064b: Construction-Hub Building (lokaler Bauzeit-Boost)

**Type:** Feature
**Epic:** Building System
**Domain:** Building
**Blocked By:** T-064
**Status:** Done
**Effort:** S (~1.5h)
**Depends on:** T-064 (Forschungs-Bauzeit-Boost)
**Blocks:** —

## Beschreibung

T-064 hat Forschungs-Multiplier für Bauzeit (multiplikativ über alle Buildings
auf allen Player-Planeten). T-064b ergänzt einen **lokalen** Effekt: ein
CONSTRUCTION_HUB-Building beschleunigt nur Bauten auf seinem Planeten.

## Acceptance Criteria

- [ ] `BuildingType::CONSTRUCTION_HUB`
- [ ] BuildingCostConfig + DurationConfig Einträge (z.B. 200 IRON_BAR + 100
      SILICON, 30min Build, 10 pop)
- [ ] Tier-1 (gated by `metallurgy` L1; Building-Prereq IRON_SMELTER L1)
- [ ] Slot-Size 2 (analog HUB)
- [ ] Strikt-unique pro Planet (T-171 Pattern)
- [ ] `Planet::getConstructionSpeedBoostFromBuilding(now): float` — z.B. ×1.10
      pro Level
- [ ] BuildBuildingCommandService + UpgradeBuildingCommandService nutzen den
      lokalen Boost zusätzlich zu Forschung + Planet-Type
- [ ] Tests
- [ ] Doc: buildings.md ergänzen

## Out of Scope

- Multi-Planet-Stacking (lokal-only ist Decision)
