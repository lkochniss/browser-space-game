# T-077 World-Boss / Raid-Target (Allianz-Content)

**Type:** Feature
**Status:** Draft
**Effort:** L
**Depends on:** T-052 (Allianz), T-103 (Battle), T-074/T-075
**Blocks:** —

## Beschreibung
Massive PvE-Targets die nur Allianzen schaffen. Spawn als Crusade-Event-Trigger (T-121) oder als statisches Endgame-POI.

Boss-Types:
- Hive-Tyrant (Xenos): Massive Flotte mit Regen
- Renegade-Warlord-Flagship: Hoher Damage-Output
- Pirate-Armada: Many-vs-Many, Wave-System
- Daemon-Incursion (Special-Event): Galaxy-weite Bedrohung, alle Allianzen koalieren

## Acceptance Criteria
- [ ] WorldBossPOI-Entity (eventId, factionId, fleetComposition, currentHp, contributors-Map<PlayerId, damage>)
- [ ] Multiple-Player-Damage-Tracking während Battle
- [ ] Loot-Verteilung proportional zu Damage-Contribution
- [ ] Top-3-Contributor-Alliance bekommt Crusade-Title (T-121)
- [ ] Boss respawnt 6-Wochen-Cycle (Crusade-Cycle)
- [ ] Boss-HP skaliert mit Galaxy-Population (mehr Spieler = härtere Bosse)
- [ ] Boss zerstört = große Belohnung (T-115 Tier-3-Resources, einzigartige Cosmetics)

## Affected Tests
- tests/Galaxy/Service/WorldBossSpawnTest.php
- tests/Battle/Service/MultiPlayerBossEngagementTest.php

## Fixtures Needed
Yes — Test-Boss mit mehreren Player-Contributors

## Notes
- Boss-Failure-Mode: Wenn keine Allianz schafft den Boss in X Tagen → Galaxy-Penalty (Trade-Steuer +10% 7d) als sanfte Konsequenz
- Endgame-Content; nicht für Bubble-/Early-Game-Spieler relevant
