# T-070 Pop-QoL-Buildings (Krankenhaus, Universität, Kultur)

**Type:** Feature
**Status:** Draft
**Effort:** M
**Depends on:** T-005 (Pop-Verbrauch), T-006 (Hub)
**Blocks:** —

## Beschreibung
Quality-of-Life-Buildings für Pop. Kein direkter Resource-Output, aber Effekte auf Pop-Wachstum/Effizienz.

Buildings:
- Hospital: +Pop-Cap, reduziert Mangel-Tod-Rate (Buffer bei W/F/O-Mangel)
- University: +RP-Multiplier auf Lab-Output (Pop-basiert)
- Cultural-Center: +Allgemeines-Output-Multiplier (Mining, Refining), max 3 Stufen
- Temple-of-Imperator: +Loyalty (für T-122 Background-Mechaniken, Folge)

## Acceptance Criteria
- [ ] BuildingType::HOSPITAL, UNIVERSITY, CULTURAL_CENTER, TEMPLE
- [ ] Hospital: +20 Pop-Cap/lvl + 50% Mangel-Tod-Reduktion (multiplicative)
- [ ] University: +5%/lvl RP-Output-Multiplier (additiv pro lvl, Cap +50%)
- [ ] Cultural-Center: +2%/lvl Mining+Refining-Output (Cap +20%)
- [ ] Temple: leer-Stub für Loyalty (kein Effekt im MVP)
- [ ] Pop-Bedarf pro QoL-Building (Personal): Hospital 30/lvl, University 40/lvl, Cultural 20/lvl, Temple 15/lvl
- [ ] Power-Consumption: Hospital 30/lvl, University 25/lvl, Cultural 15/lvl, Temple 10/lvl

## Affected Tests
- tests/Building/Service/HospitalEffectTest.php
- tests/Building/Service/UniversityRpMultiplierTest.php
- tests/Building/Service/CulturalCenterMultiplierTest.php

## Fixtures Needed
Yes — Test-Planet mit QoL-Buildings

## Notes
- Effekte multiplicative auf bestehende Boni — Spielraum für stacking
- Temple-of-Imperator als Hook für späteres Loyalty/Faction-System
