# T-070 Pop-QoL-Buildings (Krankenhaus, Universität, Kultur)

**Type:** Feature
**Status:** Done (Foundation; Mangel-Tod-Reduction + University-RP + Temple-Loyalty + Power in T-070b split)
**Effort:** M
**Depends on:** T-005 (Done), T-006 (Done), T-172 (Done)
**Blocks:** T-070b

## Beschreibung
Quality-of-Life-Buildings für Pop. Kein direkter Resource-Output, aber Effekte auf Pop-Wachstum/Effizienz.

Buildings (alle strikt-unique pro Planet, T-171-konform):
- Hospital: +Pop-Cap, reduziert Mangel-Tod-Rate (Buffer bei W/F/O-Mangel)
- University: +RP-Multiplier auf Lab-Output (Pop-basiert)
- Cultural-Center: +Allgemeines-Output-Multiplier (Mining, Refining), max 3 Stufen
- Temple-of-Imperator: +Loyalty (für T-122 Background-Mechaniken, Folge)

## Acceptance Criteria

- [x] BuildingType::HOSPITAL, CULTURAL_CENTER, TEMPLE (alle unique)
      _(T-182: UNIVERSITY revoked — Wort-Mix-Up mit RESEARCH_LAB)_
- [x] BuildingCostConfig-Entries (Hospital 250 IO + 50 Cu / 30 Pop,
      Cultural 200 IO + 50 Si / 20 Pop, Temple 150 IO / 15 Pop)
- [x] BuildingDurationConfig-Entries (Hospital/Cultural 30min, Temple 20min)
- [x] Slot-Size: alle 1 (T-182: UNIVERSITY-Slot-2 obsolet)
- [x] Hospital: +20 Pop-Cap/Level (via `getPopulationCapBonusPerLevel`)
- [x] Cultural-Center: +2%/Level Mining + Refinement, capped +20% (via
      neuer `Planet::getCulturalCenterMultiplier()` Helper, multipliziert in
      `getEffectiveMiningMultiplier` + `getEffectiveRefinementMultiplier`)
- [x] Tests: HospitalPopCapTest (4 Tests), CulturalCenterMultiplierTest (5 Tests)
- [x] BuildingUniquenessTest erweitert um neue Buildings
- [x] Doc `buildings.md` QoL-Sektion (sobald `buildings.md` existiert)

## Out of Scope (in T-070b verschoben)

- **Mangel-Tod-Reduction durch Hospital** — braucht `PopulationConsumptionProcessor` Hook
- **University RP-Multiplier** — braucht Q1-Decision (additiv vs multiplikativ)
- **Temple Loyalty-Stub** — braucht T-122 Background-Mechaniken zur Verzahnung
- **Power-Consumption** — braucht T-065 Energy-System

## Notes
- Effekte multiplicative auf bestehende Boni — Spielraum für stacking
- Temple-of-Imperator als Hook für späteres Loyalty/Faction-System
