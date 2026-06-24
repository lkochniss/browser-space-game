# T-065 Energy-System (Power-Net pro Planet)

**Type:** Feature
**Epic:** Energy System
**Domain:** Building
**Blocked By:** T-006, T-009
**Status:** Done
**Effort:** L
**Depends on:** T-006 (Done), T-009 (Done)
**Blocks:** T-068, T-071

## Beschreibung

Jeder Planet hat live-berechnete Power-Bilanz. Power-Plants + HUB-Reaktor
produzieren. Buildings konsumieren. Negative Bilanz → Mines/Refineries/
Renewable-Producer drosseln proportional `produced/consumed`.

## Resolved Decisions

- **Power-Producer (Bootstrap):** HUB liefert `50 + 25/Lvl` per Instance.
  Power-Plants (T-071) bleiben Late-Game-Hauptquelle.
- **Throttle-Mechanik:** Hard-Linear-Ratio. Bei `consumed > produced`:
  ratio = `produced / consumed` (< 1).
- **Throttle-Scope (Q1=b):** Mines + Refineries + Renewable-Producer drosseln
  mit ratio. Pop-Survival kann durch Power-Mangel erodieren — realistic.
  Strategic-Unique-Buildings (Shipyard/Lab/etc.) konsumieren aber drosseln
  nichts (sie haben keinen Production-Output).
- **Power-Consumption-Werte (Q2 sanft, Tuning-Knob für spätere Balance):**
  | Building | Consumption/Lvl |
  |----------|-----------------|
  | HQ | 1 |
  | HUB | 1 |
  | Mines (alle 9) | 3 |
  | Renewable (Water/Agri/Atmo) | 1 |
  | Storage (WAREHOUSE) | 1 |
  | IRON_SMELTER + Refineries (8 T-067) | 8 |
  | Recycling-Plant | 6 |
  | RESEARCH_LAB / PROBE_LAB / TELESCOPE | 10 |
  | SHIPYARD | 15 |
  | CONSTRUCTION_YARD | 8 |
  | Hospital / Cultural-Center / Temple | 5 |
- **Demo-CLI-Display (Q3=a):** Status-Section zeigt pro Planet
  `Power: <produced>/<consumed> (ratio X.XX)`. Bei ratio < 1 mit `[THROTTLE]`-Tag.

## Acceptance Criteria

### Power-Bilanz auf Planet

- [x] `BuildingType::getPowerProduction(int $level): int` (nur HUB liefert > 0;
      `50 + 25 × level`; alle anderen 0 bis T-071 PowerPlant-BuildingTypes hinzufügt)
- [x] `BuildingType::getPowerConsumption(int $level): int` per Decision-Tabelle oben
- [x] `Planet::getPowerProduced($now): int` — nur ready Buildings (T-062 isReady)
- [x] `Planet::getPowerConsumed($now): int` — nur ready Buildings
- [x] `Planet::getPowerBalance($now): int = produced - consumed`
- [x] `Planet::getPowerThrottleRatio($now): float` —
      `min(1.0, produced / max(1, consumed))`

### Throttle-Anwendung in Tick-Processors (Q1=b)

- [x] `ResourceProductionProcessor` (Mining) — multipliziert `desired` mit
      `$planet->getPowerThrottleRatio($now)` vor `intval`
- [x] `RefinementProductionProcessor` — analog auf `desiredOutput` (floor)
- [x] `RenewableProductionProcessor` (T-097a) — analog auf produzierte W/F/O (floor)
- [x] Bei ratio = 0 (kein Power) → kein Output (Early-Return im Processor)
- [x] Tests: jede der 3 Production-Mechaniken mit/ohne Throttle

### Demo-CLI

- [x] `showStatus` zeigt pro Planet `Power: <produced>/<consumed> (ratio X.XX)`
- [x] Bei ratio < 1: `[POWER LOW — ratio X.XX]` Tag in rot

### Tests

- [x] `PlanetPowerBalanceTest` (6): produced/consumed/balance/ratio Werte für
      diverse Building-Setups + In-Progress-Filter
- [x] `PowerThrottleProductionTest` (7): jede der 3 Production-Mechaniken
      (Mining/Refinement/Renewable) mit Half-Power, Zero-Power, Full-Power
- [x] 28 bestehende Production-Tests bekamen HUB-Booster (Foundation-Erwartung:
      jeder produktive Planet hat ein HUB)

### Docs

- [x] `buildings.md` Power-Sektion: Producer/Consumer-Tabelle, Throttle-Mechanik
- [x] `decisions.md` Eintrag T-065

## Fixtures Needed

No — Tests nutzen direkt `Planet::generatePlanet()` + Building-Constructor.

## Notes

- Live-computed wie Storage-Cap, kein DB-Feld
- Throttle wirkt proportional, kein per-Building-Priority (Folge-Ticket
  T-065b falls Player Power-Allocation manuell steuern will)
- T-071 Power-Plants (Solar/Fusion/Antimatter) erweitern HUB-Bootstrap
  via dedicated Producer-Buildings — `getPowerProduction` Mapping wächst dort
- Power-Consumption-Werte sind Foundation-soft. Nach Playtest in T-065b tunbar.

### Refinement Tokens (estimate)
- Input: ~7k
- Output: ~3k

### Implementation Tokens (estimate)
- Input: ~120k
- Output: ~12k
