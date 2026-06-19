# Resources

## ResourceCategory

| Category | Bedeutung | Base-Cap |
|----------|-----------|----------|
| `FINITE` | Aus Vorkommen abgebaut. Deposit sinkt → ausgebeutet bei 0 | 100 |
| `RENEWABLE` | Ohne Vorkommen. Pop/Hub-abhängig (T-005/T-006). Verbrauch im Tick | 500 |
| `REFINED` | Veredelt aus Rohstoffen via Verarbeitungs-Building (T-003) | 100 |
| `DEBRIS` | Trümmer aus DebrisFields (T-021), via Recycling-Plant zu Random-Output | 50 |

`ResourceType::getCategory(): ResourceCategory` liefert die Kategorie pro Case.

## Resource-Types

| Enum | Wert | Kategorie | Producer | Base-Rate | Start-Amount | Deposit auf Start-Planet |
|------|------|-----------|----------|-----------|--------------|---------------------------|
| `IRON_ORE` | iron_ore | FINITE | `IRON_MINE` | 10.0 | 0 | 1000 |
| `COAL` | coal | FINITE | `COAL_MINE` | 15.0 | 0 | nein |
| `COPPER_ORE` | copper_ore | FINITE | `COPPER_MINE` | 8.0 | 0 | nein |
| `SILICON` | silicon | FINITE | `SILICON_MINE` | 6.0 | 0 | nein |
| `ALUMINUM_ORE` | aluminum_ore | FINITE | `ALUMINUM_MINE` | 8.0 | 0 | nein |
| `TITANIUM_ORE` | titanium_ore | FINITE | `TITANIUM_MINE` | 4.0 | 0 | nein |
| `URANIUM_ORE` | uranium_ore | FINITE | `URANIUM_MINE` | 2.0 | 0 | nein |
| `WATER` | water | RENEWABLE | — (T-005/T-006) | 5.0 | 100 | nein |
| `FOOD` | food | RENEWABLE | — | 3.0 | 100 | nein |
| `OXYGEN` | oxygen | RENEWABLE | — | 0.0 | 100 | nein |
| `IRON_BAR` | iron_bar | REFINED | `IRON_SMELTER` (2 Iron + 1 Coal → 1 Bar) | linear × Level | — | nein, lazy auto-create |
| `DEBRIS_LOW` | debris_low | DEBRIS | Salvage von DebrisField (T-021) | — | — | — |
| `DEBRIS_MEDIUM` | debris_medium | DEBRIS | Salvage von DebrisField (T-021) | — | — | — |
| `DEBRIS_HIGH` | debris_high | DEBRIS | Salvage von DebrisField (T-021) | — | — | — |

## Mining-Mechanik (T-002)

- Endliche Rohstoffe: pro Erz eine dedizierte Mine
- `ResourceProductionProcessor` iteriert Deposits + zugeordnete Mining-Buildings
- Output `level × baseRate` pro Tick, geclamped am Deposit-Bestand

## Refinement-Mechanik (T-003)

- `RefinementConfig` mit Recipes (output, outputAmount, inputs, building)
- `RefinementProductionProcessor` iteriert Buildings, prüft Recipe-Mapping
- Output `level × outputAmount` pro Tick, limited durch Input-Verfügbarkeit
- Inputs anteilig debitiert pro produzierter Output-Einheit
- Output-Resource via `Planet::ensureResource()` lazy auto-created
- Multiple Smelter stacken

### Aktive Recipes

| Output | Building | Inputs (pro Output-Einheit) |
|--------|----------|------------------------------|
| `IRON_BAR` | `IRON_SMELTER` | 2 IRON_ORE + 1 COAL |

## Recycling-Mechanik (T-021)

`RecyclingProcessor` (TickProcessor) konsumiert pro Recycling-Plant-Level
2 DEBRIS-Items pro Tick (Reihenfolge LOW → MEDIUM → HIGH). Pro Item würfelt
`RecyclingTable × Randomizer` einen Output:

| Tier | Tabelle (gewichtet) |
|------|---------------------|
| `DEBRIS_LOW` | 70% IRON_ORE (5-15) / 20% COAL (3-10) / 10% nichts |
| `DEBRIS_MEDIUM` | 50% IRON_BAR (3-8) / 30% SILICON (5-15) / 15% TITANIUM_ORE (2-5) / 5% nichts |
| `DEBRIS_HIGH` | 40% TITANIUM_ORE (5-12) / 30% URANIUM_ORE (3-8) / 20% IRON_BAR (8-20) / 10% ALUMINUM_ORE (10-25) |

Tunable. Salvage von DebrisField (T-021) füttert die Cargo-Pipeline analog
AsteroidField, dank gemeinsamem `SalvageableField`-Interface.

## Renewable-Production (T-097a)

W/F/O sind RENEWABLE und werden via dedizierte Tier-0-Producer-Buildings
hergestellt (statt Mining-Deposits). `RenewableProductionProcessor` iteriert:

| Building | Resource | Base-Rate (per Tick × Level) |
|----------|----------|------------------------------|
| `WATER_RECLAIMER` | WATER | +10 |
| `AGRI_DOME` | FOOD | +6 |
| `ATMOSPHERIC_PROCESSOR` | OXYGEN | +6 |

Storage-Cap-aware (clamp am Planet-Storage-Cap). Tier-0 (kein Research-Lock —
sind Lebenserhaltung). Verbrauchs-Referenz: 50 Pop × 0.1 = 5 W/F per Tick.

## Tick-Reihenfolge (relevant für Resources)

1. `ConstructionCompletionProcessor` (T-062, recalc Pop-Cap)
2. `ResourceProductionProcessor` (Mining + T-151 Stockpile-SoftCap)
3. `RefinementProductionProcessor` (Refinement, nutzt Mining-Output)
4. `RenewableProductionProcessor` (W/F/O — T-097a, frische Resources für Pop-Tick)
5. `PopulationConsumptionProcessor` (Pop verbraucht W/F + T-151 Pop-Soft-Cap)
5. `ShipSupplyProcessor` (T-012, Ship-Life-Support)
6. `RecyclingProcessor` (T-021, DEBRIS → random Output)

## Storage (T-061)

`Planet::getStorageCapacity(ResourceType)` ist live-computed:

```
cap = ResourceCategory.baseCap + Σ(building.type.getStorageContribution(resource) × building.level)
```

| Category | Base-Cap | Beispiel-Beitrag |
|----------|----------|------------------|
| `RENEWABLE` | 500 | HUB +200/level, WATER_TANK +2000/level |
| `FINITE` | 100 | Mining-Mine +100/level eigene Resource, IRON_STORAGE +1000/level |
| `REFINED` | 100 | IRON_SMELTER +100/level, IRON_BAR_STORAGE +1000/level |
| `DEBRIS` | 50 | (kein dediziertes Storage-Building, T-021 ohne Lager-Erweiterung) |

**Cap-Stop** (T-061): Mining + Refinement Production pausieren bei vollem Lager. Refinement debitiert Inputs nur anteilig zur tatsächlichen Output-Menge. Kein Verfall.

Heute relevante Storage-Buildings: IRON_STORAGE, COAL_STORAGE, IRON_BAR_STORAGE, WATER_TANK, FOOD_SILO, OXYGEN_STORAGE. 5 weitere (Copper/Si/Al/Ti/U) folgen mit POIs T-019/T-020.

## Files

- `src/Resource/ValueObject/ResourceType.php` (Enum + getCategory)
- `src/Resource/ValueObject/ResourceCategory.php` (Enum)
- `src/Resource/ValueObject/RefinementRecipe.php` (VO)
- `src/Resource/Service/ResourceProductionConfig.php` (Mining-Werte)
- `src/Resource/Service/RefinementConfig.php` (Recipes)
- `src/Resource/Service/PopulationConsumptionConfig.php` (T-005)
- `src/Resource/Model/Resource.php` (`generateEmptyResource`, `generateWithAmount`)
- `src/Resource/Model/ResourceDeposit.php`
- `src/Building/Service/ResourceBuildingMap.php` (Mine ↔ Resource)
- `src/Tick/Processor/ResourceProductionProcessor.php`
- `src/Tick/Processor/RefinementProductionProcessor.php`
- `src/Tick/Processor/PopulationConsumptionProcessor.php`
- `src/Tick/Processor/RecyclingProcessor.php` (T-021)
- `src/Building/Service/RecyclingTable.php` (T-021 Wahrscheinlichkeits-Tabelle)
- `src/Common/Service/Randomizer.php` (T-021, testbar via Stub)
- `src/Planet/Service/ClaimStartPlanetCommandService.php` (Init beim Claim)
- `src/Planet/Model/Planet.php` (`ensureResource`-Helper)
