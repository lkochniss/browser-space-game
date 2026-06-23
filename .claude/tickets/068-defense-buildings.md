# T-068 Defense-Buildings (Schild, Geschütze, Radar)

**Type:** Feature
**Epic:** Combat & Battle
**Domain:** Building
**Blocked By:** T-065, T-067
**Status:** Ready
**Effort:** L
**Depends on:** T-065 (Ready), T-067 (Done)
**Blocks:** T-081 (Heimat-Schutz), T-103 (Battle-Resolution-Engine konsumiert Defense-Stats)

## Beschreibung

Planet-Defense gegen NPC-Angriffe (PvE-only — kein Spieler-vs-Spieler-Damage).

**Buildings:**
- `PLANETARY_SHIELD` — Shield-HP-Buffer (absorbiert Damage vor Pop/Building-Loss)
- `DEFENSE_TURRET` — Damage-Output während Defense-Battle
- `SENSOR_ARRAY` — Vorwarnung bei Pirat-Spawns
- `AA_BATTERY` — Anti-Schiff-Defense

## Resolved Decisions

- **Q1 Damage-State (d):** Jedes Defense-Building hat `currentHp` + `maxHp`.
  Damage in Battle reduziert HP. Bei HP=0 → deactivated (kein Defense-Beitrag).
  **Repair-Mechanik:** Player muss aktiv Resources zahlen + 24h Cooldown
  → Building zurück auf 100% HP. Keine Auto-Regeneration. Repair-Cost = 30%
  der Initial-Build-Cost.
- **Q2 Defense-Stats (Vorschlag locked):**
  | Building | Stats |
  |----------|-------|
  | PLANETARY_SHIELD | +5000 Shield-HP/Lvl, maxHp = 100/Lvl |
  | DEFENSE_TURRET | +500 Damage/Lvl, maxHp = 200/Lvl |
  | SENSOR_ARRAY | +1 Sensor-Range (Systems)/Lvl, maxHp = 100/Lvl |
  | AA_BATTERY | +300 Anti-Ship-Damage/Lvl, maxHp = 150/Lvl |
- **Q3 Uniqueness (Hybrid):** PLANETARY_SHIELD + SENSOR_ARRAY = strikt-unique
  (1× pro Planet, isUnique=true, Slot-Size 2). DEFENSE_TURRET + AA_BATTERY =
  multi-instance (stackable, Slot-Size 1). Volume-Defense via Turret/AA
  möglich; Shield/Radar stay single + level-scaled.
- **Q4 Sensor-Notification:** T-074 (Pirate-Spawn) Hook reads
  `Player::hasSensorInSystem(Planet $p, int $range)`. Bei Spawn-Event:
  T-074 dispatcht Notification via T-161 falls Player in Sensor-Range.
  T-068 stellt nur die Range-Stat; Dispatch ist T-074-Logic.

## Acceptance Criteria

### BuildingType + Config

- [ ] `BuildingType::PLANETARY_SHIELD`, `DEFENSE_TURRET`, `SENSOR_ARRAY`,
      `AA_BATTERY` enum-cases
- [ ] `isUnique()`: PLANETARY_SHIELD + SENSOR_ARRAY = true
- [ ] `getSlotSize()`: PLANETARY_SHIELD + SENSOR_ARRAY = 2, andere = 1
- [ ] `getVolumeContribution()`: alle 4 = 0 (Defense-Gebäude, kein Storage)
- [ ] `getPowerConsumption(level)`: SHIELD 100/Lvl, TURRET 30/Lvl,
      RADAR 20/Lvl, AA 40/Lvl (heavy Consumer — Late-Game-Bottleneck)
- [ ] BuildingCostConfig + BuildingDurationConfig + BuildingUnlockConfig
      (alle via shipbuilding-Research-Branch L1, oder dedicated `defense`-Node)

### Defense-Stats-VO + Live-Computed

- [ ] `App\Battle\ValueObject\DefenseStats` readonly-VO:
  - `shieldHp: int`, `shieldHpMax: int`
  - `turretDamage: int`
  - `sensorRange: int`
  - `aaDamage: int`
- [ ] `Planet::getDefenseStats($now): DefenseStats` — live aus Buildings × Level,
      nur READY + currentHp > 0
- [ ] Bei `building.currentHp == 0` zählt das Building NICHT mehr zu DefenseStats

### HP-State auf Building

- [ ] `Building::currentHp: int`, `Building::maxHp: int` (Doctrine fields)
- [ ] `Building::computeMaxHp(): int` = `level × type.getMaxHpPerLevel()`
- [ ] Beim Build-Complete: currentHp = computeMaxHp
- [ ] Migration für neue Spalten + Backfill (currentHp = maxHp für existing buildings)

### Repair-Mechanik

- [ ] `RepairBuildingCommand(buildingId)` + Service
- [ ] Validation: building damaged (currentHp < maxHp), 24h Cooldown nicht aktiv
- [ ] Cost = 30% der existing BuildingCostConfig-Resources (auf currentLevel)
- [ ] Effekt: currentHp = maxHp, lastRepairAt = now
- [ ] Demo-CLI Action "Repair Building"

### Sensor-Range Helper

- [ ] `Player::hasSensorInSystem(SolarSystem $sys, int $range): bool`
- [ ] Iteriert Player-Planeten, prüft `SENSOR_ARRAY.level >= 1` UND
      `sys` ist in `range` adjacenten Systemen (T-007 Galaxy-Map-Adjacency)
- [ ] T-074 nutzt das beim Spawn — T-068 owns nur die Read-API

### Tests

- [ ] `DefenseStatsTest`: shield/turret/radar/aa Stats korrekt summiert,
      damaged building ausgeschlossen
- [ ] `DefenseBuildingDamageTest`: Battle-Damage reduziert currentHp,
      currentHp=0 disables Defense-Beitrag
- [ ] `RepairBuildingTest`: Resource-Cost, Cooldown, currentHp restore
- [ ] `SensorRangeTest`: hasSensorInSystem für diverse Range-Setups

### Docs

- [ ] `buildings.md` Defense-Sektion (4 Buildings + Stats-Tabelle + Repair)
- [ ] `decisions.md` Eintrag T-068
- [ ] `combat.md` (neu, falls noch nicht) — Defense-Stats-Konsum-Contract
      für T-103 dokumentieren

## Fixtures Needed

Yes — `DefenseFixture` mit Test-Planet mit allen 4 Defense-Buildings auf L1-L5
für Battle-Tests (T-103 nutzt das später).

## Notes

- T-103 Battle-Engine LIEST `getDefenseStats()` — Defense-Side-Damage-Pool
- Shield-HP regeneriert NICHT auto (Q1 Decision: Repair-Cost). Player muss
  proaktiv Repair-Resource ausgeben
- Sensor-Range nur ein Helper-Read; T-074 Pirate-Spawn-Service triggert die
  Notification (Hook bleibt in T-074-Implementation)

### Refinement Tokens (estimate)
- Input: ~6k
- Output: ~3k
