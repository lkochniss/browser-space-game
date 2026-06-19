# Probes (Sonden)

## ProbeTypes (T-013)

| Type | Zweck |
|------|-------|
| `SYSTEM` | Meta-Info eines Systems |
| `ORBITAL` | Detail auf Planet (Resources, Pop) |
| `DEEP_SCAN` | Tiefe POI-Inhalte (z.B. Asteroid-Contents) |

Kosten + Bauzeit via `ProbeCostConfig::getResourceCost/getDurationSeconds`.

## Bauprozess (T-013)

`BuildProbeCommand(planetId, probeType)` → `BuildProbeCommandService`:
- Voraussetzung: `Planet::hasProbeLab($now)` — fertiger PROBE_LAB-Building
- Resource-Cost (Pop-Cost optional je nach Config)
- `finishedAt = now + duration`, ProbeFoundation als eigene Entity persistiert
- T-013 ist Foundation: Probes werden gebaut, Effekte/Targets sind Folge-Tickets:
  - **T-018** Telescope-Discovery (System-Discovery alternative)
  - **T-027** Planetologie-Forschung (Detail-Stats)
  - **T-087** Fog-of-War (POI-Discovery)

## Status

Aktuell: Probe wird gebaut + persistiert, hat aber **keinen aktiven Use-Case**.
Demo-CLI bietet "Build Probe" als Action, das Bauen funktioniert; was eine
fertige Probe konkret tut, kommt später.

## Files

- `src/Probe/Model/Probe.php` (Entity)
- `src/Probe/ValueObject/{ProbeId,ProbeType}.php`
- `src/Probe/Service/{BuildProbeCommandService,ProbeCostConfig}.php`
- `src/Probe/Command/BuildProbeCommand.php` + Handler

## Cross-Domain

- **Building/PROBE_LAB**: Bau-Voraussetzung
- **Discovery (T-018)** — Probes können in Folge System-Discovery befüttern (TBD)
