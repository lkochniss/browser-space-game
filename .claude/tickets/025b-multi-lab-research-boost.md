# T-025b: Multi-Lab Research-Boost

**Type:** Feature
**Epic:** Research & Tech-Tree
**Domain:** Research
**Blocked By:** T-025
**Status:** Done
**Effort:** S (~1h)
**Depends on:** T-025 (Forschungs-Foundation)
**Blocks:** —

## Beschreibung

T-025-Foundation nutzt nur das **höchste** RESEARCH_LAB-Level eines einzelnen
Planeten als Speed-Multiplier. T-025b stackt mehrere Labs aus mehreren Planeten
des Players, sodass Multi-Planet-Strategie belohnt wird.

## Decision (2026-06-19)

Geometric Decay 0.5: `effective = L1×1.0 + L2×0.5 + L3×0.25 + L4×0.125 + ...`.
Asymptotic cap bei 2× max-Lab-Level. Single Lab L3 (3.0) > 3 Labs L1 (1.75).

## Acceptance Criteria

- [x] `StartResearchCommandService::getEffectiveLabLevel(Player, now): float` (public)
- [x] `ResearchDurationConfig::durationSeconds` nimmt jetzt `float $effectiveLabLevel`
- [x] Demo-CLI Forschung-Action zeigt Effective-Lab-Level (Multi-Lab-Aggregat)
- [x] Tests: 4 neue (multi-aggregate, single-better-than-many, fractional-lab, more-labs-faster)
- [x] Suite grün (502/502)
- [x] Doc-Update: research.md

## Out of Scope

- **Parallele Forschungen:** Foundation bleibt 1-zur-Zeit
- **Spezielle Lab-Synergien** (z.B. Lab + Probe-Lab) — eigene Folge-Tickets
