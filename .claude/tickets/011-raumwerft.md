# T-011: Raumwerft

**Type:** Feature
**Status:** Done
**FX:** No
**MIG:** No
**Depends on:** T-009
**Blocks:** T-012, T-013, T-014, T-015, T-016, T-102 (gesamte Schiff-Kette)

## Description

Building-Voraussetzung für Schiffsbau. Reine Definition + Voraussetzungs-Check für T-012ff.

## AC

- [x] `BuildingType::SHIPYARD` enum-case + storage-contribution-case (kein Storage)
- [x] `BuildingCostConfig` SHIPYARD-Eintrag (500 Iron + 100 Coal + 200 Aluminum + 50 Titanium, 30 Pop)
- [x] `BuildingDurationConfig` SHIPYARD-Eintrag (3600s = 60min Strategic-Building)
- [x] `Planet::getShipyardLevel(?DateTimeImmutable): int` Helper (höchstes Level einer fertigen Shipyard)
- [x] `Planet::hasShipyard(?DateTimeImmutable): bool` Convenience
- [x] Tests: BuildingType, BuildingCostConfig (×2), BuildingDurationConfig (DataProvider erweitert), Planet (6 Cases)

## Affected

- `src/Building/ValueObject/BuildingType.php` (SHIPYARD-case + storage-cases)
- `src/Building/Service/BuildingCostConfig.php` (Cost-Eintrag)
- `src/Building/Service/BuildingDurationConfig.php` (Duration-Eintrag)
- `src/Planet/Model/Planet.php` (Helper `getShipyardLevel`/`hasShipyard`)
- `tests/Building/ValueObject/BuildingTypeTest.php` (+1 Test)
- `tests/Building/Service/BuildingCostConfigTest.php` (+2 Tests)
- `tests/Building/Service/BuildingDurationConfigTest.php` (+1 Provider-Eintrag)
- `tests/Planet/Model/ShipyardLevelTest.php` (neu, 6 Tests)

## Geklärte Fragen

1. **Min-Level pro Schiffsklasse**: Out of Scope. Mapping wird in T-102 (Schiff-Klassen) definiert. Helper liefert Level, nicht Klassen-Lock.

## Test-Status

- 230/230 grün, 476 Assertions, 0.99s

## Out of Scope (Folge-Tickets)

- **T-012** Raumschiff-Base (nutzt `getShipyardLevel`)
- **T-102** Mark-Tier-Lock per Shipyard-Level
- **T-128** Schiffbau-Forschung (kann Shipyard-Boni geben)
