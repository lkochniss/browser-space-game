# T-140 Daily / Weekly Quest-Erzeugung

**Type:** Feature
**Status:** Draft
**Effort:** M
**Depends on:** T-120 (Quest-Engine), T-096 (Player-Stats)
**Blocks:** —

## Beschreibung
Spieler-spezifische Quest-Erzeugung basierend auf game-state (Pop, Buildings, Faction-Rep). 3 Daily + 1 Weekly.

Daily: refresh 24h (alte verfallen, neue erscheinen).
Weekly: refresh 7d (höhere Rewards).

## Acceptance Criteria
- [ ] DailyQuestGenerator-Service: täglich pro Player → wählt 3 Quest-Templates passend zu Player-State
- [ ] WeeklyQuestGenerator-Service: wöchentlich pro Player → 1 Weekly-Quest mit Big-Reward
- [ ] Template-Pool: 20+ Daily-Templates (Build X Building, Mine Y Resource, Trade Z, Battle ein NPC)
- [ ] Adaptiv: Newbie bekommt einfache Quests, Veteran höhere-Tier-Quests
- [ ] Reset-Cron: Daily reset @ 00:00 UTC, Weekly @ Monday 00:00 UTC
- [ ] Reward-Skalierung: Daily kleine Rewards (Resources, RP), Weekly groß (Cosmetics + große Resource-Drops)

## Affected Tests
- tests/Quest/Service/DailyQuestGeneratorTest.php (Adaptivität)
- tests/Quest/Service/WeeklyResetCronTest.php

## Fixtures Needed
Yes — Quest-Templates pool seeded

## Notes
- Mass-Generation pro Tick → Performance: Batch-Generate alle aktive Players
- Quest-Engine reuse aus T-120
- Daily-Login-Streak (T-142) hängt zusammen — Daily-Quest-Slot füllt Streak
