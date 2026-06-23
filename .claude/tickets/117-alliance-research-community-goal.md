# T-117 Allianz-Forschung als Community-Goal

**Type:** Feature
**Epic:** Multiplayer
**Domain:** Research
**Blocked By:** T-069, T-052, T-098
**Status:** Draft
**Effort:** M
**Depends on:** T-069 (Lab-Tier), T-052 (Allianz), T-098 (Specialist-Tracks)
**Blocks:** —

## Beschreibung
Mitglieder einer Allianz können RP direkt an Tech-Projekt der Allianz spenden. **Kein zentraler Pool** (konsistent zur User-Decision "keine Allianz-Bank") — RP fließt direkt vom Spender ins aktive Projekt.

Cross-Track-Coverage: Allianz mit verschiedenen Specialist-Tracks (T-098) kann gemeinsam alle 5 Branches in Tier 4-5 erforschen.

## Acceptance Criteria
- [ ] AllianceResearchProject-Entity (allianceId, techId, totalRpRequired, currentRp, contributors-Map<PlayerId, rp>)
- [ ] Pro Allianz max 1 aktives Projekt zur Zeit (vermeidet Verzettelung)
- [ ] Donate-API: Player kann RP von eigenem RP-Pool (T-069) ans Projekt spenden
- [ ] Allianz-Tech-Locks: einmal abgeschlossen, alle Allianz-Members haben Zugriff (analog Player-Forschung)
- [ ] Tech-Effekt aktiviert nur wenn Spieler Allianz-Member bleibt
- [ ] Bei Allianz-Verlassen: Spieler verliert Allianz-Tech-Boni
- [ ] Specialist-Track-Constraint: Allianz kann Tech in Branch erforschen, auch wenn keiner Specialist im Branch ist — aber Cost ×3 (Penalty für Off-Specialty)

## Affected Tests
- tests/Alliance/Service/AllianceResearchDonationTest.php
- tests/Alliance/Service/AllianceResearchOffSpecialtyPenaltyTest.php
- tests/Alliance/Service/AllianceMemberLeaveTechAccessTest.php

## Fixtures Needed
Yes — Allianz mit mixed Specialist-Track-Members

## Notes
- "Community-Goal"-Pattern: Mitglieder sehen Progress live, gemeinsame Belohnung
- Verstärkt Allianz-Diversitätsanreiz: Mix aller 5 Tracks = optimaler Tech-Coverage
