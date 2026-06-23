# T-068 Defense-Buildings (Schild, Geschütze, Radar)

**Type:** Feature
**Epic:** Combat & Defense
**Domain:** Building
**Blocked By:** T-065, T-067, T-103
**Status:** Blocked (by T-103 — Battle-Resolution-Engine definiert Defense-Stats-Contract)
**Effort:** L
**Depends on:** T-065 (Energy), T-067 (Erzeugnis-Tree), T-103 (Battle-Resolution-Engine — Foundation für Defense-Stats-Konsum)
**Blocks:** T-081 (Heimat-Schutz)

## Beschreibung
Planet-Defense gegen NPC-Angriffe. PvE only — kein Spieler-Spieler-Damage.

Buildings:
- Planetary-Shield: HP-Buffer für Planet (absorbiert Damage vor Pop/Building-Loss)
- Defense-Turret: Damage-Output während Defense-Battle
- Sensor-Array (Radar): Reduziert Surprise-Faktor von Pirat-Spawns, Vorwarnzeit
- AA-Battery: Anti-Schiff-Defense (small/medium/large Schiffsklasse)

## Acceptance Criteria
- [ ] BuildingType::PLANETARY_SHIELD, DEFENSE_TURRET, SENSOR_ARRAY, AA_BATTERY
- [ ] Planet bekommt `getDefenseStats(): DefenseStatsVO` (shield, damage, sensor-range)
- [ ] DefenseStatsVO live-computed aus Buildings × Level
- [ ] Battle-Resolution-Engine (T-103) liest DefenseStatsVO bei Defense-Battle
- [ ] Pop-Bedarf pro Defense-Building (manning)
- [ ] Power-Consumption hoch (Shield 100/lvl, Turret 30/lvl, Radar 20/lvl, AA 40/lvl)
- [ ] Building-Damage-State: Defense-Buildings sind im Battle zerstörbar (HP-Pool, regeneriert nach Battle 7d)

## Affected Tests
- tests/Planet/Model/DefenseStatsTest.php
- tests/Battle/Service/PlanetDefenseTest.php (sobald T-103 ready)

## Fixtures Needed
Yes — Test-Planet mit Defense-Buildings für Battle-Tests

## Notes
- Defense-Buildings nur defensiv, kein Offensive-Use
- Shield-HP regeneriert in 24h (live-computed mit lastDamageAt-Timestamp)
- Radar gibt Notification wenn Pirat-Flotte spawnt im System
