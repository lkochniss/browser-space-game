# T-010: Building Level-Upgrade

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** No
**Depends on:** T-009

## Description

`docs/Bevölkerung.md`: "Bau/Ausbau" — Buildings können geupgradet werden. Aktuell `Building::level` existiert aber kein Upgrade-Flow.

## AC

- [ ] `UpgradeBuildingCommand` + Handler
- [ ] Cost skaliert mit Ziel-Level (Formel im Cost-Config)
- [ ] Level-Cap pro Type optional definieren
- [ ] Upgrade prüft Resources + Pop wie Bau

## Affected

- Neu: `src/Building/Command/UpgradeBuildingCommand.php` + Handler
- `src/Building/Service/BuildingCostConfig.php` (level-aware)

## Open Questions

1. Cost-Skalierung: linear, exponentiell (1.5^level)?
2. Pop-Bindung steigt mit Level oder fix?
