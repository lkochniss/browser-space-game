# T-081 Heimat-Schutz (Anti-Crush-Foundation)

**Type:** Feature
**Status:** Draft
**Effort:** M
**Depends on:** T-007 (SolarSystem), T-103 (Battle)
**Blocks:** —

## Beschreibung
Start-Planet hat strukturelle Anti-Crush-Garantien. Ziel: kein Spieler verliert sein gesamtes Setup nach einer einzelnen verlorenen Schlacht. PvE-Loss-Cap.

## Acceptance Criteria
- [ ] Planet bekommt `isHomePlanet: bool` (gesetzt bei Start-Claim T-046)
- [ ] HomePlanet ist `immortal`: kann nicht zerstört werden, kein Settlement-Wechsel erzwingbar
- [ ] Resource-Vault: 30% jeder Resource auf Heimat ist nicht raubbar (live-computed, kein DB-Field)
- [ ] Pop-Loss-Cap: max 10% Pop-Loss pro Defense-Battle auf Heimat (clamp im Battle-Resolver)
- [ ] Building-Damage-Cap: max 1 Defense-Building zerstört pro Battle (verhindert Total-Wipe)
- [ ] Schild-Cooldown: Nach Defense-Battle 24h Schild-Reload — kein Stacked-Attack möglich
- [ ] Notifications (T-161) bei Heimat-Attack inkl. Vorlauf via Sensor-Array (T-068)

## Affected Tests
- tests/Planet/Service/HomePlanetProtectionTest.php
- tests/Battle/Service/HomeBattleLossCapTest.php

## Fixtures Needed
Yes — Player mit HomePlanet + Defense-Buildings + Pop

## Notes
- Sekundäre Planeten (kolonisiert) haben keine Anti-Crush-Garantien — strategischer Trade-off
- Vault-Mechanik live-computed: schützt vor Loot-Greife direkt im LootRollService (T-080)
- Kein Multi-Heimat-Switch zur Exploit-Vermeidung; HomePlanet ist permanent gesetzt
