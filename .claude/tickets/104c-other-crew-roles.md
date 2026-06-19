# T-104c Andere Crew-Rollen (Forscher, Engineer, Diplomat)

**Type:** Feature
**Status:** Draft
**Effort:** M
**Depends on:** T-104a (Crew-Foundation), T-069 (Lab-Tier), T-110 (Trade-Routes)
**Blocks:** —

## Beschreibung
Erweitert Crew-System auf Non-Combat-Rollen. Jede Rolle hat eigenes Skill-Profil und Bindung an Building-Typ.

Rollen:
- **Forscher**: assigned an Lab → +Lab-Speed (×1.05/level), max 3 pro Lab
- **Engineer**: assigned an Schiff → -Maintenance + Repair-Speed
- **Diplomat**: assigned an Faction → +Reputation-Speed bei Reputation-Aktivität
- **Spy**: bleibt für T-131 (separate Foundation)

## Acceptance Criteria
- [ ] CrewType-Enum erweitert: FORSCHER, ENGINEER, DIPLOMAT (Captain bleibt T-104a)
- [ ] Akademie-Building (T-104a) trainiert auch Non-Combat-Crew (eigener Cooldown)
- [ ] Officer-Quarters-Cap deckt alle Crew-Types
- [ ] Forscher-Effekt: assigned an Lab → Lab-Output × (1 + 0.05 × level), max +30%
- [ ] Engineer-Effekt: assigned an Schiff → Maintenance-Cost × (1 - 0.05 × level), max -30%
- [ ] Diplomat-Effekt: assigned an Faction → ReputationService.changeReputation × (1 + 0.05 × level)
- [ ] Crew-Permadeath nur bei Combat-Loss (Forscher/Engineer/Diplomat sterben nicht zufällig)

## Affected Tests
- tests/Crew/Service/ForscherLabBoostTest.php
- tests/Crew/Service/EngineerShipMaintenanceTest.php
- tests/Crew/Service/DiplomatReputationBoostTest.php

## Fixtures Needed
Yes — Crew-Pools pro Rolle

## Notes
- Spy für T-131 separat — komplexere Mission-Logik
- Crew-Counter: kombiniert mit Captains gegen Officer-Quarters-Cap → Spieler muss priorisieren
