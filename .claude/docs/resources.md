# Resources

## ResourceCategory

| Category | Bedeutung |
|----------|-----------|
| `FINITE` | Aus Vorkommen abgebaut. Deposit sinkt → ausgebeutet bei 0 |
| `RENEWABLE` | Ohne Vorkommen. Pop/Hub-abhängig (T-005/T-006). Verbrauch im Tick |
| `REFINED` | Veredelt aus Rohstoffen via Verarbeitungs-Building (T-003) |

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

## Tick-Reihenfolge

1. `ResourceProductionProcessor` (Mining)
2. `RefinementProductionProcessor` (Refinement, nutzt Mining-Output)
3. `PopulationConsumptionProcessor` (Pop verbraucht Wasser/Nahrung)

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
- `src/Planet/Service/ClaimStartPlanetCommandService.php` (Init beim Claim)
- `src/Planet/Model/Planet.php` (`ensureResource`-Helper)
