# T-065 Energy-System (Power-Net pro Planet)

**Type:** Feature
**Status:** Draft
**Effort:** L
**Depends on:** T-006 (Hub), T-009 (Building-Cost)
**Blocks:** T-068, T-071

## Beschreibung
Jeder Planet hat Power-Bilanz. Power-Plants produzieren, Buildings konsumieren. Negativ = Produktion gedrosselt.

## Acceptance Criteria
- [ ] `Planet::getPowerProduced(): int` (Summe aktiver Power-Plants × Level)
- [ ] `Planet::getPowerConsumed(): int` (Summe aktiver Buildings × Level × type-spezifisch)
- [ ] `Planet::getPowerBalance(): int = produced - consumed`
- [ ] Wenn Balance negativ: alle Production-Buildings (Mines, Refineries) skalieren mit `min(1, produced/consumed)` ratio
- [ ] `BuildingType::getPowerConsumption(int $level): int` und `getPowerProduction(int $level): int`
- [ ] Hub liefert Default-Power 100 + 50/level (Hub-Reaktor)
- [ ] Mine = 5×lvl, Refinery = 15×lvl, Storage = 1×lvl Consumption
- [ ] UI-Indicator (sobald Web-Layer existiert): Power-Anzeige im Planet-Dashboard

## Affected Tests
- tests/Planet/Model/PlanetPowerTest.php (unit, balance computation)
- tests/Tick/Processor/ResourceProductionProcessorPowerTest.php (integration, throttle bei Mangel)

## Fixtures Needed
No

## Notes
- Live-computed wie Storage-Cap, kein DB-Feld
- Throttle wirkt auf alle Mines/Refineries gleichmäßig (kein per-Building-Priorisierung im MVP)
- Folge-Ticket: Power-Priorisierung (welches Building zuerst abgeschaltet) optional
