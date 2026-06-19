# Buildings

## BuildingTypes

| Type | Bonus / Wirkung | Mining-Resource | Cost (Resources) | Pop-Cost | Bauzeit (Base × 2^level) |
|------|-----------------|-----------------|------------------|----------|--------------------------|
| `IRON_MINE` | Iron-Production | IRON_ORE | 50 Iron | 5 | 5min |
| `COAL_MINE` | Coal-Production | COAL | 30 Iron | 5 | 5min |
| `COPPER_MINE` | Copper-Production | COPPER_ORE | 60 Iron | 5 | 5min |
| `SILICON_MINE` | Silicon-Production | SILICON | 80 Iron | 5 | 5min |
| `ALUMINUM_MINE` | Aluminum-Production | ALUMINUM_ORE | 80 Iron | 5 | 5min |
| `TITANIUM_MINE` | Titanium-Production | TITANIUM_ORE | 100 Iron | 5 | 5min |
| `URANIUM_MINE` | Uranium-Production | URANIUM_ORE | 100 Iron + 30 Coal | 10 | 5min |
| `HUB` | Pop-Cap +50 / Level | — | 100 Iron + 50 Coal | 10 | 30min |
| `IRON_SMELTER` | Refines IRON_BAR (2:1:1) | — (refinement) | 200 Iron + 100 Coal | 15 | 30min |
| `IRON_STORAGE` | +1000 IRON_ORE-Cap / Level | — | 100 Iron + 50 Coal | 5 | 15min |
| `COAL_STORAGE` | +1000 COAL-Cap / Level | — | 100 Iron + 50 Coal | 5 | 15min |
| `IRON_BAR_STORAGE` | +1000 IRON_BAR-Cap / Level | — | 100 IRON_BAR | 10 | 15min |
| `WATER_TANK` | +2000 WATER-Cap / Level | — | 100 Iron | 5 | 15min |
| `FOOD_SILO` | +2000 FOOD-Cap / Level | — | 100 Iron | 5 | 15min |
| `OXYGEN_STORAGE` | +2000 OXYGEN-Cap / Level | — | 150 Iron | 5 | 15min |

Bauzeit-Skalierung: `effectiveDuration = base × 2^currentLevel`. L1→L2 = 2× base, L5→L6 = 32× base. Analog Cost (T-010).

## Bauprozess (T-009 + T-062)

- Player dispatched `BuildBuildingCommand(planetId, buildingType)` über CommandBus
- Service prüft Resources + freie Pop, zieht Cost ab, assigniert Pop, fügt Building hinzu, persistiert
- Pop bleibt assigned solange Building existiert
- T-062: Service injiziert `ClockInterface` + `BuildingDurationConfig`; setzt `finishedAt = now + duration`
- Building wirkt erst, wenn `isReady($now)` true → Production/Cap-Bonus pausieren während Bauphase

`Building::isReady(?DateTimeImmutable $now)`:
- `finishedAt === null` → ready (Legacy/Tests)
- `finishedAt !== null && now === null` → konservativ NOT ready
- `finishedAt !== null && now !== null` → `finishedAt <= now`

## Upgrade (T-010 + T-062)

- `UpgradeBuildingCommand(planetId, buildingId)` über CommandBus
- Cost skaliert exponentiell: `base × 2^currentLevel` für Resources UND Pop-Cost
- Bauzeit auch exponentiell (gleiche Formel)
- Während Upgrade-Phase: Building level-inkrementiert, aber `finishedAt` in Zukunft → not ready → kein Cap-Bonus, keine Production. Effekt: Hub-Upgrade L1→L2 lässt Cap auf base fallen, bis Upgrade fertig.
- Failing Validation → kein State-Change

## Domain-Exceptions

| Exception | Trigger |
|-----------|---------|
| `InsufficientResourcesException` | Resource-Bestand < Cost |
| `InsufficientPopulationException` | freie Pop < Pop-Cost |
| `PlanetNotFoundException` | PlanetId existiert nicht |
| `BuildingNotFoundException` | BuildingId nicht auf gefundenem Planet |

Alle extenden `\DomainException`. Failing Validation → kein State-Change (Pre-Check vor Mutation).

## Cap-Recalc

- **Pop-Cap** (T-006/T-062): `Planet::addBuilding(building, ?now)` triggert auto `recalculatePopulationCap(?now)`. `UpgradeBuildingCommandService` ruft expliziten Recalc mit `now`. **Cap zählt nur ready Buildings** (T-062).
- **`ConstructionCompletionProcessor`** (T-062): TickProcessor läuft als ERSTER pro Tick und ruft `recalculatePopulationCap($now)` — sobald ein Hub fertig wird, fließt der Cap-Bonus im selben Tick ein. Idempotent.
- **Storage-Cap** (T-061): live-computed via `Planet::getStorageCapacity(ResourceType)`. Kein Recalc nötig, kein Sync-Problem.

## Refinement (T-003)

`IRON_SMELTER` ist kein Mining-Building, sondern Refinement-Building. Mapping nicht in `ResourceBuildingMap` sondern in `RefinementConfig`. Eigener Tick-Processor (`RefinementProductionProcessor`).

## Storage (T-061)

Jedes Building bringt via `BuildingType::getStorageContribution(ResourceType)` eine Cap-Beitrag pro Level:

- Mining-Mines: +100 für eigene Resource (kleiner Pre-Lager)
- IRON_SMELTER: +100 für IRON_BAR
- HUB: +200 für W/F/O (Lebensraum-Bonus)
- Storage-Buildings: +1000 (Erze/Bars), +2000 (Renewables)

`Planet::getStorageCapacity(ResourceType)` live-computed:
`cap = ResourceCategory.baseCap + Σ(building.type.contribution × building.level)`

Production/Refinement clampen Output am Cap. Volles Lager → Produktion pausiert (Stop-Strategie, kein Verfall). Inputs nur anteilig zur tatsächlichen Output-Menge debitiert.

## Tick-Reihenfolge

1. `ConstructionCompletionProcessor` (T-062) — recalc Pop-Cap mit aktueller Clock
2. `ResourceProductionProcessor` (Mining)
3. `RefinementProductionProcessor` (Refinement)
4. `PopulationConsumptionProcessor` (Pop verbraucht W/F)

Alle Processors bekommen `?DateTimeImmutable $now` aus `gameState.getClock()->now()` über TickEngine.

## Geplant

- **T-064** Bauzeit-Speed-Boost (Forschung + Spezial-Buildings reduzieren Duration)
- **High-Tier Cost-Migration:** Buildings mit IRON_BAR statt IRON_ORE als Cost (z.B. Raumwerft T-011)
- **T-025/T-026** Forschungs-Gating für höhere Building-Levels
- **Demolish/Refund-Flow** (eigenes Ticket bei Bedarf)
- **Construction-Queue:** mehrere Bauten parallel + Worker-Limit

## Files

- `src/Building/ValueObject/BuildingType.php` (Enum)
- `src/Building/ValueObject/BuildingCost.php` (VO, readonly)
- `src/Building/Service/BuildingCostConfig.php` (Cost je Type, Skalierung 2^level)
- `src/Building/Service/BuildingDurationConfig.php` (Bauzeit je Type, Skalierung 2^level)
- `src/Building/Service/ResourceBuildingMap.php` (Mine ↔ Resource)
- `src/Building/Command/BuildBuildingCommand.php` + Handler
- `src/Building/Command/UpgradeBuildingCommand.php` + Handler
- `src/Building/Service/BuildBuildingCommandService.php` (Clock+Duration injected)
- `src/Building/Service/UpgradeBuildingCommandService.php` (Clock+Duration injected)
- `src/Building/Exception/*.php`
- `src/Building/Model/Building.php` (Entity, `isReady`-Methode)
- `src/Tick/Processor/ConstructionCompletionProcessor.php` (T-062)
