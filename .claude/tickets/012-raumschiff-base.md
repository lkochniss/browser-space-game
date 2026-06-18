# T-012: Raumschiff-Basis + Life-Support

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** No
**Depends on:** T-001, T-011

## Description

`docs/Raumschiff.md`: Schiffe verbrauchen durchgehend W/F/O. Andocken am Planeten/Station = automatische Versorgung. Unterwegs eigener Lagerraum nötig — sonst Tod + Trümmerfeld. Build in Raumwerft. Aktuell keine Schiffe im Code.

## AC

- [ ] Neue Domain `src/Ship/`
- [ ] `Ship` Entity (id, type, dockedAt: Planet|Station|null, supplyStorage)
- [ ] `ShipType` enum (initial: Stub für spätere Subtypes — siehe T-013ff)
- [ ] `BuildShipCommand` + Handler — verbraucht Erzeugnisse + freie Pop, prüft Raumwerft
- [ ] Tick-Processor: Schiffe in Flotten verbrauchen W/F/O; angedockte werden aus Planet/Station versorgt
- [ ] Ohne W/F/O → Schiff stirbt, erzeugt Trümmerfeld (T-021)

## Affected

- Neu: `src/Ship/Model/Ship.php`, `ShipCollection.php`, `ValueObject/ShipId.php`, `ShipType.php`
- Neu: `src/Ship/Command/BuildShipCommand.php` + Handler
- Neu: `src/Tick/Processor/ShipSupplyProcessor.php`

## Open Questions

1. Pop pro Schiff fix oder pro Type? Doc sagt: bei Zerstörung stirbt Pop und kann auf Planet wieder produziert werden — also Pop ist gebunden, nicht "verbraucht". Bei Schiffsbau also pop assignen, nicht reduzieren.
2. Treibstoff als eigene Resource (T-026 Antrieb)?
