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
| Build Building | `BuildBuildingCommand` mit Cost-Preview im Label |
| Upgrade Building | `UpgradeBuildingCommand` |
| Build Ship | `BuildShipCommand` mit Shipyard-Pre-Check |
| Build Probe | `BuildProbeCommand` mit Probe-Lab-Pre-Check |
| Create/Move/Disband Fleet | `Create/Move/DisbandFleetCommand` |
| Load/Unload Cargo | `Load/UnloadCargoCommand` |
| Start/Stop Salvage | `Start/StopSalvageCommand` (polymorph: Asteroid + Debris) |
| Colonize Planet | `ColonizePlanetCommand` |
| **Tick Forward** | Choice 15min/1h/4h/1d/custom → `AdjustableClock.advanceSeconds` + `TickEngine.run` + `FleetArrivalService` + `SalvageProcessor` + `TelescopeDiscoveryService` |
| Forschung (Stub) | T-025 noch offen |
| Reset Demo | Drop+Recreate Schema + Re-Seed |

## Demo-Goals (T-082c)

5 fixe Mini-Quests, stateless on-demand check:

1. Hub auf Level 2 ausbauen
2. Alle 3 Basic-Mines (Iron + Coal + Copper) auf einem Planeten
3. Recycling-Plant bauen
4. 50+ Debris-Items sammeln (Planet+Ship-Cargo)
5. 2. Planet kolonisieren

Stateless Re-Compute aus Player-State, kein DB-Schema. Live-Progress in Hint
("Hub-Level: 1/2", "Debris gesamt: 35/50").

## Time-Travel

`AdjustableClock` (im `when@demo:` services-Block als ClockInterface aliased)
erlaubt deterministisches Vorspulen via `advanceSeconds(int)`. Tick-Forward
Action wickelt das mit Tick-Engine + Globalen Tick-Services in einem Schritt.

## Files

- `src/Demo/Command/InteractiveDemoCommand.php` (zentraler Command, Choice-Loop)
- `src/Demo/Service/DemoGoalChecker.php` (T-082c)
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
