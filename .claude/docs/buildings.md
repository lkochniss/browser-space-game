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
| `SHIPYARD` | Voraussetzung Schiffsbau (T-011) | — | 500 Iron + 100 Coal + 200 Al + 50 Ti | 30 | 60min |
| `PROBE_LAB` | Voraussetzung Sondenbau (T-013) | — | 200 Iron + 100 Si + 50 Cu | 15 | 30min |
| `RECYCLING_PLANT` | Konsumiert DEBRIS_* → random FINITE/REFINED (T-021) | — (recycling) | 250 Iron + 100 Cu + 80 Si | 10 | 30min |
| `TELESCOPE` | Reveals N=Level random unseen Systems / Tick (T-018) | — (discovery) | 150 Iron + 200 Si + 100 Cu | 10 | 45min |

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

## Strategic Buildings (Voraussetzungs-Gates)

| Building | Gate | Helper | Cross-Domain |
|----------|------|--------|--------------|
| `SHIPYARD` | Schiffsbau | `Planet::hasShipyard($now)` / `getShipyardLevel($now)` | T-011, ships.md |
| `PROBE_LAB` | Sondenbau | `Planet::hasProbeLab($now)` / `getProbeLabLevel($now)` | T-013, probes.md |
| `TELESCOPE` | Galaxy-Discovery | `Planet::getTelescopeLevel($now)` | T-018, discovery.md |
| `RECYCLING_PLANT` | DEBRIS-Konversion | (über `RecyclingProcessor` ausgewertet) | T-021, resources.md |

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
2. `ResourceProductionProcessor` (Mining + T-151 Stockpile-SoftCap)
3. `RefinementProductionProcessor` (Refinement)
4. `PopulationConsumptionProcessor` (Pop verbraucht W/F + T-151 Pop-Soft-Cap)
5. `ShipSupplyProcessor` (T-012, Ship-Life-Support)
6. `RecyclingProcessor` (T-021, DEBRIS → random Output)

Alle Processors bekommen `?DateTimeImmutable $now` aus `gameState.getClock()->now()` über TickEngine. Siehe auch tick.md für globale Tick-Services (FleetArrival, Salvage, TelescopeDiscovery).

## Geplant

- **T-064** Bauzeit-Speed-Boost (Forschung + Spezial-Buildings reduzieren Duration); Decisions vorab dokumentiert (multiplikativ, no-retroactive, upgrades=initial)
- **High-Tier Cost-Migration:** Buildings mit IRON_BAR statt IRON_ORE als Cost
- **T-025/T-026** Forschungs-Gating für höhere Building-Levels
- **T-065** Power-Net (Energy-System) — Reaktoren + Consumer
- **T-068** Defense-Buildings (Shield/Turret/Sensor/AA)
- **T-069** Research-Lab Tier mit RP-Output
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
