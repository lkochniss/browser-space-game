# T-072 Erzeugnis-Production-Buildings (Stahlwerk, Chip-Fab, Composite)

**Type:** Feature
**Epic:** Resources Tier-2/3
**Domain:** Building
**Blocked By:** T-067
**Status:** Superseded (durch T-067 — alle 5 Buildings dort in AC; AI-Foundry gehört zu T-115)
**Effort:** M
**Depends on:** T-067 (Erzeugnis-Tree)
**Blocks:** T-102 (Schiff-Klassen)

## Beschreibung
Nominal-Ticket: Bündelt die Erzeugnis-Buildings die in T-067 als ACs erwähnt sind. Falls T-067 zu groß wird, kann hier nachsteuern oder splitten.

Wenn T-067 alle Buildings bereits liefert: dieses Ticket schließt direkt mit "duplicate of T-067".

Geplante Buildings (recap):
- Steel-Smelter (Steel-Output)
- Chip-Fab (Chip-Output)
- Composite-Plant (Composite-Output)
- Hull-Foundry (Hull-Plate-Output)
- Shield-Assembler (Shield-Module-Output)
- AI-Foundry (AI-Core-Output, T-115-gated)

## Acceptance Criteria
- [ ] Pro Building: BuildingType-Enum-Wert
- [ ] Pro Building: Production-Logic (Recipe-Map → ResourceProductionProcessor-Hook)
- [ ] Pop-/Power-Bedarf konsistent mit T-067 Annahmen
- [ ] Storage-Cap-Stop respektiert (T-061)
- [ ] Build-Cost skaliert mit Tier (Steel günstig, Hull-Foundry teuer, AI-Foundry sehr teuer)

## Affected Tests
- tests/Building/Service/SteelSmelterTest.php
- tests/Building/Service/ChipFabTest.php
- tests/Building/Service/CompositePlantTest.php
- tests/Building/Service/HullFoundryTest.php
- tests/Building/Service/ShieldAssemblerTest.php

## Fixtures Needed
Yes — alle Buildings als Seeds, Test-Planet mit Vorgänger-Resources

## Notes
- AI-Foundry kann erst nach T-115 fully implementiert werden
- Möglicherweise Merge in T-067 wenn Scope passt
