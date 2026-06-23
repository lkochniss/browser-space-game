# T-097a: Renewable-Production-Buildings (Foundation)

**Type:** Feature
**Epic:** Pop QoL
**Domain:** Building
**Blocked By:** T-009, T-061, T-062
**Status:** Done
**Effort:** M (~2-3h)
**Depends on:** T-009 (Building-Cost), T-061 (Storage-System), T-062 (Real-Time-Construction)
**Blocks:** —
**Splits from:** T-097 (Pop-Tier-Buildings) — der QoL-Teil bleibt T-097

## Beschreibung

Aktuell: WATER/FOOD/OXYGEN sind reine Konsum-Resources. `PopulationConsumptionProcessor`
zieht ~5 W/F pro Tick bei 50 Pop, **niemand produziert Nachschub**. Initial-100-
Buff (T-001) + 300-Demo-Buff (T-082b) reichen ein paar Ticks, dann stirbt Pop.

`ResourceProductionConfig` hat zwar Base-Rates (5/3/0) für W/F/O — die werden
aber von keinem Processor gelesen, weil `ResourceProductionProcessor` nur
DEPOSITS iteriert (= FINITE-Resources).

→ Demo ist nicht dauerhaft spielbar. T-097a fügt 3 Tier-0-Foundation-Producer
hinzu damit Pop-Versorgung selbsttragend wird.

## Acceptance Criteria

- [ ] 3 neue `BuildingType`-Enum-Einträge:
  - `WATER_RECLAIMER` (produziert WATER)
  - `AGRI_DOME` (produziert FOOD)
  - `ATMOSPHERIC_PROCESSOR` (produziert OXYGEN)
- [ ] BuildingCostConfig + BuildingDurationConfig Einträge
  (jeweils 100 IRON_ORE + 5 pop, 15min Build, analog Storage-Buildings)
- [ ] **Tier-0** (keine Forschungs-Lock) — Foundation, ohne sie kein Pop-Survival
- [ ] Neuer `RenewableProductionProcessor` (TickProcessor):
  - Iteriert die 3 BuildingTypes
  - Pro fertigem Building: `+ baseRate × level` zur jeweiligen Resource
  - Storage-Cap-aware (clamp am cap)
- [ ] `RenewableProductionConfig` mit Base-Rates (siehe Balance-Tabelle unten)
- [ ] Storage-Contribution: keine (sind Producer, nicht Storage — bleibt bei
  Hub/Tank/Silo)
- [ ] Tick-Reihenfolge: läuft ZWISCHEN ResourceProduction und PopulationConsumption,
  damit frische W/F sofort konsumiert werden können (statt 1 Tick Verzögerung)
- [ ] Tests: 3+ unit (Production-Rate, Multi-Level, Storage-Cap-Stop)
- [ ] Doc: resources.md (Renewable-Production-Sektion), buildings.md (3 neue
  Building-Types in Tabelle)

## Balance-Vorschlag

| Building | Resource | Base-Rate | Cost | Duration |
|----------|----------|-----------|------|----------|
| WATER_RECLAIMER | WATER | +10/tick/level | 100 IRON_ORE + 5 pop | 15min × 2^level |
| AGRI_DOME | FOOD | +6/tick/level | 100 IRON_ORE + 5 pop | 15min × 2^level |
| ATMOSPHERIC_PROCESSOR | OXYGEN | +6/tick/level | 100 IRON_ORE + 5 pop | 15min × 2^level |

Verbrauch zur Referenz: 50 Pop × 0.1 = **5 W/F per Tick**. L1-Reclaimer/Dome
gibt **+10 / +6** → kleiner Surplus, deckt Pop-Wachstum bis ~100. L2 = +20/+12,
deutlicher Surplus bis ~200 Pop.

Skalierung: Base-Rate × Level (linear, analog Mining). T-151 Soft-Cap bei
großen Stockpiles greift weiter.

## Out of Scope

- **Pop-QoL-Buildings** (Krankenhaus, Kultur, Tempel) — T-070
- **Tier-2 Advanced-Producer** (Hydroponics-Lab, Recycling-Tower etc.) — T-097
- **Forschungs-Lock auf höhere Tiers** — Folge-Ticket
- **Type-Multiplier** (z.B. ICE-Planet bekommt WATER-Bonus) — Folge in T-063

## Notes

- Mit Production existiert ein echter Day-1+-Loop: Pop wächst → mehr Verbrauch
  → höhere Production-Buildings nötig
- Storage-Cap bleibt limitierend; Player muss Storage parallel ausbauen wenn
  Production hoch
- Demo-Buff (T-082e) mit 1500 W/F/O bleibt sinnvoll als Day-1-Komfort
