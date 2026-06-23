# T-142 Login-Streak (7-Tage-Cycle)

**Type:** Feature
**Epic:** Quests & Engagement
**Domain:** Player
**Blocked By:** T-037
**Status:** Draft
**Effort:** S
**Depends on:** T-037 (Login)
**Blocks:** —

## Beschreibung
Daily-Login-Streak mit 7-Tage-Cycle. Reset bei verpasstem Tag. Belohnungen pro Tag der Streak (kleiner Bonus, kein Pay-to-Win).

## Acceptance Criteria
- [ ] PlayerLoginStreak-Entity (playerId, currentStreak: int, lastLoginAt, totalLifetimeStreaks)
- [ ] LoginStreakUpdateService: bei Login prüft `lastLoginAt`:
  - Gleicher Tag → no-op
  - Vorheriger Tag → currentStreak++
  - Älter als gestern → currentStreak = 1 (Reset)
- [ ] 7-Tage-Reward-Map: Tag 1 = 100 Iron-Bar, Tag 2 = 200, ..., Tag 7 = Special-Cosmetic
- [ ] Tag 8+: Cycle wiederholt von Tag 1
- [ ] Belohnung claimable manuell via UI (kein Auto-Claim, verhindert Notifications-Spam)
- [ ] Notification (T-161): "Du hast 5 Tage Streak — claim deine Belohnung"

## Affected Tests
- tests/Login/Service/LoginStreakUpdateTest.php (continue, reset)
- tests/Login/Service/LoginStreakRewardTest.php

## Fixtures Needed
No

## Notes
- Belohnungen klein-progressiv: kein P2W-Risiko
- Cycle-Repetition verhindert "ein Streak gerissen, nichts mehr zu erreichen"-Frust
