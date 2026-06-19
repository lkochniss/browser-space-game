# T-021: Trümmerfeld + Trümmer + Recycling-Anlage

**Type:** Feature
**Status:** Open
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

## AC

- [ ] `DebrisField` POI-Subtype (extends Poi, STI)
- [ ] `Poi`-DiscriminatorMap aktualisieren: `'debris_field' => DebrisField::class`
- [ ] `Debris` Entity mit `DebrisQuality` enum (z.B. `LOW`, `MEDIUM`, `HIGH`, `RARE`)
- [ ] DebrisField wird via Hook erzeugt:
  - **Aus Battle (T-103)**: BattleResolver erzeugt DebrisField mit Größe/Qualität
    abhängig von Verlusten
  - **Aus Schiff-Death (T-012 erweitert)**: ShipSupplyProcessor.killShip() erzeugt
    kleines DebrisField im aktuellen System
- [ ] `BuildingType::RECYCLING_PLANT`
- [ ] `RecyclingProcessor` (TickProcessor): pro Tick verbraucht Trümmer auf Planet,
  würfelt Erzeugnis nach Tier-Wahrscheinlichkeit
- [ ] T-016 Salvage-Action für DebrisField:
  - `StartSalvageCommandService` Validation erweitern: DebrisField als gültiger
    `InvalidSalvageTargetException`-Fallback raus, akzeptieren wenn AsteroidField
    ODER DebrisField
  - `SalvageProcessor` polymorphisch: extract gegen `AsteroidField` ODER
    `DebrisField` (gemeinsame Extract-API in eigenem Interface oder Match-Branch)
  - DebrisField-Cleanup bei isEmpty analog AsteroidField (em->remove)

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
