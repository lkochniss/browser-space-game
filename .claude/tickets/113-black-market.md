# T-113 Black-Market (Renegade-Reputation-Path)

**Type:** Feature
**Epic:** Trade & Economy
**Domain:** Trade
**Blocked By:** T-131, T-073
**Status:** Draft
**Effort:** L
**Depends on:** T-131 (Spy-System), T-073 (Faction)
**Blocks:** —

## Beschreibung
Folge-Mechanik nach Spy-System (T-131). Spieler mit positiver Reputation zur Renegade-Faction (durch missions, schwer zu erreichen) bekommt Zugriff auf Black-Market.

Black-Market verkauft:
- Illegale Tech (Tech-Steals nicht durch normale Forschung erreichbar)
- Tier-3-Resources zu Markup-Preis (sofort, ohne Forschung)
- Stolen-Blueprints

## Acceptance Criteria
- [ ] Renegade-Reputation-Path: Renegade ist `isAlwaysHostile` (T-073), aber Spy-Missions (T-131) können +Reputation erzeugen → Override-Mechanik?
- [ ] **Decision needed**: Renegade umstellen auf nicht-always-hostile + spezielles Reputation-Threshold (z.B. -50 für Black-Market-Access)?
- [ ] BlackMarketOrder-Entity ähnlich Auction (T-111), aber zugänglich nur bei Renegade-Rep ≥ Threshold
- [ ] Imperium-Backlash: Trade auf Black-Market senkt Reputation zur Merchant-Guild + Faction Imperium (sobald T-122 Background-Loyalty)
- [ ] Risiko: Black-Market-Orders haben höhere Schiff-Verlust-Chance unterwegs (Imperialer Aufgriff)
- [ ] Cosmetic: "Renegade Trader" Title bei extensive Use

## Affected Tests
- tests/BlackMarket/Service/RenegadeAccessTest.php
- tests/BlackMarket/Service/ImperiumBacklashTest.php

## Fixtures Needed
Yes — Player mit Renegade-Rep + Spy-Missions completed

## Notes
- Hängt direkt an T-131 — ohne Spy-System keine Reputation-Path zu Renegade
- "schwer zu erreichen": Renegade-Rep-Aufbau soll viele Sessions kosten — kein Casual-Mechanik
