# T-067 Erzeugnis-Tree-Erweiterung (Tier-2: Steel/Chip/Composite/Hull/Shield)

**Type:** Feature
**Status:** Draft
**Effort:** L
**Depends on:** T-003 (Erzeugnis Iron-Bar)
**Blocks:** T-072 (Production-Buildings), T-102 (Schiff-Klassen), T-088 (Munition), T-091 (Tier-3-Combat-Goods)

## Beschreibung

Refinement-Chain Tier-2-Outputs. Schiffe brauchen komplexe Komponenten — nicht nur Iron-Bar.

**Bestand-Resources die wir nutzen** (alle bereits in Code seit T-001/T-002):
IRON_ORE, COAL, COPPER_ORE, SILICON, ALUMINUM_ORE, TITANIUM_ORE, URANIUM_ORE, IRON_BAR.

**Neue Roh-Erze** (Tier-2-Inputs, ergänzen das bestehende Erz-Set):
- PLASTIC_RESIN (vorkommend auf TROPICAL/OCEAN)
- TRITIUM_ORE (vorkommend auf VOLCANIC/ICE)

**Neue Tier-2-Erzeugnisse** (REFINED):
- ALUMINUM_BAR (aus Aluminum-Ore)
- COPPER_BAR (aus Copper-Ore)
- TITANIUM_BAR (aus Titanium-Ore)
- STEEL = Iron-Bar + Coal (3:1:2)
- CHIP = Copper-Bar + Silicon (2:1:1)
- COMPOSITE = Aluminum-Bar + Plastic-Resin (2:2:1)
- HULL_PLATE = Steel + Composite (4:2:1)
- SHIELD_MODULE = Chip + Tritium-Ore (3:1:1)

Tier-3 (PLASTEEL/ADAMANTIUM/AI_CORE) bleibt T-115.

## Acceptance Criteria

- [ ] Neue ResourceType-Werte: PLASTIC_RESIN, TRITIUM_ORE (FINITE)
- [ ] Neue REFINED ResourceTypes: ALUMINUM_BAR, COPPER_BAR, TITANIUM_BAR, STEEL, CHIP, COMPOSITE, HULL_PLATE, SHIELD_MODULE
- [ ] Mines für PLASTIC_RESIN, TRITIUM_ORE inkl. Storage-Contribution
- [ ] Refinement-Recipe-Registry: Input-Map → Output pro Output-Type
- [ ] Refinery-Buildings: ALUMINUM_REFINERY, COPPER_REFINERY, TITANIUM_REFINERY, STEEL_SMELTER, CHIP_FAB, COMPOSITE_PLANT, HULL_FOUNDRY, SHIELD_ASSEMBLER
- [ ] Storage-Buildings für die neuen Refined-Goods (oder Universal-Refined-Storage in Folge-Ticket)
- [ ] Pop-Bedarf + Power-Consumption pro Refinery (steigt mit Tier)
- [ ] Multi-Tier-Refinement-Tick: Erz → Bar → Steel → Hull funktioniert in einem Tick falls genug Inputs

## Affected Tests

- tests/Resource/Model/RefinementChainTest.php
- tests/Tick/Processor/MultiTierRefinementTest.php (chain: ore → bar → steel → hull)
- tests/Building/Service/SteelSmelterTest.php
- tests/Building/Service/ChipFabTest.php
- tests/Building/Service/CompositePlantTest.php
- tests/Building/Service/HullFoundryTest.php
- tests/Building/Service/ShieldAssemblerTest.php

## Fixtures Needed

Yes — alle neuen Resources + Buildings + Mines + Storages als Seeds, Test-Planet mit Vorgänger-Resources

## Notes

- Naming: Aluminum (US, wie im Code) — nicht Aluminium
- T-072 wird wahrscheinlich überflüssig (alle Production-Buildings sind hier abgedeckt) — als Duplicate-Of T-067 schließen
- Power-Consumption Refinery (Vorschlag): Aluminum/Copper/Titanium-Refinery 15/lvl, Steel 30/lvl, Chip 25/lvl, Composite 20/lvl, Hull 50/lvl, Shield 40/lvl
- Storage-Cap-Stop respektiert (T-061)
