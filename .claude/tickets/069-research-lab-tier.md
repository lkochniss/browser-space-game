# T-069 Forschungs-Lab-Tier-System

**Type:** Feature
**Status:** Draft
**Effort:** M
**Depends on:** T-025 (Forschungs-Framework)
**Blocks:** T-117 (Allianz-Forschung)

## Beschreibung
Research-Building mit Tier-Levels. Höhere Tier = höhere Forschungs-Tiers freigeschaltet (Tier-2-Tech braucht Lab-Lvl-5+, Tier-3 braucht Lab-Lvl-10+).

Lab erzeugt Research-Points (RP) pro Tick. Spieler weist RP einem Research-Project zu.

## Acceptance Criteria
- [ ] BuildingType::RESEARCH_LAB
- [ ] Lab produziert RP pro Tick (Base 1/lvl × Pop-assigned-Multiplier)
- [ ] RP wird auf Player-Account akkumuliert (nicht pro Planet) — globaler Pool
- [ ] Player kann RP einem aktiven Research-Project zuweisen (1 aktives Project zur Zeit)
- [ ] Tech hat `requiredLabTier: int` — ohne Mindest-Lab kein Project startbar
- [ ] Pop-Bedarf 30/lvl (Wissenschaftler)
- [ ] Power-Consumption 50/lvl

## Affected Tests
- tests/Research/Model/LabTierTest.php
- tests/Tick/Processor/ResearchPointProductionTest.php

## Fixtures Needed
Yes — Test-Player mit Lab-Building, mock Research-Project

## Notes
- 1 aktives Project zur Zeit reduziert Min-Maxing
- Specialist-Track (T-098) gibt Lab-Speed-Multiplier auf bestimmten Branches
- RP-Pool global → Spieler kann von jedem Planet Lab nutzen, alle zahlen auf gleiches Project
