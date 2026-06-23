# T-108 Specialty-Mining-Buildings (Asteroid-Drone / Deep-Drill / Atmospheric-Harvester)

**Type:** Feature
**Epic:** Resources Tier-2/3
**Domain:** Building
**Blocked By:** T-002, T-019, T-020, T-066
**Status:** Draft
**Effort:** M (TBD)
**Depends on:** T-002 (Endliche Rohstoffe), T-019 (POI), T-020 (Asteroidenfeld), T-066 (Treibstoff)
**Blocks:** —

## Beschreibung

Spezialisierte Mining-Buildings für non-standard Resource-Quellen.

Neue Buildings:
- ASTEROID_MINING_DRONE_HUB: minet Asteroidenfelder (T-020) automatisch; pro Tick X Resources, abhängig vom Field-Type
- DEEP_DRILL: ermöglicht Erz-Extraction in höheren Mengen pro Mine-Level (Forschungs-gated, Mining-Branch); +50% Output, dafür 3× Power
- ATMOSPHERIC_HARVESTER: extrahiert Tritium-Ore aus GAS_GIANT-Atmosphäre (sonst nur VOLCANIC/ICE)
- ICE_DRILLER: extrahiert Wasser + Tritium aus ICE-Planeten effizienter
- VOLCANIC_TAPPER: extrahiert Promethium + Sulfur aus VOLCANIC effizienter (Promethium-Mine in T-066, hier optimiert)
- LUNAR_REGOLITH_PROCESSOR: für DESERT-Planeten — Aluminium-Ore-Boost

## Acceptance Criteria

- [ ] TBD: Neue BuildingType-Werte
- [ ] TBD: Asteroid-Drone-Hub als POI-Bound-Building (braucht Asteroidenfeld in System)
- [ ] TBD: Planet-Type-spezifische Buildings (gating: bauf nur auf passendem Planet-Type)
- [ ] TBD: Deep-Drill-Forschung-Lock (Mining-Branch)
- [ ] TBD: Tier-Skalierung Output-Multiplier mit Building-Level

## Open Questions

- Asteroid-Drone-Hub: physisch im System oder am Heim-Planet? (logisch im System — Decision)
- Specialty-Buildings: existieren parallel zu Standard-Mines, oder ersetzen sie?
- Asteroid-Field-Erschöpfung (T-020) — Drone-Hub stoppt automatisch?

## Notes

- Planet-Type-Spezialisierung wird wirtschaftlich relevant (T-118 Region-Profile-Effekt)
- Asteroid-Mining öffnet Wirtschaft auf Galaxy-Ebene (POI-Mining vs Planet-Mining)
