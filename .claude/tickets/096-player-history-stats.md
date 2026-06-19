# T-096 Player-History / Stats

**Type:** Feature
**Status:** Draft
**Effort:** S
**Depends on:** T-103 (Battle), T-080 (Loot)
**Blocks:** T-141 (Achievements), T-054 (Leaderboard)

## Beschreibung
Persistente Player-Statistics: total Battles won/lost, total Resources mined, total Buildings built, etc. Foundation für Achievements + Leaderboards.

## Acceptance Criteria
- [ ] PlayerStats-Entity (1:1 mit Player, lazy-loaded)
- [ ] Counter-Felder: battlesWon, battlesLost, resourcesMinedTotal (Map<ResourceType, int>), buildingsBuilt, planetsColonized, shipsBuilt, shipsLost, factionRepLifetime (Map<FactionId, int>)
- [ ] EventListener: Tick-Processor + Battle-Resolver schreiben in PlayerStats
- [ ] Idempotent: Bei Re-Tick keine Doppel-Counts (TickProcessor-Hash-Check)
- [ ] Read-API: PlayerStatsService::getStats(Player): PlayerStatsDto
- [ ] Lifetime-XP für T-123 (Player-XP) baut hierauf

## Affected Tests
- tests/Player/Service/PlayerStatsTest.php
- tests/Player/Service/PlayerStatsIdempotencyTest.php

## Fixtures Needed
Yes — Player + Stats-Setup

## Notes
- Counter werden inkremental geupdated, nicht aggregiert aus Events (Performance)
- Gut für T-141 Achievements direkt prüfbar via Counter-Schwelle
