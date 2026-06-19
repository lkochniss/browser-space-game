# Planets

## Aggregat-Struktur

```
Planet (Entity)
├── id (PlanetId, UUID)
├── player (ManyToOne, nullable) — owner
├── solarSystem (ManyToOne, nullable, T-007) — Container-System
├── type (PlanetType, default TERRAN, T-008)
├── size (PlanetSize, default MEDIUM, T-008)
├── population (Embedded VO, T-004)
├── buildings (OneToMany, T-002/T-006/T-009)
├── resources (OneToMany)
└── resourceDeposits (OneToMany)
```

## PlanetType (T-008)

7 Typen mit Wirkung auf Renewable-Verbrauch und Deposit-Generierung.

| Type | W-Verbrauch | F-Verbrauch | Base-Deposits |
|------|-------------|-------------|---------------|
| `TERRAN` | 1.0× | 1.0× | 500 IRON_ORE + 300 COAL |
| `BARREN` | 1.0× | 1.5× | 1500 IRON_ORE + 800 COPPER_ORE |
| `ICE` | 0.5× | 1.2× | 400 SILICON |
| `GAS_GIANT` | 1.0× | 1.0× | (keine festen Deposits) |
| `OCEAN` | 0.5× | 1.0× | 600 ALUMINUM_ORE |
| `VOLCANIC` | 1.3× | 1.2× | 500 URANIUM_ORE + 800 IRON_ORE |
| `DESERT` | 1.5× | 1.5× | 1000 SILICON + 300 TITANIUM_ORE |

`PlanetType::getConsumptionMultiplier(ResourceType)` → multipliziert in `PopulationConsumptionProcessor`.
`PlanetType::generateDeposits(PlanetSize)` → liefert finale Deposit-Map (mit Size-Multi).

## PlanetSize (T-008)

| Size | Deposit-Multiplier |
|------|--------------------|
| `TINY` | 0.5 |
| `SMALL` | 0.75 |
| `MEDIUM` | 1.0 |
| `LARGE` | 1.5 |
| `HUGE` | 2.0 |

`PlanetSize::getDepositMultiplier()` skaliert die Base-Deposits aus `PlanetType::getBaseDeposits()`.

## Generierung

- **Start-Planet:** hard `TERRAN + MEDIUM`. Onboarding-Predictability.
- **Andere Planeten in Galaxy:** random Type + random Size via `array_rand()`.
- **Deposit-Generation:** automatisch via `Type::generateDeposits(Size)` für non-Start-Planets. Start-Planet bekommt fix 1000 IRON_ORE Deposit.

## Pop-Cap

`Planet::BASE_POPULATION_CAP = 100`. Per-Planet-Type-Cap noch nicht implementiert (Folge: TINY=50, HUGE=200 etc.).

`recalculatePopulationCap(?$now)`:
- Auto-getriggert in `addBuilding(building, ?$now)`
- Explizit von `UpgradeBuildingCommandService` nach `setLevel++`
- T-062: Cap zählt nur ready Buildings (`isReady($now)`)

## Boni-System (T-063)

Type-spezifische Boni × Size-Faktor → Effective Multiplier.
Formel: `multiplier = max(0, 1 + typeBonus × sizeFactor)` (Construction: `max(0.1, …)`).
sizeFactor = `PlanetSize::getDepositMultiplier()`.

### Mining-Boni (Type → Resource)

| Type | Mining-Boni |
|------|-------------|
| `TERRAN` | neutral |
| `BARREN` | +0.5 IRON_ORE, +0.5 COPPER_ORE |
| `DESERT` | +1.0 SILICON, +0.5 TITANIUM_ORE |
| `ICE` | +0.5 SILICON |
| `VOLCANIC` | +1.0 URANIUM_ORE, +0.5 IRON_ORE |
| `OCEAN` | +0.5 ALUMINUM_ORE |
| `GAS_GIANT` | -1.0 alle (= multi 0, kein Mining) |

### Pop-Growth-Boni

| Type | Bonus |
|------|-------|
| `TERRAN` | +0.2 |
| `OCEAN` | +0.1 |
| `BARREN` | -0.1 |
| `VOLCANIC` | -0.1 |
| `DESERT` | -0.2 |
| `ICE` | -0.3 |
| `GAS_GIANT` | -0.5 |

### Construction-Speed-Boni

| Type | Bonus | Wirkt auf |
|------|-------|-----------|
| `BARREN` | +0.2 | Mines |

Andere Types: 0 (Tuning-Punkt).

### Refinement-Boni

Heute alle Types 0 (Tuning-Punkt für späteres Balancing).

### Effective-Multiplier-Helper auf Planet

- `getEffectiveMiningMultiplier(ResourceType)`
- `getEffectiveRefinementMultiplier(ResourceType)`
- `getEffectivePopGrowthMultiplier()`
- `getEffectiveConstructionSpeedMultiplier(BuildingType)`

## Strategic-Building-Helper

Pro Strategic-Building bietet Planet einen Level-Helper, der nur fertige
Buildings zählt (`isReady($now)`):

| Method | Building | Konsument |
|--------|----------|-----------|
| `getShipyardLevel($now)` / `hasShipyard($now)` | SHIPYARD (T-011) | Schiffsbau (ships.md) |
| `getProbeLabLevel($now)` / `hasProbeLab($now)` | PROBE_LAB (T-013) | Sondenbau (probes.md) |
| `getTelescopeLevel($now)` | TELESCOPE (T-018) | TelescopeDiscoveryService (discovery.md) |
| `getResearchLabLevel($now)` | RESEARCH_LAB (T-025) | StartResearchCommandService (research.md) |

## Files

- `src/Planet/Model/Planet.php` (Entity)
- `src/Planet/Model/Population.php` (Embedded VO)
- `src/Planet/ValueObject/PlanetId.php`
- `src/Planet/ValueObject/PlanetType.php` (T-008)
- `src/Planet/ValueObject/PlanetSize.php` (T-008)
- `src/Planet/Service/ClaimStartPlanetCommandService.php` (Galaxy + Start-Planet)
- `src/Planet/Service/GeneratePlanetCommandService.php` (heute redundant — Folge-TechDebt)
- `src/Planet/Repository/PlanetRepository.php`
