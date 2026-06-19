# T-141 Achievement-System Foundation (Cosmetic-only)

**Type:** Feature
**Status:** Draft
**Effort:** L
**Depends on:** T-096 (Player-Stats)
**Blocks:** —

## Beschreibung
100+ Achievements in Categories. Tier Bronze/Silver/Gold/Platinum. **NUR COSMETIC** (Title/Banner) — KEINE Buffs (Decision).

Categories:
- Combat (e.g. Slay 100 Pirates → Bronze, 1000 → Gold)
- Economy (Trade-Volume thresholds)
- Diplomatie (Reputation-Tiers erreichen)
- Erkundung (Systems sondiert)
- Building (Buildings gebaut)
- Allianz (Allianz-Membership-Dauer, Crusade-Wins)
- Story (Quest-Completion)

## Acceptance Criteria
- [ ] Achievement-Entity (id, code, category, tier, title, description, requirementSpec)
- [ ] PlayerAchievement-Entity (playerId, achievementId, unlockedAt) — pivot
- [ ] AchievementCheckerService: liest PlayerStats (T-096) → unlocks achievements
- [ ] Trigger via Cron oder EventListener nach jeder Action
- [ ] **NUR Cosmetic-Reward**: Title-Unlock (Player kann gewählten Title in Profile setzen), Banner-Unlock (Cosmetic-Asset)
- [ ] **KEINE Stat-Boni** — explizit in Service-Code dokumentiert
- [ ] Achievement-Browser-UI (Web-Layer): Liste aller Achievements + Progress
- [ ] Progress-Indicator: "47/100 Pirates slain"

## Affected Tests
- tests/Achievement/Service/AchievementCheckerTest.php (multiple categories)
- tests/Achievement/Service/CosmeticOnlyRewardTest.php (assert no stat-buff applied)

## Fixtures Needed
Yes — Achievement-Catalog seeded mit ~30 ersten

## Notes
- 100+ Achievements als Foundation-Goal — start mit ~30 wesentlichen, erweitern inkrementell
- "Cosmetic-only"-Decision dokumentiert in Service-Comment + Test (Anti-Power-Creep)
- Title-Display in Public-Profile (T-054), Chat (T-053), Leaderboard
