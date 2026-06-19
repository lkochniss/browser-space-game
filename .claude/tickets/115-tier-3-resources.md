# T-115 Tier-3 Resources (Plasteel, Adamantium, Plasma-Cell, AI-Core)

**Type:** Feature
**Status:** Draft
**Effort:** L
**Depends on:** T-067 (Erzeugnis-Tree), T-086 (Black-Hole für Antimaterie-Source)
**Blocks:** T-077 (World-Boss-Loot), T-116 (Mega-Strukturen)

## Beschreibung
Endgame-Tier Resources. Komplexe Recipe-Chains, hohe Build-Cost, gating für End-Tier-Content (Mega-Strukturen, Tier-3-Schiffe).

Phase 1 (dieses Ticket): Plasteel + Adamantium
Phase 2 (Folge-Ticket oder erweitern): Plasma-Cell + AI-Core

## Acceptance Criteria
- [ ] Neue ResourceTypes: PLASTEEL, ADAMANTIUM, PLASMA_CELL, AI_CORE, ANTIMATTER
- [ ] Recipes:
  - Plasteel = Steel + Composite + Heat-Treatment (5:3:1)
  - Adamantium = Iron-Bar + Tritium + Plasma-Cell (10:5:1) — selten, Tier-3
  - Plasma-Cell = Antimaterie + Chip (1:5:1)
  - AI-Core = Chip + Adamantium + Specialized-Algorithm (Forschung-Lock) (5:1:1)
- [ ] Antimaterie-Source: Black-Hole-Harvest (T-086) primär
- [ ] Spezial-Refineries: Plasteel-Forge, Adamantium-Crucible, Plasma-Forge, AI-Foundry
- [ ] Forschungs-Lock: AI-Core braucht Tier-5-Forschung in Kybernetik-Branch
- [ ] Pop-Bedarf hoch (Specialist-Workforce)
- [ ] Power-Consumption sehr hoch (Plasma-Forge 200/lvl, AI-Foundry 300/lvl)

## Affected Tests
- tests/Resource/Model/Tier3RefinementTest.php
- tests/Building/Service/AiFoundryGateTest.php (Forschung-Lock)

## Fixtures Needed
Yes — Tier-3-Resources + Refineries seeded

## Notes
- "Endgame-Bottleneck": Adamantium-Drop-Quelle nur via Xenos-Outposts (T-075) + Black-Hole — verknappt absichtlich
- AI-Core-Gate forciert Specialist-Track-Diversität (T-098)
