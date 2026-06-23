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
| `PLASTIC_RESIN` (T-067) | plastic_resin | FINITE | `PLASTIC_RESIN_MINE` | 5.0 | 0 | nein |
| `TRITIUM_ORE` (T-067) | tritium_ore | FINITE | `TRITIUM_MINE` | 3.0 | 0 | nein |
| `WATER` | water | RENEWABLE | — (T-005/T-006) | 5.0 | 100 | nein |
| `FOOD` | food | RENEWABLE | — | 3.0 | 100 | nein |
| `OXYGEN` | oxygen | RENEWABLE | — | 0.0 | 100 | nein |
| `IRON_BAR` | iron_bar | REFINED | `IRON_SMELTER` (2 Iron + 1 Coal → 1 Bar) | linear × Level | — | nein, lazy auto-create |
| `ALUMINUM_BAR` (T-067) | aluminum_bar | REFINED | `ALUMINUM_REFINERY` (2 Al-Ore + 1 Coal → 1) | linear × Level | — | nein |
| `COPPER_BAR` (T-067) | copper_bar | REFINED | `COPPER_REFINERY` (2 Cu-Ore + 1 Coal → 1) | linear × Level | — | nein |
| `TITANIUM_BAR` (T-067) | titanium_bar | REFINED | `TITANIUM_REFINERY` (2 Ti-Ore + 1 Coal → 1) | linear × Level | — | nein |
| `STEEL` (T-067) | steel | REFINED | `STEEL_SMELTER` (2 Iron-Bar + 1 Coal → 1) | linear × Level | — | nein |
| `CHIP` (T-067) | chip | REFINED | `CHIP_FAB` (2 Cu-Bar + 1 Silicon → 1) | linear × Level | — | nein |
| `COMPOSITE` (T-067) | composite | REFINED | `COMPOSITE_PLANT` (2 Al-Bar + 2 Plastic-Resin → 1) | linear × Level | — | nein |
| `HULL_PLATE` (T-067) | hull_plate | REFINED | `HULL_FOUNDRY` (4 Steel + 2 Composite → 1) | linear × Level | — | nein |
| `SHIELD_MODULE` (T-067) | shield_module | REFINED | `SHIELD_ASSEMBLER` (3 Chip + 1 Tritium → 1) | linear × Level | — | nein |
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
| `ALUMINUM_BAR` (T-067) | `ALUMINUM_REFINERY` | 2 ALUMINUM_ORE + 1 COAL |
| `COPPER_BAR` (T-067) | `COPPER_REFINERY` | 2 COPPER_ORE + 1 COAL |
| `TITANIUM_BAR` (T-067) | `TITANIUM_REFINERY` | 2 TITANIUM_ORE + 1 COAL |
| `STEEL` (T-067) | `STEEL_SMELTER` | 2 IRON_BAR + 1 COAL |
| `CHIP` (T-067) | `CHIP_FAB` | 2 COPPER_BAR + 1 SILICON |
| `COMPOSITE` (T-067) | `COMPOSITE_PLANT` | 2 ALUMINUM_BAR + 2 PLASTIC_RESIN |
| `HULL_PLATE` (T-067) | `HULL_FOUNDRY` | 4 STEEL + 2 COMPOSITE |
| `SHIELD_MODULE` (T-067) | `SHIELD_ASSEMBLER` | 3 CHIP + 1 TRITIUM_ORE |

### Snapshot-Single-Step-pro-Tick (T-067 Q3)

`RefinementProductionProcessor` snapshotted alle REFINED-Resource-Amounts vor
dem Tick. REFINED-Inputs werden gegen Snapshot geprüft (verhindert Cascade —
Iron-Bar aus diesem Tick kann nicht im selben Tick zu Steel werden). FINITE-
Inputs (Erze, Coal etc.) gehen weiter live, weil Mining-Output bereits VOR
Refinement im `ResourceProductionProcessor` produziert wurde.

Folge: Volle Tier-1→Tier-2→Tier-3-Cascade (Iron-Ore → Iron-Bar → Steel →
Hull-Plate) braucht 3 Ticks (1 Schritt pro Tick).

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

## Volume-System (T-180 Foundation)

Foundation für Generic-Storage (T-177ff): jede ResourceType + Pop hat ein
**Volume in m³** pro Einheit. Macht Storage planet-übergreifend in einer Einheit
berechenbar statt pro-Resource separat.

`ResourceVolumeConfig::getMultiForResource(ResourceType): float` liefert m³/Unit;
`ResourceVolumeConfig::getPopMulti(): float` = 10.0 (Pop-Lebensraum-Volume).

Auszug Multiplier-Tabelle (m³/Unit):

| Resource | m³/Unit |
|----------|---------|
| WATER (Reference) | 1.0 |
| FOOD | 1.2 |
| OXYGEN | 0.3 (komprimiert) |
| IRON_ORE / COPPER_ORE / ALUMINUM_ORE / TITANIUM_ORE | 2.0 |
| COAL / SILICON | 1.8 |
| URANIUM_ORE | 2.5 (Bleicontainer) |
| PLASTIC_RESIN (T-067) | 1.5 |
| TRITIUM_ORE (T-067) | 2.0 |
| IRON_BAR | 1.5 (kompakter als Erz) |
| ALUMINUM_BAR (T-067) | 0.8 |
| COPPER_BAR (T-067) | 1.4 |
| TITANIUM_BAR (T-067) | 1.0 |
| STEEL (T-067) | 1.0 |
| CHIP (T-067) | 0.3 (klein, hochwertig) |
| COMPOSITE (T-067) | 1.2 |
| HULL_PLATE (T-067) | 2.5 (großflächig) |
| SHIELD_MODULE (T-067) | 0.8 |
| DEBRIS_* | 1.0 |

Pop-Multi: **10.0 m³** pro Person.

Fail-fast bei neuen ResourceTypes ohne Multi via `UnknownResourceVolumeException`.

T-177/T-178/T-179 nutzen diese Werte für generic Volume-Buckets pro Planet/Station.

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
