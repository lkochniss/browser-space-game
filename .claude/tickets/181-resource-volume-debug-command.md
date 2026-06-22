# T-181: Debug-Command für Resource-Volume-Inspection

**Type:** Feature (Tooling, Low-Prio)
**Status:** Done
**Effort:** XS (~30min)
**Depends on:** T-180 (Resource-Volume-Config)
**Blocks:** —

## Beschreibung

Low-Prio Convenience-Command zur Inspection der Volume-Multi-Tabelle und
Beispiel-Storage-Berechnungen. Nützlich für Balancing-Sessions.

## Acceptance Criteria

- [ ] Symfony-Command `app:debug:resource-volume`
- [ ] Listet alle Items aus `ResourceVolumeConfig` mit Multi-Wert
- [ ] Zeigt Beispiel-Volumen-Berechnungen (z.B. "1000 Iron-Ore = 2000 m³",
      "1000 Pop = 10000 m³", "1000 H2 = 200 m³")
- [ ] Optional: Cross-Reference mit aktueller Demo-State (welche Items
      haben aktuell welches Total-Volume auf Demo-Planet)
- [ ] Test: Command runs ohne Error, Output-Smoke-Test

## Out of Scope

- Live-Editing der Volume-Multi (Q3=a PHP-Const, kein Runtime-Update)
- Balance-Empfehlungen (manuelle Aufgabe)

## Notes

- Nice-to-have, nicht blockend für Foundation
- Wird relevant wenn Volume-Balance angepasst werden muss (Playtest)
