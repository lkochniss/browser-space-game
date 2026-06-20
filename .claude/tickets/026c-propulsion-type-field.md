# T-026c: PropulsionType-Field auf Ship + Speed/Range

**Type:** Feature
**Status:** Draft
**Effort:** M (~3h)
**Depends on:** T-026 (Antriebs-Tree), T-066 (Treibstoff Draft — optional)
**Blocks:** —

## Beschreibung

T-026 hat 7 Antriebs-Forschungs-Nodes implementiert, aber Schiffe nutzen sie
nicht. Es fehlt: `PropulsionType`-Enum, Ship-Field, Speed/Range pro Type,
Verzahnung mit FleetMovementConfig.

## Acceptance Criteria

- [ ] `PropulsionType` Enum (HYDROGEN, ION, FUSION, ANTIMATTER, HYPERDRIVE,
      WARP, JUMPDRIVE)
- [ ] `Ship.propulsion` Field (Default = HYDROGEN für existing ships,
      Migration-Strategy: NULL = legacy)
- [ ] `PropulsionType::getSpeed(): int` (in-System km/s)
- [ ] `PropulsionType::getRange(): int` (max Inter-System-Sprünge)
- [ ] BuildShipCommandService prüft Player hat passende Antriebs-Forschung
      (z.B. propulsion_fusion für Fusion-Antrieb)
- [ ] FleetMovementConfig nutzt Propulsion-Speed statt Default
- [ ] Tests
- [ ] Doc: ships.md Propulsion-Sektion

## Out of Scope

- Fuel-Mechanik → T-066 / T-026d
- Refit / Antriebs-Wechsel auf bestehendem Schiff
