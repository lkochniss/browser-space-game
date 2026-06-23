# T-015b: Station-Cargo-Transfer (Erweiterung zu T-015)

**Type:** Feature
**Epic:** Ships & Fleet
**Domain:** Ship
**Blocked By:** T-015, T-023
**Status:** Done (Foundation: Resources only; Pop-Transfer + Owner-Restriction defer)
**Effort:** S
**Depends on:** T-015 (Cargo-Foundation), T-023 (SpaceStation)
**Blocks:** T-023b (braucht Cargo-Transfer für Maintenance-Refill)

## Beschreibung

T-015 LoadCargo / UnloadCargo unterstützt aktuell nur Planeten als Source/Target.
Mit T-023 SpaceStation existiert nun ein zweiter Storage-Typ. Schiff muss
zwischen Station-Storage und Schiff-Cargo umladen können.

## Acceptance Criteria

- [ ] TBD: `LoadCargoCommandService` erweitern: wenn Schiff am Station-POI dockt
  (statt am Planet), aus Station-Storage laden
- [ ] TBD: `UnloadCargoCommandService` erweitern: wenn Schiff am Station-POI dockt,
  ins Station-Storage entladen
- [ ] TBD: Schiff-Dock-Mechanik: ship.planet = null, ship.station = ?SpaceStation
  (nullable). Heißt: Schiff kann an Planet ODER Station docked sein
- [ ] TBD: Migration: ships.station_id (FK auf pois, nullable, mit Discriminator-
  Filter `type = 'station'` als Validation)
- [ ] TBD: Foundation Station-Storage = embedded `CargoManifest` (existiert schon)
  → reuse CargoManifest-API

## Open Questions

- Owner-Restriction: nur Owner darf Station-Storage nutzen? Oder allianz-shared?
- Multiple-Ships an einer Station: Konkurrenz auf Storage-Capacity?
- Refresh / Tick-Hooks notwendig?

## Notes

- Voraussetzung für T-023b: Maintenance-Refill setzt voraus dass Player Resources
  zur Station transportieren kann
- Voraussetzung auch für T-093 Allianz-Stationen: dort wird das gleiche Pattern
  benutzt für Allianz-Member-Beiträge
- Pattern aus T-015 reuse: HardReject bei CapacityExceeded analog
