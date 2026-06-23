# T-094d: Bau-Queue Slot-Bonus via Logistics-Forschung

**Type:** Feature
**Epic:** Building System
**Domain:** Building
**Blocked By:** T-094, T-094c, T-025
**Status:** Done
**Effort:** S (~1h)
**Depends on:** T-094 (Bau-Queue Foundation), T-094c (HQ-Slot-Bonus), T-025 (Forschungs-Framework)
**Blocks:** —

## Beschreibung

T-094c gibt HQ-Level +1 Parallel-Slot pro 5 Level (cap 8). T-094d ergänzt
einen zweiten Bonus-Pfad via neue Forschung `logistics_1`.

## Acceptance Criteria

- [ ] Neue ResearchNode `logistics_1` (Tier-2, 3 Level, jeder Level +1 Slot)
- [ ] Prereqs: `basic_mining` L1 + `IRON_SMELTER` L1 (analog `metallurgy`)
- [ ] `Planet::getEffectiveBuildQueueCap` erweitert: + Logistics-Level
- [ ] Hard-Cap bleibt 8 (HQ-Stack + Logistics zusammen)
- [ ] Tests
- [ ] Doc: buildings.md erweitern

## Notes

- Aus T-094c-Original ausgegliedert weil:
  - HQ-Bonus war direkt umsetzbar nach T-172
  - Logistics-Research braucht eigene Decision (Cost, Prereqs, Bonus-Curve)
- Foundation: gleiche additive Logik wie HQ-Bonus
