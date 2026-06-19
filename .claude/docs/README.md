# Feature Documentation

Stub. Feature docs werden erstellt sobald Tickets `Done`.

| File | Domain | Summary |
|------|--------|---------|
| persistence.md | (cross) | Doctrine ORM Mapping, UUID-Types, Aggregate-Pattern, Repos, Migrations, Test-Setup |
| resources.md | Resource | Resource-Types (endlich + renewable + refined), Start-Amounts, Base-Werte, Storage-Plan |
| population.md | Planet | Population-VO (total/assigned/cap), Operations, Invarianten, Hydration-Caveat, Type-Multi |
| planets.md | Planet | Aggregat-Struktur, PlanetType (7), PlanetSize (5), Generierung, Pop-Cap |
| buildings.md | Building | BuildingTypes, Cost-Config, Bauprozess, Exceptions, Cap-Recalc, Refinement |

## Source of Truth (raw concept docs)

`/docs/*.md` — Obsidian-Notes mit Konzepten (DE). Sind NICHT die Feature-Docs hier — sie sind Vision/Lore. Diese Datei spiegelt was tatsächlich implementiert ist.

## Domains in `src/`

| Folder | Status |
|--------|--------|
| `Player/` | Player + CreatePlayer flow |
| `Planet/` | Planet entity (+Type/Size T-008, +Population T-004) + Claim flow + Galaxy-Gen |
| `SolarSystem/` | SolarSystem + Planet-Container (T-007) |
| `Building/` | 8 BuildingTypes, Cost-Config, Build/Upgrade-Commands, Refinement-Smelter |
| `Resource/` | 11 ResourceTypes, Mining-Production, Refinement-Recipes, Pop-Consumption |
| `Tick/` | TickEngine (atomic) + 3 Processors (Production, Refinement, Pop-Consumption) |
| `GameState/` | GameState wrapper |
| `Simulation/` | PlayerStartUpScenario (CLI demo) |
| `Common/` | CommandBus, Clock, UUID-Types |
