# T-099 Threat-Skalierung (PvE-Encounter passt sich an Spieler-Stärke an)

**Type:** Feature
**Epic:** NPC Factions
**Domain:** Faction
**Blocked By:** T-074, T-075, T-096
**Status:** Draft
**Effort:** M
**Depends on:** T-074 (Pirate), T-075 (Outposts), T-096 (Stats)
**Blocks:** —

## Beschreibung
PvE-Bedrohungen skalieren mit Player-Score (Power-Rating). Anfänger trifft schwache Pirate, Veteran trifft Hive-Tyrants.

PlayerScore = aggregierter Wert aus Pop-total, Buildings-Count, Schiff-Stärke, Forschung-Tier.

## Acceptance Criteria
- [ ] PlayerScoreService::compute(Player): int
- [ ] Cached pro Stunde (kein Live-Recompute pro Encounter)
- [ ] PirateSpawnService (T-074) liest Score → wählt Fleet-Composition aus passendem Pool
- [ ] OutpostSpawn (T-075) wählt Threat-Level entsprechend
- [ ] WorldBoss-HP (T-077) skaliert mit Galaxy-Aggregate-Score
- [ ] Neue Spieler in Bubble (T-150): Score=0 → keine Encounters
- [ ] Score-Tier-Bands: 1-100 = Newbie, 101-500 = Etablished, 501-2000 = Veteran, 2000+ = Endgame

## Affected Tests
- tests/Player/Service/PlayerScoreTest.php
- tests/Faction/Service/ThreatScaledSpawnTest.php

## Fixtures Needed
Yes — Players in verschiedenen Score-Bands

## Notes
- Score-Cap-Stop: ab Score 5000 keine weitere Threat-Skalierung — sonst Run-Away
- Catch-Up-Bonus (T-150) für Late-Joiner reduziert effective-Score temporär
