# T-011: Raumwerft

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** No
**Depends on:** T-009

## Description

`docs/Raumwerft.md`: Building-Voraussetzung für Schiffsbau. Reine Definition + Voraussetzungs-Check für T-012ff.

## AC

- [ ] `BuildingType::SHIPYARD`
- [ ] Cost-Config-Eintrag
- [ ] Service-Methode `Planet::hasShipyard(): bool` oder `MinShipyardLevel`
- [ ] Spätere Schiffsbau-Commands nutzen das

## Affected

- `src/Building/ValueObject/BuildingType.php`
- `src/Building/Service/BuildingCostConfig.php`
- `src/Planet/Model/Planet.php` (Helper)

## Open Questions

1. Min-Level Raumwerft pro Schiffsklasse (große Schiffe = höheres Level)?
