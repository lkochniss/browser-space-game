# T-121 Crusade-System (6-Wochen-Cycle)

**Type:** Feature
**Status:** Draft
**Effort:** XL
**Depends on:** T-077 (World-Boss), T-052 (Allianz), T-076 (Galaxy-Events)
**Blocks:** —

## Beschreibung
Alle 6 Wochen startet 1 Crusade. KEINE permanenten Mandates (Decision). World-Boss-Spawn als Crusade-Target. Allianzen melden sich an, kollektive Belohnung.

Top-3 Allianzen mit höchster Damage-Contribution: Unique-Title für 1 Cycle.

## Acceptance Criteria
- [ ] Crusade-Entity (id, cycleNumber, startsAt, endsAt, targetType, targetEntityId, registeredAlliances-Set, contributorPoints-Map<AllianceId, points>, status)
- [ ] CrusadeScheduler (Cron/Messenger): startet jede 6 Wochen → spawnt World-Boss (T-077) oder Galaxy-Event-Variante
- [ ] Anmeldung: Allianzen registrieren sich pre-Crusade (1 Woche Anmeldephase)
- [ ] Während Crusade: registrierte Allianzen sammeln Damage-Punkte → Battle-Damage gegen Crusade-Target zählt
- [ ] Reward-Phase nach Crusade-End: Top-3 Allianzen → unique Title "Crusader of <Cycle>"
- [ ] Alle teilnehmenden Allianzen: Reward-Resources + Allianz-Reputation
- [ ] Cycle-Counter persistent (Cycle 1, 2, 3 ...)
- [ ] **KEINE permanenten Mandates** (kein "Crusade-Sieger ist Galaxy-Hetman für 6 Wochen")
- [ ] Notifications für alle Allianzen bei Crusade-Start

## Affected Tests
- tests/Crusade/Service/CrusadeSchedulerTest.php
- tests/Crusade/Service/CrusadePointsTrackingTest.php
- tests/Crusade/Service/CrusadeRewardDistributionTest.php

## Fixtures Needed
Yes — Multiple Allianzen + Schiff-Pools für Battle-Tests

## Notes
- 6 Wochen Cycle gewählt für Long-Time-Game-Pacing — nicht jede Woche, sonst Burnout
- Persistent World (Decision): keine Saison-Resets, Crusade ist Mini-Event innerhalb laufendem Game
