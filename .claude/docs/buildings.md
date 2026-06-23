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
| `HQ` (T-172) | Pop-Cap +25/Lvl, W/F/O-Storage +200/Lvl, Slot-Bonus (PlanetSize-capped) | — | 200 Iron + 100 Coal | 20 | 60min |
| `HUB` (T-172 refactor, multi-instance) | Pop-Cap +100/Lvl, kein Storage | — | 50 Iron + 25 Coal | 5 | 15min |
| `IRON_SMELTER` | Refines IRON_BAR (2:1:1) | — (refinement) | 200 Iron + 100 Coal | 15 | 30min |
| `IRON_STORAGE` | +1000 IRON_ORE-Cap / Level | — | 100 Iron + 50 Coal | 5 | 15min |
| `COAL_STORAGE` | +1000 COAL-Cap / Level | — | 100 Iron + 50 Coal | 5 | 15min |
| `IRON_BAR_STORAGE` | +1000 IRON_BAR-Cap / Level | — | 100 IRON_BAR | 10 | 15min |
| `WATER_TANK` | +2000 WATER-Cap / Level | — | 100 Iron | 5 | 15min |
| `FOOD_SILO` | +2000 FOOD-Cap / Level | — | 100 Iron | 5 | 15min |
| `OXYGEN_STORAGE` | +2000 OXYGEN-Cap / Level | — | 150 Iron | 5 | 15min |
| `WATER_RECLAIMER` | +10 WATER/tick/level | (Renewable) | 100 Iron | 5 | 15min |
| `AGRI_DOME` | +6 FOOD/tick/level | (Renewable) | 100 Iron | 5 | 15min |
| `ATMOSPHERIC_PROCESSOR` | +6 OXYGEN/tick/level | (Renewable) | 100 Iron | 5 | 15min |
| `SHIPYARD` | Voraussetzung Schiffsbau (T-011) | — | 500 Iron + 100 Coal + 200 Al + 50 Ti | 30 | 60min |
| `PROBE_LAB` | Voraussetzung Sondenbau (T-013) | — | 200 Iron + 100 Si + 50 Cu | 15 | 30min |
| `RECYCLING_PLANT` | Konsumiert DEBRIS_* → random FINITE/REFINED (T-021) | — (recycling) | 250 Iron + 100 Cu + 80 Si | 10 | 30min |
| `TELESCOPE` | Reveals N=Level random unseen Systems / Tick (T-018) | — (discovery) | 150 Iron + 200 Si + 100 Cu | 10 | 45min |
| `RESEARCH_LAB` | Voraussetzung Forschung; höheres Level reduziert Forschungs-Dauer (T-025) | — (research) | 200 Iron + 100 Si + 50 Cu | 15 | 45min |

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

## Tech-Tree-Locks (T-170)

Buildings sind hinter Forschung versteckt. Tier-0 (`IRON_MINE, HUB, RESEARCH_LAB,
WATER_TANK, FOOD_SILO, OXYGEN_STORAGE`) frei; alle anderen via Research gated.

`BuildingUnlockConfig::requiredResearch(BuildingType): ?{slug, level}` ist Single-
Source-of-Truth. `BuildBuildingCommandService` validiert vor Cost-Check und wirft
`BuildingLockedException`. Demo-CLI Build-Menu zeigt locked Buildings als
🔒 mit Reason.

| Forschung | Unlocks |
|-----------|---------|
| `basic_mining` | COAL_MINE, COPPER_MINE, IRON_STORAGE, COAL_STORAGE |
| `metallurgy` | IRON_SMELTER, IRON_BAR_STORAGE |
| `astronomy` | TELESCOPE, PROBE_LAB |
| `shipbuilding` | SHIPYARD |
| `advanced_mining` | SILICON_MINE, ALUMINUM_MINE, TITANIUM_MINE, URANIUM_MINE |
| `recycling` | RECYCLING_PLANT |

Forschungen selbst haben Building-Prereqs ("currently-has-ready"). Vollständiges
Tier-Mapping: research.md.

## Strategic Buildings (Voraussetzungs-Gates)

| Building | Gate | Helper | Cross-Domain |
|----------|------|--------|--------------|
| `SHIPYARD` | Schiffsbau | `Planet::hasShipyard($now)` / `getShipyardLevel($now)` | T-011, ships.md |
| `PROBE_LAB` | Sondenbau | `Planet::hasProbeLab($now)` / `getProbeLabLevel($now)` | T-013, probes.md |
| `TELESCOPE` | Galaxy-Discovery | `Planet::getTelescopeLevel($now)` | T-018, discovery.md |
| `RECYCLING_PLANT` | DEBRIS-Konversion | (über `RecyclingProcessor` ausgewertet) | T-021, resources.md |
| `RESEARCH_LAB` | Forschungs-Voraussetzung + Speed-Multiplier | `Planet::getResearchLabLevel($now)` | T-025, research.md |

## Storage (T-177 Generic-Volume — supersedes T-061)

Pro Planet existiert **ein einheitliches Volume-Storage in m³**. Jede Item-
Quantity belegt Volume via `ResourceVolumeConfig` (Pop = 10 m³, Wasser = 1 m³,
IRON_ORE = 2 m³, etc.). Per-Resource-Caps gibt es nicht mehr — alles teilt
sich einen Pool.

### Volume-Capacity-Formel

```
cap = Planet::BASE_VOLUME_CAPACITY (5000)
    + Σ (building.type.getVolumeContribution × building.level)
```

| Building | m³/Lvl | Rolle |
|----------|--------|-------|
| `WAREHOUSE` (T-177) | 500 | Hauptquelle Volume (Tier-0, non-unique) |
| `HQ` (T-172) | 25 | Verwaltungs-Buffer |
| Mines (alle 9) | 50 | Per-Mine kleiner Buffer für Output |
| Refineries (alle 9 inkl. IRON_SMELTER) | 50 | Per-Refinery Output-Buffer |
| `RECYCLING_PLANT` | 100 | Voluminöses Debris-Handling |
| `HUB` / QoL / Strategic-Buildings | 0 | Kein Storage-Beitrag |

### Planet-Storage-API

| Methode | Zweck |
|---------|-------|
| `getStorageVolumeCapacity(): int` | Gesamt-Cap in m³ |
| `getStorageVolumeUsed(): int` | Σ items × multi + pop × 10 |
| `getStorageVolumeFree(): int` | clamp(cap - used, ≥ 0) |
| `canAddItem(R, qty): bool` | Volume-Check für `qty × multi(R)` |
| `maxAddableQuantity(R, qty): int` | min(qty, floor(free / multi(R))) |
| `getStorageCapacity(R): int` *(legacy shim)* | `current(R) + maxAddable(R)` — Production-Processors |

### Cap-Stop-Verhalten (Q3 = a)

Volume-Cap-Stop für ALLE Production. Mines/Refineries/Renewables clampen
Output gegen `getStorageCapacity(R)`. Volles Lager → Produktion pausiert (kein
Verfall). Inputs nur anteilig zur tatsächlich addierten Output-Menge debitiert.

### T-061-Migration (Q1 = a)

Die 6 alten Storage-Buildings (IRON_STORAGE / COAL_STORAGE / IRON_BAR_STORAGE /
WATER_TANK / FOOD_SILO / OXYGEN_STORAGE) sind **gelöscht**. WAREHOUSE ist
ihre einzige Konsolidierung — non-unique, beliebig stapelbar.

## Tick-Reihenfolge

1. `ConstructionCompletionProcessor` (T-062) — recalc Pop-Cap mit aktueller Clock
2. `ResourceProductionProcessor` (Mining + T-151 Stockpile-SoftCap)
3. `RefinementProductionProcessor` (Refinement)
4. `PopulationConsumptionProcessor` (Pop verbraucht W/F + T-151 Pop-Soft-Cap)
5. `ShipSupplyProcessor` (T-012, Ship-Life-Support)
6. `RecyclingProcessor` (T-021, DEBRIS → random Output)

Alle Processors bekommen `?DateTimeImmutable $now` aus `gameState.getClock()->now()` über TickEngine. Siehe auch tick.md für globale Tick-Services (FleetArrival, Salvage, TelescopeDiscovery).

## Building-Uniqueness + Slot-Cap (T-171)

### Strikt-unique (max 1 pro Planet, Folge-Build = `BuildingAlreadyExistsException`)

| Building | Slot-Size | Grund |
|----------|-----------|-------|
| `HQ` (T-172) | 3 | Zentrale Verwaltung, multi sinnlos |
| `RESEARCH_LAB` | 3 | Forschungs-Quelle; Multi-Lab via T-025c über mehrere Planeten |
| `SHIPYARD` | 3 | Schiffbau-Gate, heavy industry |
| `PROBE_LAB` | 2 | Sondenbau-Gate |
| `RECYCLING_PLANT` | 2 | Strategic |
| `TELESCOPE` | 2 | Discovery-Source |
| `CONSTRUCTION_YARD` (T-064b) | 2 | Lokaler Bauzeit-Boost ×1.10/Lvl |
| `HOSPITAL` (T-070) | 1 | +20 Pop-Cap/Lvl; T-070b: Mangel-Tod-Reduction |
| `CULTURAL_CENTER` (T-070) | 1 | +2%/Lvl Mining + Refinement (capped +20%) |
| `TEMPLE` (T-070) | 1 | T-070b/T-122: Loyalty-Hook |

> T-182: UNIVERSITY entfernt — war Wort-Mix-Up mit RESEARCH_LAB. Lab ist die
> einzige Forschungs-Einrichtung; +RP-Output-Multi (T-070b) gestrichen.

### Non-unique (Multi-Instance erlaubt)

Mines (alle 7), Storage-Buildings (alle 6), Renewable-Producer (3), IRON_SMELTER —
alle Slot-Size 1.

### Slot-Cap pro PlanetSize

| Size | Slots |
|------|-------|
| TINY | 8 |
| SMALL | 12 |
| MEDIUM | 18 |
| LARGE | 28 |
| HUGE | 40 |

`Planet::getBuildingSlotsUsed()` summiert `getSlotSize()` aller Buildings (in-Bau + ready).
`BuildBuildingCommandService` validiert: `used + needed > cap` → `PlanetSlotsFullException`.
Spieler-Strategie: mit knappem Cap zwingt zu Spezialisierung (reine Production-Welt
ohne Mines, oder Mining-Hub ohne Strategic Buildings).

## Cancel + Refund (T-094b)

`CancelBuildCommand(planetId, buildingId)` bricht laufenden Build/Upgrade ab.

| Fall | Effekt |
|------|--------|
| Initial-Build (Level 1, in Bau) | Building wird gelöscht; 50% Resource-Refund (floor); 100% Pop released |
| Upgrade (Level N+1, in Bau) | Level → N, finishedAt=null (sofort wieder ready); 50% Upgrade-Cost-Refund; 100% Upgrade-Pop released |
| Bereits ready | `BuildingNotInProgressException` |

Refund-Rate ist hardcoded 50% — zukünftig via Logistics-Forschung anhebbar (T-094c-Folge).

## Bau-Queue (T-094 Foundation)

Pro Planet **parallele Build/Upgrade-Jobs** mit dynamischem Cap. `Planet::countActiveBuildJobs($now)`
zählt Buildings mit `!isReady($now)`. `BuildBuildingCommandService` und
`UpgradeBuildingCommandService` werfen `BuildQueueFullException` bei Überschreitung.

Bau-Queue (parallel) und Slot-Cap (total, je nach PlanetSize) sind komplementär.

**T-094c HQ-Slot-Bonus:** `Planet::getEffectiveBuildQueueCap($now) = min(8, 3 + HQ-Level/5)`.

| HQ-Level | Planet-Cap (HQ only) |
|----------|----------------------|
| 0–4 | 3 |
| 5–9 | 4 |
| 10–14 | 5 |
| 15–19 | 6 |
| 20–24 | 7 |
| 25+ | 8 (hard cap) |

**T-094d Logistics-Forschung:** `logistics_1` (3 Levels, +1 Slot pro Level).
Stack mit HQ-Bonus, gemeinsam Hard-Cap 8.

`BuildQueueCapCalculator::compute($planet, $player, $now)` ist Single-Source-of-Truth
— Build/Upgrade-Services + Demo-CLI rufen ihn. Beispiel: HQ L10 (+2) + logistics_1 L3
(+3) = 8 (cap reached).

## Bauzeit-Boost (T-064)

`ConstructionSpeedResearchConfig::getMultiplier(?Player): float` aggregiert
Forschungs-Multiplier (multiplikativ über Nodes UND Levels). Aktuelle Quellen:

| Node | Wirkung |
|------|---------|
| `construction_speed_1` (Tier-1, 3 Levels) | ×1.10 pro Level → L3 ≈ ×1.331 = -25% Duration |

Build- und Upgrade-Service multiplizieren mit `Planet::getEffectiveConstructionSpeedMultiplier($type)` (T-063 Planet-Type-Bonus). **Decision: nicht retroaktiv** — wirkt nur auf neu gestartete Bauten.

**Lokales Construction-Yard-Building (T-064b):** strikt-unique pro Planet, Slot-Size 2,
gated by `metallurgy` L1. `Planet::getConstructionHubSpeedMultiplier($now) = 1.10^level`
(kein Hub = 1.0). Stackt multiplikativ mit T-064-Forschung + T-063-Planet-Type-Bonus.
Build- und Upgrade-Service multiplizieren alle drei zusammen.

Player kann so eine **Industrie-Welt** spezialisieren: Construction-Yard L5 = ×1.61
nur auf diesem Planeten, kombiniert mit Forschung × Planet-Type-Bonus.

## Geplant
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
