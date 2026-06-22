# T-094c: Bau-Queue Slot-Erweiterung via Hub + Forschung

**Type:** Feature
**Status:** Done (HQ-Bonus implementiert; Logistics-Forschung in T-094d split)
**Effort:** S (~1.5h)
**Depends on:** T-094 (Bau-Queue Foundation), T-025 (Forschungs-Framework)
**Blocks:** —

## Beschreibung

T-094 hardcodet `MAX_CONCURRENT_BUILDS = 3`. T-094c macht das tunbar:
- Hub-Upgrade-Bonus: +1 Slot pro Hub-Level-5 (L5=4 Slots, L10=5, L15=6)
- Logistics-Forschung: +1 Slot pro Forschungs-Level (max 3)
- Maximum cap z.B. 8 Slots

## Acceptance Criteria

- [ ] `Player::getMaxConcurrentBuilds(Planet, $now): int` — aggregiert
      Foundation-Cap + Hub-Bonus + Logistics-Forschung
- [ ] BuildBuildingCommandService + UpgradeBuildingCommandService nutzen
      den dynamischen Wert statt Konstante
- [ ] Neue Forschung `logistics_1` (Tier-2; max 3 Levels; Prereq:
      basic_mining + IRON_SMELTER L1)
- [ ] Demo-CLI Status zeigt aktuellen Cap (z.B. "Build-Queue: 2/4")
- [ ] Tests
- [ ] Doc: buildings.md ergänzen

## Out of Scope

- Construction-Queue als persistente Entity mit Auto-Start (klassisches
  OGame-Queue-Pattern) — Foundation bleibt parallel-Slots
