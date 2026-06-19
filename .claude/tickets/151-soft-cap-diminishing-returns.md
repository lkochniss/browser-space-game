# T-151 Soft-Cap / Diminishing Returns (Sanft, -0.1%/Step)

**Type:** Feature
**Status:** Draft
**Effort:** M
**Depends on:** T-005 (Pop), T-009 (Building-Cost), T-061 (Storage)
**Blocks:** —

## Beschreibung
Sanftes Anti-Run-Away-System. Diminishing Returns auf 3 Achsen, je -0.1% pro Step:
- Pop ab 1 Mio: zusätzliche Pop-Wachstum-Rate sinkt -0.1%/100k Pop
- Building-Level ab 20+: jedes weitere Level kostet ×(1.05)^lvl exponentiell mehr
- Resource-Stockpile ab 100k pro Resource: -0.1%/10k Mining-Effizienz

## Acceptance Criteria
- [ ] PopGrowthService: ab Pop > 1_000_000 → Multiplier `1 - (pop - 1_000_000) / 1_000_000_000` (clamp min 0.1)
- [ ] BuildingCostService: für level > 20 → Cost ×(1.05)^(lvl-20) on top of existing scaling
- [ ] ResourceProductionProcessor: ab Stockpile > 100k pro ResourceType → Mining-Multiplier `1 - (stockpile - 100k) / 1_000_000` (clamp min 0.5)
- [ ] Soft-Cap-Indicators in UI: zeigen Multiplier-Wirkung pro Achse
- [ ] Konfigurierbar (für Tuning) via Service-Constants

## Affected Tests
- tests/Population/Service/PopulationDiminishingReturnsTest.php
- tests/Building/Service/HighLevelCostExponentialTest.php
- tests/Resource/Service/StockpileMiningPenaltyTest.php

## Fixtures Needed
Yes — Late-Game-Players mit hohen Pop/Building/Stockpile

## Notes
- "Sanft" gewählt (-0.1%/Step) — Frustration vermeiden
- Soft-Cap motiviert Specialization über Stockpile-Hoarding
- Wirkt zusammen mit Storage-Cap (T-061): Stockpile-Cap natürlich + Mining-Penalty additional
