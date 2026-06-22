# T-096 Player-History / Stats

**Type:** Feature
**Status:** Done (Foundation; Tick-/Battle-Counters in T-096b split)
**Effort:** S
**Depends on:** T-014 (Done), T-009 (Done), T-012 (Done)
**Blocks:** T-096b

## Beschreibung
Persistente Player-Statistics: total Battles won/lost, total Resources mined, total Buildings built, etc. Foundation für Achievements + Leaderboards.

## Acceptance Criteria

- [x] 3 Counter-Felder direkt auf Player (`stats_buildings_built`,
      `stats_planets_colonized`, `stats_ships_built`) — pragmatisch statt
      separater PlayerStats-Entity (3 ints, kein Schema-Overhead)
- [x] `Player::recordBuildingBuilt()`, `recordPlanetColonized()`,
      `recordShipBuilt()` Methoden (alle post-Success Hooks)
- [x] Hook in `BuildBuildingCommandService` (Initial-Build, nicht Upgrade)
- [x] Hook in `ColonizePlanetCommandService` (Erfolg)
- [x] Hook in `BuildShipCommandService` (Erfolg)
- [x] Migration `Version20260622000006`
- [x] Tests: 5 IT-Tests (Default + 3 Hook-Increments + Multi-Stack)
- [x] Doc `player.md` Stats-Sektion

## Out of Scope (in T-096b verschoben)

- **Resource-Mining-Total** (JSON map ResourceType→int) — braucht Hook in
  ResourceProductionProcessor mit Idempotency-Diskussion
- **Battle-Counters** (battlesWon, battlesLost, shipsLost) — braucht T-103
- **Faction-Rep-Lifetime** (cum-Rep-Gain) — Hook in ReputationService
- **Refactor zu separater PlayerStats-Entity** — falls Performance/Schema
  das verlangt; aktuell 3 ints reichen
- **PlayerStatsDto + getStats() Read-API** — für T-141 Achievements / T-054 Leaderboard
- **Lifetime-XP-Aggregation** — für T-123

## Notes
- Counter werden inkremental geupdated, nicht aggregiert aus Events (Performance)
- Build-Counter zählt Initial-Builds, nicht Upgrades — Upgrade ist Level-Steigerung,
  kein neues Building
- Demo CLI zeigt die Counter heute nicht — kann in Status-Display ergänzt werden
