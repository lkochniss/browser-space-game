# T-096b Player-Stats Extension (Folge zu T-096)

**Type:** Feature
**Status:** Draft
**Effort:** M-L
**Depends on:** T-096 (Foundation, Done), T-103 (Battle), T-080 (Loot),
T-002 Resource-Production-Tick
**Blocks:** T-123 (Player-XP-Career), T-141 (Achievements), T-054 (Leaderboard)

## Beschreibung

T-096 Foundation deckt 3 Command-basierte Counter (buildingsBuilt /
planetsColonized / shipsBuilt). Diese Erweiterung fügt Tick-/Battle-basierte
Counter hinzu sowie eine separate PlayerStats-Entity falls Performance/Schema
das nötig macht.

## Acceptance Criteria

### Mining-Total

- [ ] `Player.statsResourcesMinedTotal: array<ResourceType-value, int>` (JSON)
- [ ] Hook in `ResourceProductionProcessor` (Tick): nach Resource-Mining wird
      Counter pro Resource-Type addiert
- [ ] Idempotenz via TickEngine-Transaction-Wrap (existing pattern)
- [ ] Tests: Multi-Tick-Aggregate

### Battle-Counters (T-103 Hook)

- [ ] `Player.statsBattlesWon: int`, `statsBattlesLost: int`, `statsShipsLost: int`
- [ ] Hook in Battle-Resolver: nach Battle-Resolution Counter pro Sieger/Verlierer

### Faction-Rep-Lifetime

- [ ] `Player.statsFactionRepLifetime: array<FactionSlug, int>` (JSON, cum-Rep-Gain)
- [ ] Hook in `ReputationService::changeReputation()`

### Refactor (optional, falls Performance)

- [ ] PlayerStats als separate Entity (1:1, lazy) statt direkte Player-Felder
- [ ] Migration für existing Counter

### Read-API

- [ ] `PlayerStatsService::getStats(Player): PlayerStatsDto`
      mit allen Foundation + Extension-Countern
- [ ] DTO ist Read-Model für T-141 Achievements + T-054 Leaderboard

### XP-Hook (T-123 Folge)

- [ ] `PlayerStatsService::computeLifetimeXp(Player): int` aggregiert über
      gewichtete Counter (z.B. 1 Building = 10 XP, 1 Battle-Win = 100 XP)

## Out of Scope

- T-123 XP-Career-Implementation (eigenes Ticket)
- T-141 Achievement-Definitions
- T-054 Leaderboard-UI (T-034 web-Layer noch Open)

## Notes

- Foundation in T-096 deckt die Command-basierten Counter; Tick + Battle
  hier separat damit die Idempotency-Diskussion separat geführt werden kann
- Mining-Counter wird hot — alle 15min × alle Players. Performance-Hinweis
  beachten falls Counter-Tabelle wächst (eventuell mit Folge-Ticket auf
  separates Stats-Schema verschieben)
