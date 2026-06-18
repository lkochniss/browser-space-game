# T-014: Kolonisationsschiff + Besiedlungsflow

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** No
**Depends on:** T-007, T-012

## Description

`docs/Kolonisationsschiff.md`: Ermöglicht Besiedlung eines Planeten. Voraussetzung: Planet vorher mit Sonde erkundet (T-013).

## AC

- [ ] `ShipType::COLONY_SHIP`
- [ ] `ColonizePlanetCommand` (Ship, target Planet)
- [ ] Validation: Planet noch nicht claimed, Planet erkundet, Ship am Ziel
- [ ] Erfolg: Planet wird Player zugewiesen, Ship wird verbraucht (oder bleibt — entscheiden)
- [ ] Start-Pop-Pakage durchs Schiff (z.B. 50 Pop)

## Affected

- `src/Ship/ValueObject/ShipType.php`
- Neu: `src/Planet/Command/ColonizePlanetCommand.php` + Handler
- `src/Planet/Model/Planet.php` (Status erkundet/claimed)

## Open Questions

1. Wird Ship bei Kolonisierung zerstört (verbaut) oder kann es weiter nutzen?
2. Pop-Menge mitgeführt fix oder konfigurierbar?
3. Erkundungs-Status auf Planet als bool oder Level (Planetologie-abhängig)?
