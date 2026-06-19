# T-025b: Multi-Lab Research-Boost

**Type:** Feature
**Status:** Draft
**Effort:** S (~1h)
**Depends on:** T-025 (Forschungs-Foundation)
**Blocks:** —

## Beschreibung

T-025-Foundation nutzt nur das **höchste** RESEARCH_LAB-Level eines einzelnen
Planeten als Speed-Multiplier. T-025b stackt mehrere Labs aus mehreren Planeten
des Players, sodass Multi-Planet-Strategie belohnt wird.

## Decision-Idee (zu klären beim Start)

`effectiveLabBonus = max(labLevel) + Σ(otherLabs × diminishing)` — z.B. Haupt-
Lab = 100% Wirkung, jedes weitere Lab = +20% Bonus (mit Cap).

Konkrete Formel offen — abhängig vom Tuning aus T-025-Demo-Erfahrung.

## Acceptance Criteria

- [ ] `Planet::getResearchLabLevel` bleibt (max-pro-Planet); neuer Helper auf
  Player-Ebene: `Player::getEffectiveResearchSpeedMultiplier()` aggregiert über
  alle Planeten
- [ ] `ResearchDurationConfig::computeDuration` nutzt Player-Helper statt nur
  einen Planet
- [ ] Tests: Single-Lab → unverändert; 2 Labs → erhöhter Speed; 5 Labs → cap
- [ ] Doc-Update: research.md

## Out of Scope

- **Parallele Forschungen:** Foundation bleibt 1-zur-Zeit
- **Spezielle Lab-Synergien** (z.B. Lab + Probe-Lab) — eigene Folge-Tickets
