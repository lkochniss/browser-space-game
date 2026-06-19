# T-009: Building-Kosten + Bauprozess

**Type:** Feature
**Status:** Done
**FX:** No
**MIG:** Yes (`Version20260618000003` — `buildings.finished_at`)
**Depends on:** T-004 ✓

## Description

`docs/Bevölkerung.md` + `docs/Raumschiff.md`: Bau verbraucht Ressourcen + freie Pop. T-009 baut: BuildingCost-VO, Cost-Config, BuildBuildingCommand + Handler + Service. Pop wird permanent assigned (Building hält Pop-Slot bis Verfall).

## Scope (final)

- Cost (Resources + Pop) pro BuildingType
- Command-Bus-Integration
- Resource-Debit + Pop-Bindung
- Domain-Exceptions
- `finishedAt`-Field als Stub für T-062 (Echtzeit-Mechanik)

## AC

- [x] `BuildingCost` readonly VO (resources-Map + populationCost)
- [x] `BuildingCostConfig` Service mit Cost je BuildingType:
  - IRON_MINE: 50 Iron + 5 Pop
  - COAL_MINE: 30 Iron + 5 Pop
  - COPPER_MINE: 60 Iron + 5 Pop
  - SILICON_MINE: 80 Iron + 5 Pop
  - ALUMINUM_MINE: 80 Iron + 5 Pop
  - TITANIUM_MINE: 100 Iron + 5 Pop
  - URANIUM_MINE: 100 Iron + 30 Coal + 10 Pop
  - HUB: 100 Iron + 50 Coal + 10 Pop
- [x] `BuildBuildingCommand(planetId, buildingType)` + `BuildBuildingCommandHandler` + `BuildBuildingCommandService`
- [x] Service-Flow: find planet → check resources → check free pop → debit resources → assign pop → addBuilding → flush
- [x] Domain-Exceptions:
  - `InsufficientResourcesException(resourceType, required, available)`
  - `InsufficientPopulationException(required, availableFree)`
  - `PlanetNotFoundException(planetId)`
- [x] Pop-Bindung permanent (assigned bleibt erhöht solange Building existiert)
- [x] `Building::finishedAt` (?DateTimeImmutable) Field + Migration `Version20260618000003` — Stub für T-062
- [x] `addBuilding` triggert weiterhin `recalculatePopulationCap()` (T-006) → HUB-Bau erhöht Cap sofort
- [x] Failing Validation → kein State-Change (Resources/Pop unverändert, kein Building hinzugefügt)
- [x] Bestehende Tests grün (81/81, +7 IT in BuildBuildingCommandTest)

## Geklärte Fragen

1. **Bauzeit:** Echtzeit, nicht Tick-Mechanik. T-009 nur Stub-Field. T-062 baut Wall-Clock-Mechanik.
2. **Pop-Bindung:** Permanent assigned solange Building existiert.
3. **Cost-Material:** Heute Erze direkt (T-001/T-002). T-003 (Erzeugnisse) wird high-tier-Costs später migrieren.
4. **Cost-Werte:** Defaults wie oben.

## Implementation

- `src/Building/ValueObject/BuildingCost.php` (neu, readonly)
- `src/Building/Service/BuildingCostConfig.php` (neu)
- `src/Building/Command/BuildBuildingCommand.php` (neu)
- `src/Building/Command/BuildBuildingCommandHandler.php` (neu)
- `src/Building/Service/BuildBuildingCommandService.php` (neu)
- `src/Building/Exception/InsufficientResourcesException.php` (neu)
- `src/Building/Exception/InsufficientPopulationException.php` (neu)
- `src/Building/Exception/PlanetNotFoundException.php` (neu)
- `src/Building/Model/Building.php` (`finishedAt` Field + Getter/Setter)
- `migrations/Version20260618000003.php` (neu)
- `tests/Building/Command/BuildBuildingCommandTest.php` (neu, 7 IT)

## Edge Cases (getestet)

- Iron-Mine: happy path → resources debited, pop assigned, Building added, finishedAt=null
- Hub: cap raised to 150 nach Bau
- Insufficient resources → exception, no state change
- Resource not on planet (Coal für Hub, kein Coal-Entry) → InsufficientResourcesException
- Insufficient free pop → exception
- Planet not found → exception
- Validation failure → resources/pop/buildings unchanged

## Bekannte Lücken / Folge-Tickets

- **T-062 Echtzeit-Bauzeit**: `finishedAt` ist Stub (default null = instant). Wall-Clock-Mechanik + Production/Cap-Gating folgt.
- **T-010 Building-Upgrade**: Level-Erhöhung mit skalierter Cost.
- **T-009.x Building-Verfall / Demolish-Cost-Refund**: Aktuell kein Demolish-Flow → Pop bleibt assigned bis Ende.
- **T-003 Erzeugnisse**: Higher-Tier-Buildings (Eisenhütte etc.) werden mit Erzeugnissen statt Erzen bauen.

### Token Usage (estimate)
- Input: ~12k
- Output: ~6k
