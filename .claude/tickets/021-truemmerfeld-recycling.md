# T-021: Trümmerfeld + Trümmer + Recycling-Anlage

**Type:** Feature
**Status:** Done
**FX:** No
**MIG:** No
**Depends on:** T-019 (POI-Foundation done), T-103 (Battle-Engine — moderner Folge-
Ticket; war ursprünglich T-024 Battle-Resolution-Stub)

## Description

Trümmerfeld bleibt nach Raumschlacht zurück. Bergungsschiff (T-016) holt Trümmer
raus. Trümmer haben Qualitäts-Tier. Recycling-Anlage konvertiert Trümmer → zufällige
Erzeugnisse (Wahrscheinlichkeit pro Tier).

**Stand nach T-019:** POI-Foundation existiert mit STI. DiscriminatorMap-Eintrag
`'debris_field' => Poi::class` ist Stub und wird in T-021 zu `DebrisField::class`
umgehängt.

**Stand nach T-012 ShipSupplyProcessor:** Schiff-Death erzeugt aktuell kein
Trümmerfeld (T-021 Out-of-Scope-Marker in T-012 platziert). T-021 erweitert das.

## Decisions (2026-06-19)

1. **Persistence:** aggregierter JSON-Counter (`debris_contents`) analog
   AsteroidField. Keine Sub-Entities.
2. **Tiers:** 3 (LOW / MEDIUM / HIGH); RARE später wenn nötig.
3. **Cargo-Repräsentation:** 3 neue ResourceTypes `DEBRIS_LOW/MEDIUM/HIGH` mit
   neuer `ResourceCategory::DEBRIS` (baseCap=50). Cargo-Flow via existierende
   LoadCargo/UnloadCargo unverändert.
4. **DebrisField-Spawn:**
   - WorldFixture: 1 deterministischer DebrisField in Sol-Gamma (8 LOW + 4 MED + 1 HIGH)
   - Demo-Galaxy-Garantie erweitert um DebrisField im Heimat-System
   - ShipSupplyProcessor.killShip() spawnt Mini-DebrisField (2 DEBRIS_LOW)
   - Battle-Spawn (T-103): out of scope, kommt mit T-103
5. **Recycling-Tabelle:** s. `RecyclingTable.php` — 70/20/10 LOW, 50/30/15/5 MED,
   40/30/20/10 HIGH. Tunable.

## AC

- [x] `DebrisField` POI-Subtype (extends Poi, STI, implements `SalvageableField`)
- [x] `Poi`-DiscriminatorMap aktualisiert: `'debris_field' => DebrisField::class`
- [x] DEBRIS_LOW/MEDIUM/HIGH ResourceTypes + `ResourceCategory::DEBRIS`
- [x] `SalvageableField` Interface — gemeinsame Extract-API für Asteroid + Debris;
      `SalvageProcessor` + `StartSalvageCommandService` polymorphisch
- [x] `BuildingType::RECYCLING_PLANT` + Cost (250 IRON_ORE / 100 COPPER / 80 SI / 10 pop)
      + Duration (30min)
- [x] `RecyclingProcessor` (TickProcessor) — konsumiert `level × 2` Debris-Items
      pro Tick, würfelt Output via `RecyclingTable`
- [x] `Randomizer` Service — testbar via Stub
- [x] `ShipSupplyProcessor.killShip()` spawnt DebrisField im Heim-System
- [x] WorldFixture erweitert (1 DebrisField in Sol-Gamma)
- [x] Demo-Galaxy-Garantie erweitert (DebrisField im Heimat-System)
- [x] Demo-Salvage-Menu polymorph (Asteroid + Debris)
- [x] Tests: 6 DebrisField-Unit, 6 Recycling-Unit, 2 ShipSupply-Debris-Spawn
      (IT), 2 Fixture-Debris-Asserts. 14 neu, 441/441 Suite grün.

## Affected

- Neu: `src/POI/Model/DebrisField.php` (extends Poi)
- Neu: `src/Debris/Model/Debris.php`, `ValueObject/DebrisQuality.php`
- `src/POI/Model/Poi.php` (DiscriminatorMap Update)
- `src/Building/ValueObject/BuildingType.php` (+ RECYCLING_PLANT)
- Neu: `src/Tick/Processor/RecyclingProcessor.php`
- `src/Tick/Processor/ShipSupplyProcessor.php` (DebrisField-Spawn bei killShip)
- Migration für DebrisField-spezifische Spalten (debris_quality_distribution JSON?)

## Open Questions

1. Wahrscheinlichkeits-Tabelle pro Tier — sinnvolle Defaults?
2. Trümmer als generische Cargo-Items oder eigene Entity? — Empfehlung: eigene
   Entity wegen Quality-Tier
3. Tier-Anzahl: 3 oder 4?
4. **DebrisField-Persistence**: einzelne Trümmer (Sub-Entities) oder aggregierte
   Zähler pro Quality-Tier (analog AsteroidField.contents-JSON)?
