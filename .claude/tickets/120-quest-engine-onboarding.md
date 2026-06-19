# T-120 Quest-Engine + 10 Onboarding-Quests

**Type:** Feature
**Status:** Draft
**Effort:** L
**Depends on:** T-046 (Onboarding), T-073 (Faction)
**Blocks:** T-140 (Daily/Weekly Quests)

## Beschreibung
Foundation für Quest-System. 10 Onboarding-Quests führen neuen Spieler durch alle Spiel-Mechaniken (Build Mine, Forsche Antrieb, Kolonisiere Planet, Trade an Auction, Erlebe Battle).

Quest-Typen (Foundation):
- TRANSPORT (Bring X von A nach B)
- TRADE (Verkaufe X für Y Cash)
- ESCORT (Begleite Schiff durch Sektor)
- RECOVERY (Salvage zerstörtes Schiff)
- DISCOVERY (Sondiere unbekanntes System)

## Acceptance Criteria
- [ ] Quest-Entity (id, playerId, type, params-JSON, status: ACTIVE/COMPLETED/EXPIRED, progress, rewardSpec, expiresAt)
- [ ] QuestObjective-Strategy-Pattern: pro Quest-Typ eigener Resolver (Transport-Resolver, Trade-Resolver, etc.)
- [ ] Quest-Trigger via EventListener: bei game-state-Änderung (build, trade, kolonize) prüft alle aktiven Quests
- [ ] 10 Onboarding-Quests als seeded Templates, in Reihenfolge (Quest N freigeschaltet nach N-1 done)
- [ ] Reward-Engine: gibt Resources, RP, oder Cosmetics
- [ ] Notification (T-161) bei Quest-Complete
- [ ] Quest-Log-UI (sobald Web-Layer)

## Affected Tests
- tests/Quest/Service/QuestProgressTest.php (jede Type)
- tests/Quest/Service/OnboardingQuestSequenceTest.php (Quest 1 → 10)

## Fixtures Needed
Yes — Quest-Templates seeded

## Notes
- Foundation: 5 Quest-Typen + 10 Onboarding-Templates
- Daily/Weekly (T-140) baut auf gleicher Engine
- Faction-Quests (T-078, deferred) als Folge-Use-Case
