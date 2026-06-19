# T-123 Player-XP / Karriere

**Type:** Feature
**Status:** Draft
**Effort:** L
**Depends on:** T-096 (Player-Stats)
**Blocks:** —

## Beschreibung
Player-Level-System. XP aus diversen Aktivitäten (Building, Battle, Trade, Quest). Cap Level 100. Asymptotischer XP-Cost — späte Levels brauchen Wochen.

Skill-Slots offen für später (z.B. permanente Mini-Boni durch Skill-Picks pro Level — Folge-Ticket).

## Acceptance Criteria
- [ ] Player-Entity bekommt `level: int` (default 1), `xpTotal: int`, `xpThisLevel: int`
- [ ] XPSourceTable: Building +X xp, Battle-Win +Y, Quest +Z, etc.
- [ ] XPThresholdFunction: `xpForLevel(level) = 100 × level^2.2` (Beispiel — asymptotisch)
- [ ] Level-Up-Event: emittiert Domain-Event (T-057), triggert Notification (T-161)
- [ ] Level-Cap 100
- [ ] Skill-Slot-Reservation: pro Level 1 Skill-Slot, aber Skill-Picks sind Folge-Ticket (Slot bleibt unused im MVP)
- [ ] Player-Level visible in Public-Profile (T-054)

## Affected Tests
- tests/Player/Service/PlayerXpAccrualTest.php (XP aus diversen Sources)
- tests/Player/Service/PlayerLevelUpThresholdTest.php (asymptotic curve)

## Fixtures Needed
Yes — Player in unterschiedlichen Level-Stages

## Notes
- "Wochen für späte Levels": absichtlich Long-Time-Spieler-Anker
- Skill-Slots als Hook für T-098 Specialist-Track-Erweiterung oder andere
- XP-Sources sollen alle Aktivitäten honorieren — kein Single-Activity-Grind dominant
