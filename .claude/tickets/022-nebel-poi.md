# T-022: Nebel POI + Stealth

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** No
**Depends on:** T-017, T-019

## Description

`docs/Nebel.md`: POI in dem sich Flotten verstecken können. Andere Flotten/Sonden entdecken sie erst beim Anflug ins Nebel. Taktik-Werkzeug für Hinterhalt/Flucht.

## AC

- [ ] `Nebula` POI-Subtype
- [ ] `Fleet` kann `FleetLocation::Nebula` haben → hidden-State
- [ ] Sicht-Logik: Standard-Discovery erkennt Fleet im Nebel NICHT
- [ ] Sonde/Fleet die Nebel betritt → entdeckt versteckte Fleets (Event)

## Affected

- Neu: `src/POI/Model/Nebula.php`
- `src/Fleet/Model/Fleet.php` (Nebel-Location supported)
- `src/Tick/Processor/FleetMovementProcessor.php` (Entry-Detection-Hook)

## Open Questions

1. Nebel buff/debuff für Flotten in der Schlacht (z.B. weniger Schaden)? Doc sagt nichts.
2. Maximale Flotten pro Nebel?
