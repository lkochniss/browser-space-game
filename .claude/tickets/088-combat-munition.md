# T-088 Combat-Munition (Verbrauchs-Resources im Battle)

**Type:** Feature
**Epic:** Combat & Battle
**Domain:** Resource
**Blocked By:** T-067, T-103, T-102, T-178
**Status:** Ready
**Effort:** M (~4h)
**Depends on:** T-067 (Done), T-103 (Ready), T-102 (Ready), T-178 (Blocked-but-Ready-soon — Ship-Cargo-Universal)
**Blocks:** —

## Beschreibung

Combat-Verbrauchs-Resources. Schiffe ohne Munition haben drastisch reduzierten
Damage-Output + Forced-Retreat. Schafft Resource-Pressure auf Combat-Aktivität.

## Resolved Decisions

- **Q1 Munition-Typ pro Klasse (differenziert):**
  | ShipClass | Munition |
  |-----------|----------|
  | Frigate, Destroyer | `BALLISTIC_AMMO` |
  | Cruiser, Battleship | `WARHEAD` |
  | Carrier + Late-Tier | `PLASMA_CHARGE` (T-115 Tier-3-gated) |
  | Defense-Buildings | `POINT_DEFENSE_MAG` (T-068 Defense-Stats-Konsum) |
- **Q2 Verbrauchs-Rate (Damage-skaliert):** `munition_consumed = ceil(damage_dealt / 100)` pro Round.
  Captain-Boost steigert Damage → höhere Munition-Cost. Pacing wird automatisch
  durch Damage-Multipler.
- **Q3 Munition-Storage:** Ship-Cargo (T-178). Munition belegt Volume per
  `ResourceVolumeConfig`-Multi. Lade über T-015 LoadCargo wie Resources.
- **Q4 Out-of-Ammo:** Damage × 0.3 + Forced-Retreat. Bei Out-of-Ammo zieht
  sich Schiff ab nächster Round aus Battle zurück (markiert als RETREATED,
  keine weitere HP-Reduktion + kein Damage-Output). T-103 Battle-Engine
  honoriert RETREATED-Status.
- **Q5 Re-Supply (4 Pfade):**
  1. Eigener Planet — LoadCargo wie heute (T-015)
  2. Eigene Station — LoadCargo via T-015b
  3. Transport-Ship (Logistics-Loop) — Transport bringt Munition zum Front-Ship
  4. **Trade-Hub** (T-112 Statische Handelsposten) — Cash-for-Munition kaufen
  5. **Allianz-Stützpunkt** (T-093) — wenn Allianz Munition freigegeben hat,
     LoadCargo möglich

## Acceptance Criteria

### Resource-Types

- [ ] `ResourceType::BALLISTIC_AMMO`, `WARHEAD`, `PLASMA_CHARGE`,
      `POINT_DEFENSE_MAG` als REFINED-Kategorie
- [ ] `ResourceCategory::REFINED` Mapping erweitert
- [ ] Volume-Multi in `ResourceVolumeConfig` (Vorschlag):
      BALLISTIC_AMMO 0.5, WARHEAD 1.0, PLASMA_CHARGE 0.3, POINT_DEFENSE_MAG 0.5

### Refinement-Recipes

- [ ] `BALLISTIC_AMMO` = 2 STEEL + 1 COPPER_BAR → 1 (AMMO_FACTORY)
- [ ] `WARHEAD` = 3 STEEL + 1 TRITIUM_ORE + 1 CHIP → 1 (WARHEAD_PLANT)
- [ ] `PLASMA_CHARGE` = 1 PLASMA_CELL (T-115) + 2 COMPOSITE → 1 (PLASMA_FORGE)
- [ ] `POINT_DEFENSE_MAG` = 1 STEEL + 1 CHIP → 2 (DEFENSE_AMMO_PLANT)

### Manufacturing-Buildings

- [ ] `BuildingType::AMMO_FACTORY`, `WARHEAD_PLANT`, `PLASMA_FORGE`,
      `DEFENSE_AMMO_PLANT` (alle non-unique, Slot-Size 1)
- [ ] BuildingCostConfig + BuildingDurationConfig + BuildingUnlockConfig
- [ ] RefinementConfig erweitert um die 4 neuen Recipes
- [ ] Power-Consumption per T-065-Schema (mid: 8/Lvl)

### Battle-Engine-Integration (T-103 Hook)

- [ ] `Ship::getMunitionType(): ResourceType` — Mapping per ShipClass
- [ ] `BattleResolver`:
      - Pro Round + Schiff: `munition_consumed = ceil(damage_dealt / 100)`
      - Aus `ship.cargo.getResource(munitionType)` debit
      - Bei 0 Munition → `ship.outOfAmmo = true`, Damage × 0.3
      - Bei Out-of-Ammo am Round-Ende → `ship.status = RETREATED`
- [ ] T-068 Defense-Buildings konsumieren `POINT_DEFENSE_MAG` analog
      (aus Planet-Storage)

### Re-Supply

- [ ] T-015 `LoadCargoCommandService` akzeptiert Munition-ResourceTypes (kein
      neues Command nötig — Cargo-Loading deckt das ab)
- [ ] Trade-Hub-Purchase (T-112 Folge) — out-of-scope für T-088, aber Hook benannt
- [ ] Allianz-Munition-Freigabe (T-093 Folge) — out-of-scope, Hook benannt

### Tests

- [ ] `MunitionConsumptionTest` (IT): Damage-skaliert konsumiert correct
- [ ] `OutOfAmmoBehaviorTest`: Damage ×0.3 + RETREATED nach Out-of-Ammo
- [ ] `MunitionTypeMappingTest`: ShipClass → MunitionType correct
- [ ] `RefinementConfigTest`: 4 neue Recipes registered
- [ ] `LoadCargoMunitionTest`: Munition in Ship-Cargo lädt korrekt

### Docs

- [ ] `combat.md` Munition-Sektion (T-088)
- [ ] `resources.md` Munition-Types + Volume-Multi-Tabelle
- [ ] `buildings.md` 4 neue Manufacturing-Buildings
- [ ] `decisions.md` Eintrag T-088

## Out of Scope

- Auction-House Munition-Markt (T-111 Folge)
- Allianz-Munition-Freigabe-Mechanik (T-093 Folge)
- Trade-Hub-Purchase Detail (T-112 Folge)
- Loot-Drop-Munition (T-080 Folge)

## Fixtures Needed

Yes — `MunitionFixture` mit Test-Schiff (Cargo-loaded) + Manufacturing-Building +
Resource-Stockpiles.

## Notes

- T-088 zentralisiert Combat-Resource-Pressure: Industrie-Spieler vs.
  Combat-Spieler = symbiotisches Eco-System
- Forced-Retreat verhindert "ride to zero HP" — Schiffe können sich
  taktisch zurückziehen wenn Munition leer
- T-115 Tier-3-Plasma-Cell als PLASMA_CHARGE-Input macht Late-Tier-Combat
  abhängig von Tier-3-Industrie

### Refinement Tokens (estimate)
- Input: ~7k
- Output: ~3k
