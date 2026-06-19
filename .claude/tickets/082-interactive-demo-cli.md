# T-082: Interactive Demo-CLI für Game-Sandbox

**Type:** Feature
**Status:** Done
**Effort:** L
**MIG:** No (separate demo-env mit eigener SQLite-File)
**Depends on:** alle bisher implementierten Commands (T-001 bis T-023, T-073, T-085, T-016, T-017, T-019-T-020, T-022, T-151)
**Blocks:** —

## Beschreibung

Interaktiver Symfony-Console-Command der einen Demo-Player + Start-Galaxy seedet
und alle Game-Actions als Choice-Menü anbietet. Zentraler Use-Case: User kann
manuell verschiedene Spiel-Cases üben ohne Web-UI.

Aufruf:
```
APP_ENV=demo php bin/console app:demo:run --env=demo            # erstes Mal
APP_ENV=demo php bin/console app:demo:run --reset --env=demo    # Reset
```

## Acceptance Criteria

- [x] `.env.demo` mit `DATABASE_URL=sqlite:///%kernel.project_dir%/var/demo.db`
- [x] `AdjustableClock` im `when@demo:` Block als ClockInterface-Alias registriert
- [x] `AdjustableClock::advanceSeconds(int)` Helper ergänzt + nullable Constructor
- [x] `InteractiveDemoCommand` (`app:demo:run`) mit Choice-Loop
- [x] `--reset` Flag + auto-detect-Setup beim ersten Run (schema-existence-check)
- [x] Auto-Setup: schema create + faction seed + Player + Galaxy via ClaimStartPlanetCommand
- [x] Aktions-Menü mit allen bisher implementierten Commands:
  - Status (Planet, Resources, Pop, Buildings, Schiffe, Fleets, POIs)
  - Building Build / Upgrade
  - Schiff Build (alle ShipTypes via Choice)
  - Probe Build (alle ProbeTypes)
  - Fleet Create / Move / Disband
  - Cargo Load / Unload
  - Salvage Start / Stop
  - Kolonisation (ColonizePlanetCommand)
  - Tick Forward — Choice (15min/1h/4h/1d/custom) + Clock advance + TickEngine.run + FleetArrivalService.resolve + SalvageProcessor.runTick
  - Forschung (Stub: "noch nicht verfügbar — T-025")
  - Reset Demo (mit Confirmation)
- [x] Inline-Scoping: TickEngine-Wiring in services.yaml gefixt (tagged_iterator
  app.tick_processor) — war existing autowire-error
- [x] Tests: 6 Unit (AdjustableClockTest mit DataProvider)
- [x] Suite grün (423/423, 1433 assertions)
- [x] Smoke-Test: `app:demo:run --reset --env=demo --no-interaction` bootet sauber

## Out of Scope

- Web-UI / Browser-Frontend → T-034
- Multi-Player-Demo → später, irrelevant für Single-Player-Sandbox
- Battle-Resolution → T-103 noch Draft
- Galaxy-Map-Visualisierung → T-160
- Multi-Ship-Selection im Fleet-Create — Foundation: 1 Ship pro Fleet, Player kann
  weitere via TBD-Erweiterung adden

## Files

**Neu:**
- `src/Demo/Command/InteractiveDemoCommand.php`
- `.env.demo`
- `tests/Common/Service/AdjustableClockTest.php`

**Geändert:**
- `src/Common/Service/AdjustableClock.php` (+ advanceSeconds + nullable Constructor)
- `config/services.yaml` (TickEngine-tagged_iterator-Wiring + when@demo Clock-Alias)

## Notes

- Demo-Env nutzt SQLite-File, Production-DB bleibt unberührt
- AdjustableClock erlaubt deterministisches Time-Travel
- Pattern für künftige Sandbox-Tools: `app:demo:*` Command-Namespace
