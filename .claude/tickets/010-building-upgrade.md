# T-010: Building Level-Upgrade

**Type:** Feature
**Status:** Done
**FX:** No
**MIG:** No
**Depends on:** T-009 ✓

## Description

Buildings können geupgradet werden. Cost skaliert exponentiell `2^currentLevel` über alle Resources + Pop. Hub-Upgrade triggert Cap-Recalc (closes T-006-Lücke).

## AC

- [x] `UpgradeBuildingCommand(planetId, buildingId)` + Handler + Service
- [x] `BuildingCostConfig::getCost(type, currentLevel = 0)` mit Skalierung `2^currentLevel`:
  - Initial-Build: `currentLevel=0` → 2^0 = 1 → base
  - L1 → L2: `currentLevel=1` → 2^1 = 2 → 2× base
  - L5 → L6: `currentLevel=5` → 2^5 = 32 → 32× base
  - Skalierung gilt für Resources UND `populationCost`
- [x] Service-Flow: find planet → find building → calc cost → check resources → check pop → debit → assign → setLevel++ → recalcCap → flush
- [x] Cap-Recalc nach Upgrade — closes T-006 stale-cap-Lücke (Hub L1→L2: 150→200)
- [x] `BuildingNotFoundException(planetId, buildingId)` neu
- [x] Failing Validation → kein State-Change
- [x] Bestehende Tests grün (93/93, +12: 5 BuildingCostConfig + 7 UpgradeBuildingCommand)

## Geklärte Fragen

1. **Cost-Skalierung:** Exponentiell 2^currentLevel
2. **Pop-Bindung:** Steigt mit gleicher Formel (Pop scales 2^level genauso)
3. **Level-Cap:** Kein Cap heute — späteres Forschungs-Gating (T-025/T-026) regelt das
4. **Cap-Recalc:** Service ruft `Planet::recalculatePopulationCap()` nach setLevel — closes T-006-Lücke

## Implementation

- `src/Building/Service/BuildingCostConfig.php` (`getCost(type, currentLevel=0)` mit Skalierung)
- `src/Building/Command/UpgradeBuildingCommand.php` (neu)
- `src/Building/Command/UpgradeBuildingCommandHandler.php` (neu)
- `src/Building/Service/UpgradeBuildingCommandService.php` (neu)
- `src/Building/Exception/BuildingNotFoundException.php` (neu)
- `tests/Building/Service/BuildingCostConfigTest.php` (5 unit cases)
- `tests/Building/Command/UpgradeBuildingCommandTest.php` (7 IT cases)

## Edge Cases (getestet)

- Iron-Mine L1→L2: cost verdoppelt, level++
- Hub L1→L2: cap 150→200 (recalc)
- Insufficient resources → throw, no state change
- Insufficient pop → throw
- Building not found → throw
- Planet not found → throw
- Validation failure → resources/pop/buildings unchanged

## Bekannte Lücken / Folge-Tickets

- **T-062 Echtzeit-Bauzeit:** Upgrade ist heute instant. T-062 muss `finishedAt` neu setzen + isReady-Gate auch beim Upgrade prüfen.
- **T-025/T-026 Forschung:** Höhere Levels könnten via Forschung gegated werden (Level-Cap pro Type).
- **T-009.x Demolish:** Kein "Building entfernen + Pop release" Flow heute.

### Token Usage (estimate)
- Input: ~10k
- Output: ~5k
