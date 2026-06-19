# Demo-Sandbox (Interactive CLI)

## Zweck (T-082 + T-082b + T-082c)

Symfony-Console-Command `app:demo:run --env=demo` als Sandbox zum manuellen
Üben aller bisher implementierten Game-Actions ohne Web-UI. Eigene SQLite-DB
unter `var/demo.db`, isoliert von dev/test.

## Aufruf

```bash
APP_ENV=demo php bin/console app:demo:run --env=demo            # ersten Mal / weitermachen
APP_ENV=demo php bin/console app:demo:run --reset --env=demo    # frischer Reset
```

Auto-Setup: erkennt fehlendes Schema, erstellt es, seedet Faction + Player +
Galaxy via `ClaimStartPlanetCommand`. Mit `--reset` zusätzlich Schema-Drop.

## Demo-Buff (T-082b)

`applyDemoBuff()` nach Bootstrap:
- Hub L1 vorab fertig
- 300 W/F/O initial statt 100

`ensureDemoGalaxyContent()` garantiert:
- 1 AsteroidField (falls noch keiner) im Heimat-System
- 1 Wormhole-Pair Heimat ↔ random anderes System
- 1 DebrisField (T-021) im Heimat-System

→ Salvage / Travel / Recycling sind sofort testbar ohne Pop-Hunger-Tod.

## Menu-Actions

| Action | Backing-Command/Service |
|--------|-------------------------|
| Status | Planet, Resources, Pop, Buildings, Ships, Fleets, POIs |
| **Goals** (T-082c) | `DemoGoalChecker` — 5 Mini-Quests mit ✓/✗ |
| Galaxy Overview | `SolarSystemRepository` filterd auf entdeckte (T-018) |
| Build Building | `BuildBuildingCommand` mit Cost-Preview im Label; locked Buildings mit 🔒 + Forschungs-Reason (T-170) |
| Upgrade Building | `UpgradeBuildingCommand` |
| Build Ship | `BuildShipCommand` mit Shipyard-Pre-Check |
| Build Probe | `BuildProbeCommand` mit Probe-Lab-Pre-Check |
| Create/Move/Disband Fleet | `Create/Move/DisbandFleetCommand` |
| Load/Unload Cargo | `Load/UnloadCargoCommand` |
| Start/Stop Salvage | `Start/StopSalvageCommand` (polymorph: Asteroid + Debris) |
| Colonize Planet | `ColonizePlanetCommand` |
| **Tick Forward** | Choice 15min/1h/4h/1d/custom → `AdjustableClock.advanceSeconds` + `TickEngine.run` + `FleetArrivalService` + `SalvageProcessor` + `TelescopeDiscoveryService` + `ResearchCompletionService` |
| **Export Log** (T-082d) | Zeigt Pfad + letzte 20 Action-Log-Einträge auf stdout |
| **Forschung** (T-025) | Cost+Duration-Preview je Node; ein laufendes Research mit ETA; bei Abschluss Level++ |
| Reset Demo | Drop+Recreate Schema + Re-Seed (+ Log-Backup `.bak`) |

## Demo-Goals (T-082c)

6 fixe Mini-Quests, stateless on-demand check:

1. Hub auf Level 2 ausbauen
2. Alle 3 Basic-Mines (Iron + Coal + Copper) auf einem Planeten
3. Recycling-Plant bauen
4. 50+ Debris-Items sammeln (Planet+Ship-Cargo)
5. 2. Planet kolonisieren
6. Erste Forschung abschließen (T-025)

Stateless Re-Compute aus Player-State, kein DB-Schema. Live-Progress in Hint
("Hub-Level: 1/2", "Debris gesamt: 35/50").

## Action-Log (T-082d)

`DemoActionLogger` schreibt **jede** Menü-Aktion als 1 JSONL-Line in
`var/demo-log.jsonl` (gitignored). Format:

```json
{
  "ts": "2026-06-19T15:25:30.320362+00:00",
  "action": "BuildBuilding",
  "params": { "advance_seconds": 900, "fleets_arrived": 0, ... },
  "success": true,
  "error": null,
  "snapshot": { "clock_now": "...", "player_id": "...", "planets": [...], "ships": [...], "fleets": [...], "discovered_system_ids": [...] }
}
```

**Vollständiger Snapshot** (T-082d Decision): alle IDs + Wallclock-Timestamps
(`finished_at`, `arrived_at`, `salvage_last_tick`). Ermöglicht KI-Tuning von
Cost-/Duration-/Speed-Werten gegen echte Sessions.

`StateSnapshotter` baut den Snapshot aus Player-State + Repos. Read-only-Actions
(Status/Goals/Galaxy/Export Log) werden **bewusst auch geloggt** — zeigen das
Decision-Verhalten des Players.

**Reset-Verhalten:** `--reset` benennt vorhandenes Log um nach
`var/demo-log-{Ymd-His}.jsonl.bak` damit die Vorgeschichte erhalten bleibt.

**Kein Auto-Rotate** (T-082d Decision) — Demo bleibt überschaubar groß; bei
Bedarf manuell wegwerfen.

**Export-Aktion** im Menü: zeigt den Pfad + letzte 20 Einträge kompakt. Vollen
Inhalt bei Bedarf via `cat var/demo-log.jsonl` oder direkt an die KI-Tuning-
Session geben.

## Time-Travel

`AdjustableClock` (im `when@demo:` services-Block als ClockInterface aliased)
erlaubt deterministisches Vorspulen via `advanceSeconds(int)`. Tick-Forward
Action wickelt das mit Tick-Engine + Globalen Tick-Services in einem Schritt.

## Files

- `src/Demo/Command/InteractiveDemoCommand.php` (zentraler Command, Choice-Loop)
- `src/Demo/Service/DemoGoalChecker.php` (T-082c)
- `src/Demo/Service/DemoActionLogger.php` (T-082d, JSONL-Append, Backup-on-Reset)
- `src/Demo/Service/StateSnapshotter.php` (T-082d, vollständiger Player-Snapshot)
- `src/Demo/ValueObject/DemoGoal.php` (readonly DTO)
- `.env.demo` (`DATABASE_URL=sqlite:///var/demo.db`)
- `config/services.yaml` `when@demo:` Block (ClockInterface=AdjustableClock)
- `config/bundles.php` (DoctrineFixturesBundle für demo aktiviert)
- `src/Common/Service/AdjustableClock.php` (advanceSeconds, nullable Constructor)

## Cross-Domain

Demo-CLI ist reiner User der Domain-Commands — keine eigene Game-Logic.
Die eine Ausnahme: `applyDemoBuff` + `ensureDemoGalaxyContent` mutieren Galaxy
direkt um Demo-Tauglichkeit zu garantieren (würde im Web-Layer Test/Fixture-
Domäne sein).

## Geplant

- **T-034 Web-Layer** — UI als Alternative zur Demo-CLI
- **T-103 Battle-Sandbox** (Decisions vorab dokumentiert) — Battle-Action im Demo-Menü
