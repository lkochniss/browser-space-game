# T-006: Hub-Gebäude

**Type:** Feature
**Status:** Done
**FX:** No
**MIG:** No
**Depends on:** T-004 ✓

## Description

`docs/Hub.md`: Hub erhöht Pop-Cap des Planeten. Skaliert mit Level.

## Scope (final)

`BuildingType::HUB` + Cap-Bonus auf BuildingType-Level. Auto-Recalc wenn Buildings sich ändern. Bau-Voraussetzungen (Resources, Pop) bleiben für T-009.

## AC

- [x] `BuildingType::HUB` neu
- [x] `BuildingType::getPopulationCapBonusPerLevel()` Methode (HUB=50, default=0)
- [x] `Planet::BASE_POPULATION_CAP = 100` Konstante
- [x] `Planet::recalculatePopulationCap()` öffentliche Methode (für T-010 Upgrade)
- [x] Pop-Cap-Berechnung: `BASE + Σ(building.type.bonusPerLevel * building.level)`
- [x] Auto-Recalc in `Planet::addBuilding()` nach erfolgreichem Add
- [x] Reine Berechnung — keine Tick-Abhängigkeit
- [x] Bestehende Tests grün (74/74, +10: BuildingTypeTest 2, HubPopulationCapTest 7, IT 1)

## Geklärte Fragen

1. **Cap-Boost:** +50 pro Hub-Level
2. **Max-Hubs:** Kein Limit
3. **Recalc-Strategie:** Auto in `Planet::addBuilding`
4. **Start-Hub:** Nein — Cap bleibt 100, Hub kommt später (T-009)

## Implementation

- `src/Building/ValueObject/BuildingType.php`: HUB case + `getPopulationCapBonusPerLevel()` match
- `src/Planet/Model/Planet.php`: `BASE_POPULATION_CAP` const, `recalculatePopulationCap()` public, addBuilding triggert recalc
- `tests/Building/ValueObject/BuildingTypeTest.php` (neu)
- `tests/Planet/Model/HubPopulationCapTest.php` (neu, 7 Cases)
- `tests/Persistence/PlayerPlanetPersistenceTest.php` (+1 IT für Hub+Cap-Persistenz)

## Bekannte Lücken (für Folgetickets)

- **Level-Change Recalc**: `Building::setLevel()` triggert keinen Cap-Recalc auf dem zugeordneten Planet. T-010 Upgrade muss `Planet::recalculatePopulationCap()` explizit aufrufen (oder eigenen Mutator `Planet::upgradeBuilding(building, newLevel)` einführen). Test `test_recalculate_after_level_change_must_be_explicit` dokumentiert dieses Verhalten.
- **Building entfernen**: Es gibt kein `Planet::removeBuilding()` heute. Wenn implementiert (T-009 / oder eigener TD-Ticket), muss recalc folgen.

## Folge-Tickets

- T-009 Building-Cost (verlangt Pop+Resources beim Bau)
- T-010 Building-Upgrade (Level-Change → Recalc)

### Token Usage (estimate)
- Input: ~7k
- Output: ~3k
