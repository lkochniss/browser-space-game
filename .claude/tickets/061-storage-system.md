# T-061: Storage-System (Lager-Kapazität pro Resource)

**Type:** Feature
**Status:** Done
**FX:** No
**MIG:** No (cap ist live-computed, keine neuen Spalten; neue BuildingType-Enum-Cases passen in vorhandene varchar(32))

## Description

Aus T-001-Klärung: Jedes Ressourcen-Gebäude bringt automatisch etwas Lager-Kapazität mit. Echte Speicher-Gebäude bringen pro Level deutlich mehr. Bei vollem Lager pausiert die Produktion (Stop-Strategie).

## AC

- [x] `ResourceCategory::getBaseCap()` per Category (RENEWABLE 500, FINITE 100, REFINED 100)
- [x] `BuildingType::getStorageContribution(ResourceType): int` per Level
  - Mining-Mines: +100/level für eigene Resource (Iron-Mine → Iron-Storage)
  - HUB: +200/level für W/F/O (Lebensraum-Bonus)
  - IRON_SMELTER: +100/level für IRON_BAR
  - Storage-Buildings: +1000/level (W/F/O-Tanks: +2000/level)
- [x] `Planet::getStorageCapacity(ResourceType)` live-computed: `base + Σ(contribution × level)` (analog zu Pop-Cap)
- [x] 6 neue Storage-Buildings: `IRON_STORAGE`, `COAL_STORAGE`, `IRON_BAR_STORAGE`, `WATER_TANK`, `FOOD_SILO`, `OXYGEN_STORAGE`
- [x] `BuildingCostConfig` Storage-Building-Costs (Iron + Coal)
- [x] `ResourceProductionProcessor` (Mining): clamp extraction durch Storage-Cap (Stop bei Voll)
- [x] `RefinementProductionProcessor`: clamp output durch Output-Storage-Cap; Inputs nur anteilig debitiert
- [x] Cap-Stop pausiert Produktion (kein Verfall) — entspricht User-Wahl
- [x] Bestehende Tests grün (158/158, +15: 3 ResourceCategory, 8 StorageCapacity, 2 Mining-Cap, 2 Refinement-Cap)

## Geklärte Fragen

1. **Base-Cap-Strategie:** Pro ResourceCategory + Building-Contribution
2. **Cap-Verhalten:** Stop (Produktion pausiert)
3. **Storage-Building:** Pro Resource ein eigener Type (heute 6 implementiert; weitere 5 für Copper/Si/Al/Ti/U als Folge wenn POIs T-019/T-020 die Erze liefern)
4. **Cap-Berechnung:** Live computed (analog Pop-Cap T-006)

## Implementation

- `src/Resource/ValueObject/ResourceCategory.php` (+`getBaseCap`)
- `src/Building/ValueObject/BuildingType.php` (+6 Storage-Cases, +`getStorageContribution`)
- `src/Building/Service/BuildingCostConfig.php` (+6 Storage-Costs)
- `src/Planet/Model/Planet.php` (+`getStorageCapacity`)
- `src/Tick/Processor/ResourceProductionProcessor.php` (cap-clamp on extraction)
- `src/Tick/Processor/RefinementProductionProcessor.php` (cap-clamp on output)
- `tests/Resource/ValueObject/ResourceCategoryTest.php` (3 cases, DataProvider)
- `tests/Planet/Model/StorageCapacityTest.php` (8 cases)
- `tests/Tick/Processor/ResourceProductionProcessorTest.php` (+2: cap-stop + partial-clamp)
- `tests/Tick/Processor/RefinementProductionProcessorTest.php` (+2: cap-stop + partial-clamp)

## Edge Cases (getestet)

- Empty planet: only Base-Cap
- Mine L1/L3 contribute correctly
- Storage-Buildings 10× größer als Mines
- WATER_TANK 2000/level, HUB 200/level für W/F/O
- Smelter contributes only to IRON_BAR (nicht Iron/Coal)
- Multiple Buildings stack additively
- Mining-Storage-Cap blockiert Production (1000 Deposit + voller Storage = 0 extracted)
- Mining partial-clamp (5 room, will produce 10 → produces 5)
- Refinement Cap-Stop (full Bar-Storage = 0 produced)
- Refinement partial-clamp (2 room, 3 bars desired → 2 produced + proportional inputs debited)

## Folge-Hinweise

- 5 weitere Storage-Buildings (Copper/Si/Al/Ti/U) wenn diese Erze via POIs T-019/T-020 abbaubar werden
- Renewables ohne Hub-Bau auf Start-Planet: Cap=500 → 100 Start-Wert weit unter Cap. Pop-Verbrauch reduziert über Zeit
- Resource-Production über Cap (z.B. wenn Cap später schrumpft) wird heute toleriert (kein Auto-Reduce der Resource); nur Production blockiert
- T-014 Kolonisation: neue Planeten brauchen Initial-Storage = nur Base-Cap → Aufbau-Phase mit Cap-Druck

### Token Usage (estimate)
- Input: ~14k
- Output: ~6k
