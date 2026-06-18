# T-016: Bergungsschiff + Salvage

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** No
**Depends on:** T-012, T-020 (Asteroidenfeld), T-021 (Trümmerfeld)

## Description

`docs/Bergungsschiff.md`: Ziviles Schiff, baut Trümmerfelder + Asteroidenfelder ab und transportiert das Material zurück.

## AC

- [ ] `ShipType::SALVAGE`
- [ ] `SalvageCommand` (Ship, target POI)
- [ ] Per Tick: Ship am Asteroidenfeld → Erz-Resource ans Schiff/Planet
- [ ] Per Tick: Ship am Trümmerfeld → Trümmer ans Schiff/Planet
- [ ] Bei Asteroidenfeld leer → POI verschwindet
- [ ] Bei Trümmerfeld leer → POI verschwindet

## Affected

- `src/Ship/ValueObject/ShipType.php`
- Neu: `src/Ship/Command/SalvageCommand.php` + Handler
- Neu: `src/Tick/Processor/SalvageProcessor.php`

## Open Questions

1. Salvage-Rate pro Tick (fix vs. Schiffsklasse)?
2. Wer hält die geborgene Ware: das Schiff (CargoManifest) oder direkt der Heimatplanet?
