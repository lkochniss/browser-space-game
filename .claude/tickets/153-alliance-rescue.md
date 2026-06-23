# T-153 Allianz-Rescue-Mechanik

**Type:** Feature
**Epic:** Multiplayer
**Domain:** User
**Blocked By:** T-052, T-105
**Status:** Draft
**Effort:** M
**Depends on:** T-052 (Allianz), T-105 (Schiff-Maintenance, Stranded-State)
**Blocks:** —

## Beschreibung
Folge-Ticket nach Allianz + Schiff-System fertig. Allianz-Members können einander aktiv aushelfen wenn:
- Schiff stranded (T-105 Treibstoff-Mangel) → Rescue-Mission liefert Treibstoff/Supplies
- Heimat unter Heavy-Attack → Allianz-Member entsendet Defense-Reinforcement

## Acceptance Criteria
- [ ] RescueMission-Entity (id, requesterPlayerId, targetEntity (Schiff/Planet), responderPlayerId, status, payload-JSON)
- [ ] StrandedShipRescue: anderer Allianz-Member sendet Tankschiff → Treibstoff-Refill, Schiff fliegt heim
- [ ] DefenseReinforcement: Allianz-Member sendet Combat-Flotte zur Heimat → joint Defense-Battle (T-103)
- [ ] Trigger nur via expliziten Rescue-Request (kein Auto-Rescue, vermeidet Exploit)
- [ ] Cooldown: 24h zwischen Rescue-Requests pro Player
- [ ] Notification an alle Allianz-Members bei Rescue-Request (T-161)
- [ ] Rescue-Stats werden in PlayerStats (T-096) getrackt — Cosmetic-Achievement-Fodder

## Affected Tests
- tests/Alliance/Service/StrandedRescueTest.php
- tests/Alliance/Service/DefenseReinforcementTest.php
- tests/Alliance/Service/RescueCooldownTest.php

## Fixtures Needed
Yes — Allianz mit Multi-Members + verschiedenen Rescue-Scenarios

## Notes
- Foundation für "Allianz als Force-Multiplier" (Decision)
- Anti-Exploit: Cooldown verhindert "free fuel via friend" loops
