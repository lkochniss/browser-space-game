# T-006: Hub-Gebäude

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** No
**Depends on:** T-004

## Description

`docs/Hub.md`: Hub erhöht Pop-Cap des Planeten. Gebäude wie andere — baubar, levelbar.

## AC

- [ ] `BuildingType::HUB`
- [ ] Pop-Cap des Planeten = Base-Cap + Σ(Hubs * capPerLevel * level)
- [ ] Reine Berechnung — keine Abhängigkeit vom Tick (sofort wirksam beim Bau)
- [ ] Bau-Voraussetzungen über T-009

## Affected

- `src/Building/ValueObject/BuildingType.php`
- `src/Planet/Model/Planet.php` (cap-Berechnung)
- evtl. Service: `PopulationCapCalculator`

## Open Questions

1. Cap-Boost pro Hub-Level? Vorschlag: +50/Level.
2. Max-Anzahl Hubs pro Planet?
