# T-074 Pirat-Encounter-Spawn

**Type:** Feature
**Status:** Draft
**Effort:** L
**Depends on:** T-073 (Faction-Foundation), T-007 (SolarSystem), T-017 (Flotte)
**Blocks:** T-077 (World-Boss), T-099 (Threat-Skalierung)

## Beschreibung
Pirat-Flotten spawnen passiv in Systemen. Random-Encounter beim Spieler-Schiff-Bewegung oder als statisches POI in Systemen.

PvE-Quelle: Combat ohne andere Spieler. Loot + Reputation-Gain (Pirate-Faction sinkt, Imperium-Faction steigt — aber Pirate ist always-hostile, Tier bleibt HOSTILE).

## Acceptance Criteria
- [ ] PirateEncounter-Entity (Position System+Coords, FleetComposition, expiresAt)
- [ ] Spawn-Service: pro Tick X% Chance pro System für Pirat-Spawn (System-Level/Spieler-Score abhängig)
- [ ] Pirat-Flotte ist mit `factionId = pirate_consortium` markiert
- [ ] Spieler-Schiff-Movement durchquert System mit Pirat → triggert Encounter (auto-attack)
- [ ] Encounter-Resolution via T-103 Battle-Engine
- [ ] Loot bei Sieg: T-080 Loot-Tabelle (vorausgesetzt T-080 done)
- [ ] Pirat-Flotte despawnt nach 7 Tagen wenn nicht engaged

## Affected Tests
- tests/Faction/Service/PirateEncounterSpawnTest.php
- tests/Battle/Service/PirateEncounterResolutionTest.php (sobald T-103 ready)

## Fixtures Needed
Yes — Test-Systems, Player-Score-Setup

## Notes
- Spawn-Rate: Bubble-Phase 0% (T-150), nach Bubble graduell hoch
- Pirat-Stärke skaliert mit Player-Score (T-099)
- Bewegung-Schiff-Detection auch via Sensor-Array (T-068) — Vorwarnzeit
