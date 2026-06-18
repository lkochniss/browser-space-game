# T-019: POI-Basis + Subtypes

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** No
**Depends on:** T-007

## Description

`docs/POI.md`: POIs leben im Sonnensystem, von Sonden/Flotten/Teleskop entdeckbar. Subtypes: Trümmerfeld, Nebel, Raumstation, unbekannte Flotten, Asteroidenfeld, Wurmloch, Schwarzes Loch.

## AC

- [ ] Neue Domain `src/POI/`
- [ ] `Poi` Basisklasse oder Interface mit `PoiId`, `PoiType`, `SolarSystem`, `discoveredBy: Set<Player>`
- [ ] `PoiType` enum mit allen Subtypes (DEBRIS_FIELD, NEBULA, STATION, UNKNOWN_FLEET, ASTEROID_FIELD, WORMHOLE, BLACK_HOLE)
- [ ] `SolarSystem` hält `PoiCollection`
- [ ] Konkrete Subtypes per Composition oder Vererbung (in eigenen Tickets implementiert)

## Affected

- Neu: `src/POI/Model/Poi.php`, `ValueObject/PoiId.php`, `PoiType.php`, `Model/PoiCollection.php`
- `src/SolarSystem/Model/SolarSystem.php`

## Open Questions

1. Vererbung (`AsteroidField extends Poi`) vs. Komposition (`Poi.payload`)? Empfehlung: Vererbung pro Type, da Verhalten stark unterschiedlich.
2. Discovery-State pro Player oder global per POI? (Doc impliziert pro Player.)
