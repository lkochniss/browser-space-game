# T-162 Build-Templates / Bau-Queue-UI

**Type:** Feature
**Epic:** Game UI
**Domain:** Building
**Blocked By:** T-094, T-034
**Status:** Draft
**Effort:** M
**Depends on:** T-094 (Bau-Queue), T-034 (Web-Layer)
**Blocks:** —

## Beschreibung
Power-User-Tool: Spieler speichert Build-Templates ("Mining-Setup", "Combat-Hub") und appliziert sie auf neue Planeten in einem Klick.

Template = ordered List of Build-Tasks (Construct/Upgrade pro BuildingType + Level-Target).

## Acceptance Criteria
- [ ] BuildTemplate-Entity (id, ownerPlayerId, name, tasks-JSON-Array)
- [ ] Template-Save-API: Spieler exportiert aktuellen Planet-State als Template
- [ ] Template-Apply-API: bei neuem Planet → queue alle Tasks via T-094 BuildQueue
- [ ] UI: Template-Browser, Save-Current-State-Button, Apply-To-Planet-Button
- [ ] Validation: Template-Apply checkt Resources/Pop-Verfügbarkeit, queues nur was passt
- [ ] Public-Templates: Spieler kann Template als "shareable" markieren, andere Spieler können kopieren
- [ ] Max 10 private + 5 public Templates pro Player

## Affected Tests
- tests/Building/Service/BuildTemplateApplyTest.php
- tests/Building/Service/PublicTemplateShareTest.php

## Fixtures Needed
Yes — Sample-Templates seeded

## Notes
- "Power-User-Tool" — kein P2W, vereinfacht Multi-Planet-Management
- Templates respektieren T-094 Queue-Slots (queue füllt sich nach Cap)
