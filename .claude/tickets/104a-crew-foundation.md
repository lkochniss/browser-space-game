# T-104a Crew-Foundation (Akademie + Captains)

**Type:** Feature
**Epic:** Combat & Battle
**Domain:** Ship
**Blocked By:** T-009, T-070
**Status:** Draft
**Effort:** L
**Depends on:** T-009 (Building-Cost), T-070 (Pop-QoL via Officer-Quarters)
**Blocks:** T-102, T-104b, T-104c

## Beschreibung
Captains als limited Resource. Pro Combat-Schiff genau 1 Captain nötig. Captains werden in Akademie ausgebildet — eigene Bauzeit, eigene Pop-Bindung.

Foundation-Scope: Akademie-Building + Crew-Entity + Captain-Type. Skill-Trees (T-104b) + andere Rollen (T-104c) als Folgetickets.

## Acceptance Criteria
- [ ] BuildingType::ACADEMY (trainiert Captains)
- [ ] BuildingType::OFFICER_QUARTERS (Wohnraum/Cap für Captains, max 5/lvl)
- [ ] Crew-Entity (id, type=CAPTAIN, level=1-10, ownerPlayerId, status: TRAINING/IDLE/ASSIGNED, assignedShipId-nullable)
- [ ] Captain-Training: Akademie produziert 1 Captain alle 7 Tage / Lvl
- [ ] Captain-Stats simple: +stats-Bonus auf Schiff (z.B. +5% Damage/lvl, +5% HP/lvl, +5% Schild/lvl)
- [ ] Captain-Cap pro Player = Officer-Quarters-Cap, exceeded → kein Training startbar
- [ ] Captain-Assignment-Service: assign/unassign zu Schiff
- [ ] Captain-Permadeath bei Schiff-Loss in Battle (Captain stirbt mit Schiff)

## Affected Tests
- tests/Crew/Service/CaptainTrainingTest.php (Akademie-Output)
- tests/Crew/Service/CaptainCapTest.php (Officer-Quarters-Limit)
- tests/Crew/Service/CaptainAssignmentTest.php

## Fixtures Needed
Yes — Akademie + Officer-Quarters seeds, Test-Captain-Pool

## Notes
- Foundation: keine Skill-Trees, nur lineares Level → +stats
- Skill-Trees in T-104b
- Permadeath ist Anti-Spam-Sicherung: Captain-Verlust schmerzt
