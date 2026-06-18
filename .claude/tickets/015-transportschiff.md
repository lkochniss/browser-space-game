# T-015: Transportschiff + Cargo-Transfer

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** No
**Depends on:** T-012

## Description

`docs/Transportschiff.md`: Transporter befördern Pop, Rohstoffe, Erzeugnisse zwischen Planeten/Stations. Kleine = nur intra-system + schneller. Größere = FTL-fähig + langsamer + Kampf-anfällig.

## AC

- [ ] `ShipType::TRANSPORT_SMALL`, `TRANSPORT_MEDIUM`, `TRANSPORT_LARGE`
- [ ] Transport hält `CargoManifest` (Map type→amount, plus Pop-Slot)
- [ ] `LoadCargoCommand`, `UnloadCargoCommand`
- [ ] Per-Class Constraints: small = intra-system, large = FTL-fähig
- [ ] Combat-Vulnerability-Stat (für T-024)

## Affected

- `src/Ship/ValueObject/ShipType.php`
- Neu: `src/Ship/ValueObject/CargoManifest.php`
- Neu: `src/Ship/Command/Load/UnloadCargoCommand.php` + Handler

## Open Questions

1. Cargo als generische Map oder pro Slot-Typ (Pop separat von Resources)?
2. Auto-Transfer-Routen (Recurring) jetzt oder eigenes Ticket?
