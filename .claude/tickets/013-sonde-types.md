# T-013: Sonden (Systemsonde, Orbitalsonde, Tiefenscan-Sonde)

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** No
**Depends on:** T-012

## Description

`docs/Sonde.md` + Subtypen: 3 Sondentypen für Erkundung. Können von Schiffen transportiert werden. System-Sonde billig + One-shot. Orbital bleibt im Orbit + liefert Telemetrie. Tiefenscan für versteckte POIs/seltene Resources/Endgame.

## AC

- [ ] `ProbeType` enum: `SYSTEM`, `ORBITAL`, `DEEP_SCAN`
- [ ] `Probe` Entity (id, type, location, fuel)
- [ ] `BuildProbeCommand` (analog Ship)
- [ ] Probe kann von Ship transportiert werden (transport-Slot)
- [ ] Per-Type Capabilities-Definition (was scannt was)

## Affected

- Neu: `src/Probe/Model/Probe.php`, `ValueObject/ProbeType.php`
- Neu: `src/Probe/Command/BuildProbeCommand.php` + Handler

## Open Questions

1. Probe = eigene Domain oder Sub von Ship? Vorschlag: eigene Domain, da semantisch andere Lebenszyklen (Einmalmission, kein Pop).
2. Welche Forschung pro Type Voraussetzung?
