# Feature Documentation

Index aller Domain-Docs. Reflektiert was implementiert ist (Done-Tickets), nicht
Vision. Vision-Konzepte liegen unter `/docs/*.md` (Obsidian-Notes, DE).

| File | Domain | Summary |
|------|--------|---------|
| persistence.md | (cross) | Doctrine ORM Mapping, UUID-Types, Aggregate-Pattern, Repos, Migrations, Test-Setup |
| resources.md | Resource | 14 ResourceTypes (FINITE+RENEWABLE+REFINED+DEBRIS), Mining, Refinement, SoftCap |
| population.md | Planet | Population-VO (total/assigned/cap), Operations, Invarianten, Hydration-Caveat |
| planets.md | Planet | Aggregat, PlanetType (7), PlanetSize (5), Generierung, Pop-Cap |
| buildings.md | Building | 18 BuildingTypes, Cost/Duration-Config, Bauprozess, Storage-Cap, Tick-Reihenfolge |
| ships.md | Ship | 6 ShipTypes, Bau (Shipyard-Gate), Life-Support, Cargo, Salvage (polymorph), Colonize |
| crew.md | Crew | T-104a Foundation: Captain, Akademie/Officer-Quarters, Wallclock-Training, XP-Level-Up, Assign, Permadeath |
| combat.md | Battle | T-103 Foundation Battle-Resolver — Round-Engine, Captain-Stats + Permadeath, Planet-Defense-Konsum |
| fleets.md | Fleet | DOCKED ↔ IN_TRANSIT, Create/Move/Disband, FleetArrivalService (Tick-Resolver), Travel-Speed |
| poi.md | POI | STI-Foundation + 5 Subtypes (Asteroid/Debris/Nebula/Wormhole/Station), SalvageableField-Interface |
| probes.md | Probe | 3 ProbeTypes + ProbeLab-Gate; Foundation, Effekte folgen mit T-018/T-027/T-087 |
| tick.md | Tick | TickEngine (atomic, tagged_iterator), 6 Processors + 3 globale Tick-Services, Reihenfolge |
| discovery.md | Discovery | T-018 PlayerSystemDiscovery + Telescope-Reveal, Galaxy-Fog-of-War-Foundation |
| research.md | Research | T-025 Wallclock-Forschungs-Framework + RESEARCH_LAB + Stub-Nodes; Tree-Erweiterungen via Folge-Tickets |
| factions.md | Faction | NPC-Faction-Foundation + Reputation-Service; aktuell nicht aktiv konsumiert |
| player.md | Player | Player-Aggregat + Galaxy-Bootstrap (ClaimStartPlanet), Init-Konstanten |
| demo.md | Demo | Interactive Sandbox-CLI `app:demo:run` + Demo-Goals + Time-Travel |
| decisions.md | (meta) | Architektur-Decisions log |
| dependencies.md | (meta) | Cross-Domain Dependency-Graph |
| glossary.md | (meta) | Begriffs-Definitionen |

## Source of Truth (raw concept docs)

`/docs/*.md` — Obsidian-Notes mit Konzepten (DE). Sind NICHT die Feature-Docs hier
— sie sind Vision/Lore. Diese Datei spiegelt was tatsächlich implementiert ist.

## Domains in `src/`

| Folder | Doc | Status |
|--------|-----|--------|
| `Player/` | player.md | Foundation done |
| `Planet/` | planets.md + population.md | T-004/T-007/T-008 done |
| `SolarSystem/` | (in player.md / poi.md) | T-007 Foundation |
| `Building/` | buildings.md | 18 Types, T-009-T-011, T-013, T-018, T-021, T-061-T-062, T-151 |
| `Resource/` | resources.md | 14 Types incl. DEBRIS (T-021), 4 Categories |
| `Tick/` | tick.md | 6 Processors, T-009/T-012/T-021/T-062 |
| `Ship/` | ships.md | T-012/T-014/T-015/T-016 |
| `Fleet/` | fleets.md | T-017 |
| `POI/` | poi.md | T-019-T-023 + T-085 |
| `Probe/` | probes.md | T-013 (Foundation only) |
| `Discovery/` | discovery.md | T-018 |
| `Research/` | research.md | T-025 (Foundation only, Branches folgen) |
| `Faction/` | factions.md | T-073 (Foundation only) |
| `Demo/` | demo.md | T-082 + T-082b + T-082c |
| `DataFixtures/` | (in persistence.md) | T-049a WorldFixture |
| `Common/` | (in persistence.md / tick.md) | UUIDs, Clock, Randomizer, SoftCap |
| `GameState/` | (in tick.md) | Wrapper |
| `Simulation/` | (n/a) | Test-Scenarios |
| `Controller/` | (n/a) | leer — T-034 Web-Layer kommt |
