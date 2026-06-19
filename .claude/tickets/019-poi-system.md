# T-019: POI-Basis + Subtypes

**Type:** Feature
**Status:** Done
**FX:** No
**MIG:** Yes (`Version20260619000008` — pois table)
**Depends on:** T-007
**Blocks:** T-020, T-021, T-022, T-023, T-075, T-076, T-085, T-086 (POI-Subtype-Tree)

## Description

POI-Foundation als Single-Table-Inheritance. Subtypes (Trümmerfeld, Nebel,
Raumstation, Asteroidenfeld, Wurmloch, Schwarzes-Loch, Pirat-Flotte) erweitern
die DiscriminatorMap durch eigene Klassen in den Folge-Tickets.

## AC

- [x] Domain `src/POI/` (Model, Repository, ValueObject)
- [x] `Poi` Entity mit STI:
  - `#[ORM\InheritanceType('SINGLE_TABLE')]`
  - `#[ORM\DiscriminatorColumn(name: 'type', type: 'string', length: 32)]`
  - `#[ORM\DiscriminatorMap]` mit allen 7 PoiType-Werten → `Poi::class` (Foundation-Stub).
    Folge-Tickets ersetzen Map-Einträge durch konkrete Subklassen.
- [x] `PoiType` Enum mit 7 Werten (DEBRIS_FIELD, NEBULA, STATION, UNKNOWN_FLEET,
  ASTEROID_FIELD, WORMHOLE, BLACK_HOLE)
- [x] `PoiId` ValueObject + `PoiIdType` Doctrine-Custom-Type
- [x] `PoiRepository` mit `findBySolarSystem`
- [x] `SolarSystem.pois` OneToMany Collection (mappedBy: solarSystem) + `addPoi`/`getPois`
- [x] Migration `Version20260619000008` (pois-Tabelle mit FK auf solar_systems)
- [x] doctrine.yaml: `poi_id` type registriert
- [x] Tests: 1 Unit (PoiType-Enum), 3 IT (Persistence + SolarSystem-Collection + Repository)
- [x] Suite grün (339/339, 724 assertions)

## Geklärte Fragen

1. **Inheritance-Pattern:** Single-Table-Inheritance — eine `pois`-Tabelle mit
   `type`-Discriminator. Folge-Tickets fügen Subklassen über DiscriminatorMap-
   Erweiterung hinzu. Rationale: type-spezifische Daten kommen erst mit Subtypes,
   STI ist schnell + standardkonform.
2. **Discovery-State:** Out-of-Scope. Kommt mit T-087 Fog-of-War als separate
   `PlayerSystemDiscovery`-Entity (Player × System × Level).
3. **Foundation-Scope:** Pure Foundation. Subtypes bleiben für T-020 etc.

## Out of Scope (Folge-Tickets)

- **Subtypes** mit eigener Logic (T-020 Asteroidenfeld → endliche Resources,
  T-021 Trümmerfeld → Salvage, T-022 Nebel → Stealth, T-023 Raumstation,
  T-075 Renegade-/Xenos-Outposts, T-076 Galaxy-Events, T-085 Wurmloch,
  T-086 Schwarzes-Loch)
- **Discovery / Fog-of-War** → T-087
- **POI-Spawn beim Galaxy-Init** → T-007-Erweiterung oder eigenes Folge-Ticket

## Files

**Neu:**
- `src/POI/ValueObject/{PoiId,PoiType}.php`
- `src/Common/Doctrine/Type/PoiIdType.php`
- `src/POI/Model/Poi.php`
- `src/POI/Repository/PoiRepository.php`
- `migrations/Version20260619000008.php`
- `tests/POI/ValueObject/PoiTypeTest.php`
- `tests/POI/Persistence/PoiPersistenceTest.php`

**Geändert:**
- `src/SolarSystem/Model/SolarSystem.php` (pois OneToMany + addPoi/getPois)
- `config/packages/doctrine.yaml` (poi_id type)
