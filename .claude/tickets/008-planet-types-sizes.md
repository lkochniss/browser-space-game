# T-008: Planet-Typen + Größen

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** No

## Description

`docs/Planet.md`: 5 Größen (sehr klein, klein, mittel, groß, gigantisch), verschiedene Typen mit unterschiedlichen Rohstoffen + Boni. Spieler startet auf erdähnlichem Planet. Aktuell ohne Type/Size.

## AC

- [ ] `PlanetSize` enum (`TINY`, `SMALL`, `MEDIUM`, `LARGE`, `HUGE`)
- [ ] `PlanetType` enum (initial: `TERRAN`/erdähnlich, weitere Stubs nach Bedarf)
- [ ] `Planet` hält `size` + `type`
- [ ] Generierung: `GeneratePlanetCommandService` rollt Type + Size; Deposits abhängig von Type/Size
- [ ] Start-Planet hart auf `TERRAN` + sinnvolle Size

## Affected

- `src/Planet/Model/Planet.php`
- Neu: `src/Planet/ValueObject/PlanetSize.php`, `PlanetType.php`
- `src/Planet/Service/GeneratePlanetCommandService.php`
- evtl. `ResourceDeposit`-Generierung gekoppelt an Type

## Open Questions

1. Typenliste vollständig jetzt definieren? Vorschlag: TERRAN, BARREN, ICE, GAS_GIANT, OCEAN, VOLCANIC, DESERT.
2. Size beeinflusst was konkret? (Pop-Cap base, Anzahl Building-Slots, Deposit-Mengen?)
3. Boni-System spec aus Doc unvollständig — separates Ticket?
