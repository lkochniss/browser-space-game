# T-070b Pop-QoL-Buildings Effekte-Extension (Folge zu T-070)

**Type:** Feature
**Status:** Draft
**Effort:** M
**Depends on:** T-070 (Foundation, Done), T-005 (Pop-Verbrauch, Done),
T-025c (Research, Done), T-065 (Energy, Draft), T-122 (Background, Draft)
**Blocks:** —

## Beschreibung

T-070 etabliert die 4 QoL-BuildingTypes + Cost/Duration + Hospital-Pop-Cap +
Cultural-Center-Mining/Refinement-Multi. Die restlichen Effekte brauchen
Hooks in bestehenden Services und sind hier ausgelagert.

## Acceptance Criteria

### Hospital — Mangel-Tod-Reduktion

- [ ] `PopulationConsumptionProcessor` liest Hospital-Level pro Planet
- [ ] Mangel-Tod-Rate `× (1 - 0.5 × min(1.0, hospitalLevel / 5))` —
      L5 Hospital halbiert die Sterberate
- [ ] Tests: Pop-Mortality bei W/F/O-Mangel mit + ohne Hospital

### University — RP-Output-Multiplier

- [ ] `StartResearchCommandService` liest University-Level auf Primary-Lab-Planet
- [ ] Effective-Lab-Bonus `+ 0.05 × universityLevel` (max +0.5 ≈ ½ Level)
- [ ] ODER separater Speed-Multiplier auf `ResearchDurationConfig::durationSeconds`
- [ ] Tests: Forschungs-Dauer mit/ohne University

### Temple — Loyalty-Stub

- [ ] Foundation-Hook in `Player` für `loyalty: int` (default 0)
- [ ] Temple-Level boostet Loyalty-Gewinn (Q1: pro Level oder Tick?)
- [ ] Effekt-Mapping zu T-122 Background-Bonuses (Loyalty = X% mehr Identity-Bonus?)
- [ ] Tests

### Power-Consumption (alle 4)

- [ ] T-065 Hook (sobald Energy-System Draft → Done):
      Hospital 30/Lvl, University 25/Lvl, Cultural 15/Lvl, Temple 10/Lvl
- [ ] PowerNetService skipped, falls Strom nicht ausreicht → Effekt 0

## Open Questions

### Q1: University RP-Multi-Modell

- (a) Additiv zum Effective-Lab (`+0.05/lvl`)
- (b) Multiplikativer Speed-Faktor (`× (1 + 0.05/lvl)`)
- (c) Cost-Reduktion statt Speed-Boost

### Q2: Hospital-Mangel-Tod-Formel

- (a) Linear (50% Reduktion bei L5)
- (b) Asymptotisch (max 80% Reduktion bei L10)
- (c) Step (L1=10%, L3=30%, L5=50%, L10=70%)

### Q3: Temple-Loyalty

- (a) Player-Level (1 Wert pro Player)
- (b) Pro-Planet-Wert
- (c) Pro-Faction-Wert
- Welche Werte hat Temple bevor T-122 done ist?

## Out of Scope

- Crew-System (T-104a) Synergien — eigener Hook
- Building-Damage / Defense (T-068) hat eigene Munition-Mechanik
- Allianz-Boni für QoL — späteres Folge-Ticket

## Notes

- T-070 ist Foundation; T-070b wartet auf abhängige Services
- Foundation-Effekte (Hospital Pop-Cap, Cultural-Center Mining/Refinement)
  funktionieren bereits ohne T-070b
