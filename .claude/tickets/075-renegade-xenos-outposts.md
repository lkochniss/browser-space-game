# T-075 Renegade- und Xenos-Outposts

**Type:** Feature
**Epic:** NPC Factions
**Domain:** POI
**Blocked By:** T-073, T-019
**Status:** Draft
**Effort:** L
**Depends on:** T-073 (Faction), T-019 (POI-System)
**Blocks:** T-077 (World-Boss), T-080 (Loot)

## Beschreibung
Statische POI-Threats in Galaxy. Anders als Pirat-Random-Encounters: Outposts haben feste Position, persistente Threat, müssen aktiv angegriffen werden.

Outpost-Types:
- Renegade-Stronghold: Mid-Tier-Threat, Loot von Imperialer-Tech (Renegades = abtrünnige Menschen)
- Xenos-Hive: Higher-Tier-Threat, Loot von Xenos-Tech (Tier-3-Resources)
- Xenos-Wormhole-Gate: Spawnt regelmäßig Xenos-Reinforcements wenn nicht zerstört

## Acceptance Criteria
- [ ] OutpostPOI-Entity (Position System, FactionId, FleetComposition, currentHp, lastDamagedAt)
- [ ] Outpost-Spawn auf Galaxy-Init: zufällig pro System mit X% Chance
- [ ] Outpost-Typen mit Threat-Level (1-10, T-099-skaliert)
- [ ] Spieler kann Outpost mit Flotte angreifen (T-103 Battle)
- [ ] Outpost-HP regeneriert wenn nicht zerstört (24h)
- [ ] Loot: T-080 Drop-Tabelle pro Faction
- [ ] Wormhole-Gate spawnt alle 7 Tage Xenos-Reinforcement-Flotte im System
- [ ] Outpost-Position auf Galaxy-Map sichtbar (T-160)

## Affected Tests
- tests/Faction/Service/OutpostSpawnTest.php
- tests/Battle/Service/OutpostAttackTest.php

## Fixtures Needed
Yes — Test-Galaxy mit Outposts seeded

## Notes
- Outposts sind shared-PvE-Content: mehrere Spieler können nacheinander angreifen oder kooperieren
- HP-Pool groß genug dass Solo-Spieler mehrere Sessions braucht
