# T-152 Inactivity-Schutz (7d / 30d / 90d Stages + Vacation-Pack)

**Type:** Feature
**Epic:** Game Balance
**Domain:** Player
**Blocked By:** T-037, T-074
**Status:** Draft
**Effort:** M
**Depends on:** T-037 (Login), T-074 (Pirate-Spawn)
**Blocks:** —

## Beschreibung
Schutz inaktiver Spieler. 3-Stage-System:
- **7 Tage inaktiv**: Pirate-Encounters auf Heimat pausiert. Production läuft normal weiter.
- **30 Tage inaktiv**: Production halbiert (Pop wird teils zu Civilian-Reserve), aber Heimat unverwundbar.
- **90 Tage inaktiv**: Player-Account "frozen" — Resources eingefroren, kein Production, keine NPC-Action. Heimat bleibt intakt.

Plus: **Vacation-Welcome-Pack** bei Re-Login nach 30+ Tagen Pause: Bonus-Resources + 7 Tage Catch-Up-Mining-Boost.

## Acceptance Criteria
- [ ] PlayerActivityState-Enum (ACTIVE, INACTIVE_7D, INACTIVE_30D, INACTIVE_90D)
- [ ] InactivityChecker-Cron: täglich aktualisiert State basierend auf `lastLoginAt`
- [ ] Pro State entsprechende Multiplier in Production / Combat / Spawn-Services
- [ ] Vacation-Pack-Service: bei Re-Login nach 30+ Tagen → Resource-Drop + 7d Mining-×1.5
- [ ] Frozen-Account: bei 90d-Stage → Production-Tick komplett skipped
- [ ] Re-Login-Notification: "Du warst X Tage weg — hier ist dein Welcome-Pack"
- [ ] Allianz: inaktive Members behalten Membership 90d, dann auto-removed

## Affected Tests
- tests/Player/Service/InactivityStateTest.php (state-transitions)
- tests/Player/Service/VacationPackTest.php (re-login scenarios)
- tests/Tick/Processor/FrozenAccountSkipTest.php

## Fixtures Needed
Yes — Players mit unterschiedlichen lastLoginAt

## Notes
- 90d-Frozen statt Account-Delete: Spieler kann jederzeit zurückkommen
- Allianz-Member-Auto-Removal verhindert Karteileichen-Spam
