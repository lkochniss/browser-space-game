# T-082f: Demo-Action-Log zeigt zu wenig Details (Build/Research/etc.)

**Type:** Bug
**Status:** Open
**Severity:** Medium (KI-Tuning kann Patterns nicht erkennen ohne Action-Params)
**Effort:** XS (~30min)

## Symptom

Im JSONL-Log unter `var/demo-log.jsonl` ist `params: []` für die meisten
Aktionen. Nur `Tick Forward` liefert echte Parameter (advance_seconds etc.).

Beispiel-Eintrag heute:
```json
{
  "ts": "...",
  "action": "Build Building",
  "params": [],     ← leer!
  "snapshot": {...}
}
```

→ Bei KI-Balance-Tuning lässt sich nicht erkennen, **welches Gebäude** der
Player gebaut hat — nur dass irgendein Build-Versuch lief. Snapshot zeigt
zwar das Endergebnis, aber Cause→Effect-Analyse ist tedious.

## Root Cause

`$this->lastActionParams = []` wird zu Beginn jeder Loop-Iteration zurückgesetzt.
Nur `tickForward` schreibt Werte hinein. Build/Research/Salvage/etc. bleiben
leer obwohl sie konkrete Parameter (Building-Type, Node-Slug, ...) haben.

## Fix

Pro Action-Methode `$this->lastActionParams = [...]` setzen, **nachdem**
die Validation/Choice durchgelaufen ist (= gewählter Wert bekannt).

## Acceptance Criteria

Pro Action mindestens ein aussagekräftiger Param:

| Action | Params |
|--------|--------|
| Build Building | `building_type`, `planet_id` |
| Upgrade Building | `building_id`, `building_type`, `from_level`, `planet_id` |
| Build Ship | `ship_type`, `planet_id` |
| Build Probe | `probe_type`, `planet_id` |
| Create Fleet | `ship_ids`, `origin_planet_id` |
| Move Fleet | `fleet_id`, `target_planet_id` |
| Disband Fleet | `fleet_id` |
| Load Cargo | `ship_id`, `resources`, `pop_count` |
| Unload Cargo | `ship_id`, `resources`, `pop_count` |
| Start Salvage | `ship_id`, `poi_id`, `resource_type` |
| Stop Salvage | `ship_id` |
| Colonize Planet | `ship_id`, `target_planet_id` |
| Forschung | `node_slug` (existiert teilweise schon) |
| Reset Demo | `confirmed: true/false` |

- [ ] Alle 13 Actions setzen `$this->lastActionParams = [...]` nach Choice
- [ ] Smoke-Test: nach Build + Research zeigt Log konkrete Params
- [ ] Suite grün

## Files

**Geändert:**
- `src/Demo/Command/InteractiveDemoCommand.php` (alle Action-Methoden)

## Out of Scope

- Snapshot-Detail-Erweiterung (Snapshot ist schon vollständig)
- Web-UI-Log-Viewer
