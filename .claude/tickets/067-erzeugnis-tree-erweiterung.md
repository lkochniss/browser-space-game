# T-067 Erzeugnis-Tree-Erweiterung (Tier-2: Steel/Chip/Composite/Hull/Shield)

**Type:** Feature
**Status:** Done
**Effort:** L
**Depends on:** T-003 (Done)
**Blocks:** T-102 (Schiff-Klassen), T-088 (Munition), T-091 (Tier-3-Combat-Goods)
**Supersedes:** T-072 (per Q2 Resolved Decision)

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

- [x] Neue ResourceType-Werte: PLASTIC_RESIN, TRITIUM_ORE (FINITE)
- [x] Neue REFINED ResourceTypes: ALUMINUM_BAR, COPPER_BAR, TITANIUM_BAR, STEEL, CHIP, COMPOSITE, HULL_PLATE, SHIELD_MODULE
- [x] Mines für PLASTIC_RESIN, TRITIUM_ORE inkl. Storage-Contribution
- [x] Refinement-Recipe-Registry: 8 neue Recipes in `RefinementConfig`
- [x] Refinery-Buildings: ALUMINUM_REFINERY, COPPER_REFINERY, TITANIUM_REFINERY, STEEL_SMELTER, CHIP_FAB, COMPOSITE_PLANT, HULL_FOUNDRY, SHIELD_ASSEMBLER
- [~] Storage-Buildings für die neuen Refined-Goods — Q1 Override: NICHT in T-067 (Generic-Volume-Storage T-177 übernimmt das); kleiner Per-Refinery-Storage-Contribution (100/lvl) bleibt
- [x] Pop-Bedarf pro Refinery in `BuildingCostConfig`
- [~] Power-Consumption — defer T-065 (Energy-System Draft); Vorschlag-Werte in Notes dokumentiert
- [~] Multi-Tier-Refinement-Tick: Q3 Override = **Single-Step-pro-Tick** via Snapshot — Cascade läuft progressiv über mehrere Ticks
- [x] Volume-Multi-Einträge (Q4) für 10 neue Resources in `ResourceVolumeConfig`
- [x] Tests: RefinementConfigTest (Recipe-Coverage + STEEL-Spec) + Tier2RefinementCascadeTest (5 IT-Tests: Bar-Production + Steel-Recipe + Cascade-Block + Two-Tick-Cascade + Shield-Module)
- [x] Doc `resources.md` Recipe-Tabelle + Snapshot-Sektion + Volume-Tabelle erweitert

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
- T-072 ist durch dieses Ticket **Superseded** (Q2 resolved)
- Power-Consumption Refinery (Vorschlag): Aluminum/Copper/Titanium-Refinery 15/lvl, Steel 30/lvl, Chip 25/lvl, Composite 20/lvl, Hull 50/lvl, Shield 40/lvl
- Storage-Cap-Stop via T-177 Generic-Volume-Storage (statt T-061 per-Category)

## Resolved Decisions

- **Storage-Approach (Q1):** AC angepasst — keine eigenen Storage-Buildings für
  Tier-2-Refined. Items wandern automatisch in Generic-Volume-Storage (T-177).
  T-067 implementierbar parallel zu T-177.
- **T-072 (Q2):** Superseded durch T-067 (Steel-Smelter/Chip-Fab/etc. liegen
  hier; AI-Foundry gehört zu T-115 Tier-3).
- **Refinement-Tick-Pattern (Q3):** Multi-Tick-Cascade. Pro Tick wird genau
  EIN Refinement-Schritt verarbeitet. Iron-Ore → Iron-Bar (Tick 1) → Steel
  (Tick 2) → Hull-Plate (Tick 3). Begründung: Transparenz für Player +
  kein "Cheat-Feeling" durch instant-Chain.
- **Volume-Multi-Einträge (Q4):** Als AC in T-067. Tabelle siehe unten.

## Volume-Multi-Einträge (für T-180 ResourceVolumeConfig, AC in T-067)

| Resource | Multi (m³) | Reason |
|----------|-----------|--------|
| PLASTIC_RESIN | 1.5 | Halbflüssig, Verpackung |
| TRITIUM_ORE | 2.0 | Erz, sperrig |
| ALUMINUM_BAR | 0.8 | Leicht, refined kompakt |
| COPPER_BAR | 1.4 | Schwerer als Aluminum, kompakt |
| TITANIUM_BAR | 1.0 | Mittlere Dichte refined |
| STEEL | 1.0 | Industrieprodukt (Tier-2-Default) |
| CHIP | 0.3 | Klein, hochwertig pro Volumen |
| COMPOSITE | 1.2 | Sandwich-Material, leichter Bulk |
| HULL_PLATE | 2.5 | Großflächig, sperrig |
| SHIELD_MODULE | 0.8 | Kompakte Einheit |

## Adjusted Acceptance Criteria (Override)

- [~] (Q1) AC "Storage-Buildings für die neuen Refined-Goods" gestrichen —
      Items kommen in Generic-Volume-Storage (T-177)
- [+] (Q4) Volume-Multi-Einträge für 10 neue Resources in
      `ResourceVolumeConfig` (T-180-Tabelle erweitern, siehe oben)
- [+] (Q3) Refinement-Tick implementiert als **Single-Step-pro-Tick**, kein
      Multi-Tier-Chain in einem Tick. Order: Mining-Processor → Refinement-
      Processor (verarbeitet maximal einen Recipe-Step pro Refinery pro Tick)
