# T-026c: PropulsionType-Field auf Ship + Speed/Range

**Type:** Feature
**Epic:** Ships & Fleet
**Domain:** Ship
**Blocked By:** T-026
**Status:** Done
**Effort:** M (~3h)
**Depends on:** T-026 (Antriebs-Tree, Done)
**Blocks:** —

## Beschreibung

T-026 hat 7 Antriebs-Forschungs-Nodes implementiert, aber Schiffe nutzten sie
nicht. T-026c führt `PropulsionType`-Enum + Ship-Field + Research-Gate beim
Build + Speed-Multiplier-Integration in Fleet-Movement.

## Acceptance Criteria

- [x] `PropulsionType` Enum (HYDROGEN, ION, FUSION, ANTIMATTER, HYPERDRIVE,
      WARP, JUMPDRIVE) mit `getSpeedMultiplier`, `getMaxSystemRange`,
      `getRequiredResearchSlug`, `isFtl`
- [x] `Ship.propulsion` Field (Default = HYDROGEN, Migration füllt existing
      Ships mit Default-Wert)
- [x] `Ship::getEffectiveSpeed()` = `ShipType.getSpeed() × Propulsion.getSpeedMultiplier()`
- [x] `Fleet::getMinSpeed()` nutzt Effective-Speed statt nur ShipType-Speed
- [x] `BuildShipCommandService` prüft `getRequiredResearchSlug()` via
      `PlayerResearchRepository` und wirft `PropulsionResearchNotMetException`
- [x] Migration `Version20260622000002` (ships.propulsion, default 'hydrogen')
- [x] Tests: Unit (`PropulsionTypeTest`) + IT (3 BuildShip-Tests, Default/Gate/Success)
- [x] Doc `ships.md` Propulsion-Sektion + Exceptions-Tabelle

## Out of Scope

- Fuel-Mechanik (T-066, blocked by T-177)
- Refit / Antriebs-Wechsel auf bestehendem Schiff
- Per-Move-Range-Enforcement (Schiff kann nur X Systeme weit jumpen) — `getMaxSystemRange`
  ist heute informativ; T-026d kann das später enforcen
- Demo-CLI Propulsion-Auswahl beim Ship-Build (Default HYDROGEN reicht; UI-Erweiterung Folge)
