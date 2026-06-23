# T-131 Spy-System Foundation (Recon + Sabotage gegen NPC-Outposts)

**Type:** Feature
**Epic:** NPC Factions
**Domain:** Faction
**Blocked By:** T-073, T-075, T-104c
**Status:** Draft
**Effort:** L
**Depends on:** T-073 (Faction), T-075 (Outposts), T-104c (Crew, Spy-Rolle)
**Blocks:** T-113 (Black-Market)

## Beschreibung
Spy-Foundation: Spy-Network-Building + 2 Mission-Types. **PvE only — KEIN Spielerspion** (kein Spieler kann anderen Spieler ausspionieren/sabotieren).

Mission-Types:
- **Recon**: Sammelt Detail-Intel zu Outpost (HP, Fleet-Comp, Loot-Estimates) — visible in Galaxy-Map
- **Sabotage**: Schwächt NPC-Outpost (HP -20%, Re-Generate-Pause 7d)

Folge: Heist + Infiltration für komplexere Missions.

## Acceptance Criteria
- [ ] BuildingType::SPY_NETWORK (auf Heimat-Planet, max 1)
- [ ] CrewType::SPY (T-104c-Pattern: Akademie trainiert Spy)
- [ ] SpyMission-Entity (id, playerId, type, targetEntityId (Outpost), assignedSpy, status, scheduledEndAt, result-JSON)
- [ ] Recon-Mission: 24h, Spy bound, Erfolg-Rate 80% (skaliert mit Spy-Level)
- [ ] Sabotage-Mission: 48h, Spy bound, Erfolg-Rate 50%, Failure-Mode → Spy verloren (Permadeath)
- [ ] Anti-Spieler-Lock: Mission-Target-Validation refuses jegliche Player-Entity (nur NPC-Faction-Targets)
- [ ] Renegade-Mission-Path: Spy-Missions gegen Imperium-Outposts (zukünftige T-XXX) → Renegade-Rep-Gain (Foundation für T-113)

## Affected Tests
- tests/Spy/Service/ReconMissionTest.php
- tests/Spy/Service/SabotageMissionTest.php (success + failure)
- tests/Spy/Service/SpyAntiPlayerLockTest.php (rejects player target)

## Fixtures Needed
Yes — Spy-Network + Spies + Outposts

## Notes
- Permadeath bei Sabotage-Failure: macht Spy-Spam riskant
- Renegade-Rep-Path absichtlich versteckt: Spieler entdecken erst beim Test
- "PvE only" hard-locked: Type-Validation verhindert Player-Targets per Construction
