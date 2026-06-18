# T-017: Flotte + Bewegung

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** No
**Depends on:** T-007, T-012

## Description

`docs/Flotte.md`: Schiffsverband. Langsamstes Schiff = Flotten-Speed. Verbraucht Treibstoff, Nahrung, Wasser im Flug. Anker im Orbit, in Nebel oder bei Raumstation = Vorräte auffüllen. Trifft auf Gegner → Raumschlacht (T-024).

## AC

- [ ] `Fleet` Entity (id, owner, ships, location, target, status)
- [ ] `FleetLocation` VO (Planet | Station | Nebula | InTransit{from, to, progress})
- [ ] `MoveFleetCommand` (Fleet, target)
- [ ] Tick-Processor: bewegt InTransit-Fleets, verbraucht Supplies (W/F + Treibstoff)
- [ ] Ankern triggert Resupply
- [ ] Ohne Treibstoff → Stillstand (kein Tod); ohne W/F/O → Ship-Tod (T-012)

## Affected

- Neu: `src/Fleet/Model/Fleet.php`, `ValueObject/FleetId.php`, `FleetLocation.php`
- Neu: `src/Fleet/Command/MoveFleetCommand.php` + Handler
- Neu: `src/Tick/Processor/FleetMovementProcessor.php`

## Open Questions

1. Ein Schiff immer in genau einer Flotte (auch wenn alleine)? Empfehlung: ja, vereinfacht Modell.
2. Treibstoff = eigener ResourceType oder pro Antriebs-Tech (T-026) andere "Fuel"-Variante?
3. Inter-System-Sprung als atomarer Step oder als Travel-Phase?
