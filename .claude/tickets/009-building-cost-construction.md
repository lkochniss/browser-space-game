# T-009: Building-Kosten + Bauprozess

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** No
**Depends on:** T-004 (Population)

## Description

`docs/Bevölkerung.md` + `docs/Raumschiff.md`: Bau verbraucht Erzeugnisse + freie Pop. Aktuell `Building::createNewBuilding` ohne Kosten. Building-Kosten + `BuildBuildingCommand` einführen.

## AC

- [ ] `BuildingCost` VO (Map ResourceType→amount + Pop-Bedarf)
- [ ] Per `BuildingType` definierte Cost (Service `BuildingCostConfig`)
- [ ] `BuildBuildingCommand` + Handler: prüft + zieht Resources + bindet Pop
- [ ] Fehlt etwas → Exception (`InsufficientResourcesException` o.ä.)
- [ ] Pop-Bindung permanent (assigned bleibt erhöht solange Building existiert)

## Affected

- Neu: `src/Building/ValueObject/BuildingCost.php`, `Service/BuildingCostConfig.php`
- Neu: `src/Building/Command/BuildBuildingCommand.php` + Handler
- `src/Planet/Model/Planet.php` (build-API)

## Open Questions

1. Bauzeit (Ticks) jetzt oder erst später? Vorschlag: 1 Tick instant für MVP, BauQueue später eigenes Ticket.
2. Pop dauerhaft assigned oder nur während Bauphase?
