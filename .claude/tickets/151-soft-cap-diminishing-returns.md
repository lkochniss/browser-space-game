# T-151 Soft-Cap / Diminishing Returns (Sanft, -0.1%/Step)

**Type:** Feature
**Status:** Done
**Effort:** S
**MIG:** No
**Depends on:** T-005 (Pop), T-009 (Building-Cost), T-061 (Storage)
**Blocks:** —

## Beschreibung

Sanftes Anti-Run-Away-System. Diminishing Returns auf 3 Achsen, je -0.1% pro Step:
- **Pop ab 1M**: Pop-Wachstum-Rate sinkt graduell
- **Building-Level ab 20+**: jedes weitere Level kostet ×(1.05)^(lvl-20) zusätzlich
- **Resource-Stockpile ab 100k pro Resource**: Mining-Effizienz sinkt graduell

## Acceptance Criteria

- [x] `SoftCapConfig` Service in `src/Common/Service/` mit 3 Multiplier-Methoden:
  - `popGrowthMultiplier(int $popTotal): float` — `1 - (pop - 1M) / 1B`, clamp min 0.1
  - `buildingCostMultiplier(int $currentLevel): float` — `1.05 ^ max(0, lvl - 20)`
  - `miningMultiplier(int $stockpile): float` — `1 - (stockpile - 100k) / 1M`, clamp min 0.5
- [x] Hook 1: `PopulationConsumptionProcessor::logisticGrowthDelta` × `popGrowthMultiplier(total)`
- [x] Hook 2: `BuildingCostConfig::getCost` Multiplier kombiniert mit existierendem 2^level Doubler
- [x] Hook 3: `ResourceProductionProcessor` × `miningMultiplier(currentStockpile)` pro Resource
- [x] Konstanten als public class consts (Tuning ohne Code-Change möglich)
- [x] Default-Args (`new SoftCapConfig()`) damit Plain-Tests ohne DI-Container funktionieren
- [x] Tests: 13 Unit (SoftCapConfig DataProvider × 3), 4 Unit (BuildingCostSoftCap),
  2 Unit (PopulationGrowthSoftCap), 2 Unit (MiningStockpileSoftCap)
- [x] Suite grün (417/417, 1439 assertions)

## Out of Scope (Folge-Tickets)

- **UI-Indicators für Soft-Cap-Wirkung** → Web-Layer (T-034+)
- **Per-Player-Soft-Cap-Anpassung via Tech-Forschung** → T-127 Mining/Industrie-Branch
  (kann z.B. die Mining-Threshold von 100k auf 200k anheben)

## Notes

- "Sanft" gewählt (-0.1%/Step) — Frustration vermeiden, aber dauerhaft spürbarer Anker
- Wirkt zusammen mit Storage-Cap (T-061): Stockpile-Cap natürlich + Mining-Penalty
  zusätzlich
- T-122 Player-Background + T-098 Specialist-Tracks könnten später per-Player-
  Override-Multiplier ergänzen

## Files

**Neu:**
- `src/Common/Service/SoftCapConfig.php`
- `tests/Common/Service/SoftCapConfigTest.php`
- `tests/Building/Service/BuildingCostSoftCapTest.php`
- `tests/Tick/Processor/PopulationGrowthSoftCapTest.php`
- `tests/Tick/Processor/MiningStockpileSoftCapTest.php`

**Geändert:**
- `src/Tick/Processor/PopulationConsumptionProcessor.php` (SoftCapConfig in growth-rate)
- `src/Building/Service/BuildingCostConfig.php` (SoftCapConfig × Cost-Multiplier)
- `src/Tick/Processor/ResourceProductionProcessor.php` (SoftCapConfig × Mining-Output)
