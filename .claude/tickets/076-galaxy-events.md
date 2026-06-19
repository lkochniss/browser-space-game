# T-076 Galaxy-Events (Kosmische Stürme, Anomalien, Limited-Time)

**Type:** Feature
**Status:** Draft
**Effort:** M
**Depends on:** T-007 (SolarSystem), T-019 (POI-System)
**Blocks:** —

## Beschreibung
Limited-Time-Events die Galaxy-State temporär verändern. Quelle für engagement-loops + überraschende Ressourcen-Möglichkeiten.

Event-Types:
- Warp-Storm: temporär Bewegung in System gedrosselt (×0.5 Speed)
- Resource-Anomalie: temporäres Bonus-Deposit auf Planet (×2 Mining 7d)
- Xenos-Invasion: temporärer Spawn-Surge von Xenos-Outposts (Galaxy-weit, Crusade-Trigger T-121)
- Friedensfest-Imperator: Galaxy-Cooperation-Bonus (×1.2 Trade-Speed 3d)

## Acceptance Criteria
- [ ] GalaxyEvent-Entity (eventType, scope (system/galaxy), startsAt, expiresAt, payload-JSON)
- [ ] Event-Scheduler-Service (Cron/Messenger): random-spawnt Events pro Woche
- [ ] Event-Effekt-Resolver: Event modifiziert Tick-Multiplier während Aktivität
- [ ] In-Game-Notification (T-161) bei Event-Start
- [ ] Galaxy-Map (T-160) zeigt aktive Events visuell
- [ ] Mindestens 4 Event-Types implementiert
- [ ] Event-Log auf Player-Account (welche Events miterlebt)

## Affected Tests
- tests/Galaxy/Service/EventSchedulerTest.php
- tests/Galaxy/Service/EventEffectResolverTest.php (multiplier wirkt)

## Fixtures Needed
Yes — pre-scheduled Events für Test-Setup

## Notes
- Events sollen sich nie spielentscheidend negativ auswirken — höchstens Disturb (Warp-Storm = Verzögerung, kein Schaden)
- Crusade-Events (T-121) sind eine Spezialform mit Allianz-Coalition-Trigger
