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

## Resolved Decisions

- **Power-Producer (Default-Bootstrap):** HUB (neu, multi-instance via T-172)
  liefert `50 + 25/level` per Instance. Multi-Stack belohnt Pop-Wachstum.
  Power-Plants (T-071) bleiben dedizierte Hauptquelle für Late-Game.
- **Throttle-Mechanik bei Mangel:** Hard-Linear-Ratio (alle Production-
  Buildings drosseln proportional mit `produced/consumed`). Kein Per-Building-
  Priority im MVP.
- **Power-Consumer-Scope:** Alle Buildings konsumieren (auch Renewable +
  HQ — Lore: Lights, Pumps, Admin). Magnitude differenziert:
  - HQ + HUB + Renewable (Water-Reclaimer/Agri-Dome/Atmospheric-Processor)
    + Storage: klein (1-3×level)
  - Production (Mines, Refineries): mittel (5-15×level wie ursprüngliche AC)
  - Strategic-Unique (Shipyard, Research-Lab, Probe-Lab, Construction-Yard):
    hoch (25-40×level — Late-Game-Bottleneck-Faktor)
- **HQ-Verbrauch:** `3×level` (klein, aber nicht 0 — konsistent mit
  "alle konsumieren").

## Adjusted Acceptance Criteria (Override)

- [x] (Spezifiziert) `BuildingType::getPowerConsumption(int $level): int`
      für ALLE Types definieren (nicht nur Mine/Refinery/Storage)
- [x] (Spezifiziert) `BuildingType::getPowerProduction(int $level): int`
      nur HUB (50 + 25/level); rest = 0 bis T-071
- [x] (Renamed) Original-AC "Hub liefert 100 + 50/level (Hub-Reaktor)"
      ersetzt durch: HUB liefert 50 + 25/level per Instance
