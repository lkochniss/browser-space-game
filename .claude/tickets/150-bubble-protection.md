# T-150 Bubble-Schutz (Tutorial-Phase bis 2. Planet)

**Type:** Feature
**Status:** Draft
**Effort:** M
**Depends on:** T-046 (Onboarding), T-014 (Kolonisation)
**Blocks:** T-074 (Pirat-Spawn pausiert in Bubble)

## Beschreibung
Newbie-Schutz: bis zur Kolonisation des 2. Planeten ist Spieler in Bubble-Phase. **Keine NPC-Encounters, keine Spieler-Interaktionen** — pure Tutorial-Modus mit Mining/Buildings/Forschung.

Tutorial-Gate via T-014 Kolonisation: Bubble endet erst wenn Spieler Kolonisationsschiff baut + erfolgreich 2. Planet kolonisiert.

Catch-Up-Bonus: Late-Joiner +50% Mining für 14 Tage nach Account-Creation (laufend, auch wenn Bubble vorbei).

## Acceptance Criteria
- [ ] Player-Entity: `bubbleStatus: enum BUBBLE/EXITED` (default BUBBLE)
- [ ] Bubble-Exit-Trigger: ColonizationCommand-Success für 2. Planet → setzt status EXITED
- [ ] Während Bubble:
  - PirateSpawnService (T-074) skippt Player
  - OutpostAttacks (T-075) richten sich nicht auf Bubble-Player
  - AuctionService (T-111) blockt Player (kein early Trade-Min-Maxing)
  - Galaxy-Map zeigt nur eigenen Sektor + Tutorial-Hinweise
- [ ] Catch-Up-Multiplier: `(1 + 0.5)` Mining-Output für 14 Tage seit Player.createdAt
- [ ] UI: Bubble-Phase als Onboarding-Indikator
- [ ] Notification bei Bubble-Exit: "Welcome to the Galaxy"

## Affected Tests
- tests/Player/Service/BubbleProtectionTest.php (Pirate skipped, Auction blocked)
- tests/Player/Service/BubbleExitOnSecondPlanetTest.php
- tests/Player/Service/CatchUpMultiplierTest.php

## Fixtures Needed
Yes — Players in unterschiedlichen Bubble-States

## Notes
- "bis 2. Planet" statt zeitbasiert (Decision): Spieler-Tempo respektieren
- Catch-Up-Bonus läuft parallel (auch nach Bubble-Exit) — verkürzt Aufholzeit zu Veteran-Spielern
