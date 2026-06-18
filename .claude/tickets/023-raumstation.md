# T-023: Raumstation pro System

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** No
**Depends on:** T-007, T-011

## Description

`docs/Raumstation.md`: Eine Station pro Sonnensystem. Erlaubt Resource-/Erzeugnis-Verteilung zwischen Planeten, Sammelpunkt für Flotten, Handelspunkt mit fremden Völkern.

## AC

- [ ] `SpaceStation` Entity mit eigenem Lager + Docking-Slots
- [ ] Constraint: max 1 Station pro `SolarSystem` (per Player oder global — entscheiden)
- [ ] `BuildSpaceStationCommand` — verbraucht Erzeugnisse + Pop, braucht Raumwerft im System
- [ ] Station kann als `FleetLocation` dienen (Resupply)
- [ ] Resource-Lager auf Station — Transferziel (T-015)

## Affected

- Neu: `src/Station/Model/SpaceStation.php`, `ValueObject/StationId.php`
- `src/SolarSystem/Model/SolarSystem.php` (max-1 station)
- Neu: `src/Station/Command/BuildSpaceStationCommand.php` + Handler

## Open Questions

1. Station = pro Player oder pro System (= ein Player blockiert das System)?
2. Handel mit fremden Völkern jetzt schon (impliziert Multi-Faction) oder als eigenes späteres Ticket?
