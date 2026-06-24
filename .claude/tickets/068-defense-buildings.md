# T-068 Defense-Buildings (Schild, Geschütze, Radar)

**Type:** Feature
**Epic:** Combat & Battle
**Domain:** Building
**Blocked By:** T-065, T-067
**Status:** Done
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

- [x] `BuildingType::PLANETARY_SHIELD`, `DEFENSE_TURRET`, `SENSOR_ARRAY`,
      `AA_BATTERY` enum-cases
- [x] `isUnique()`: PLANETARY_SHIELD + SENSOR_ARRAY = true
- [x] `getSlotSize()`: PLANETARY_SHIELD + SENSOR_ARRAY = 2, andere = 1
- [x] `getVolumeContribution()`: alle 4 = 0 (Defense-Gebäude, kein Storage)
- [x] `getPowerConsumption(level)`: SHIELD 100/Lvl, TURRET 30/Lvl,
      SENSOR 20/Lvl, AA 40/Lvl (heavy Consumer — Late-Game-Bottleneck)
- [x] BuildingCostConfig + BuildingDurationConfig hinzugefügt
- [ ] BuildingUnlockConfig (Research-Gate) — _deferred: kein dedicated
      `defense`-Node existiert heute; Foundation lässt Defense-Buildings
      gated nur via Resource-Tier (STEEL/CHIP/COMPOSITE) — Research-Gate
      kommt mit T-128 oder dediziertem `defense_basics`-Node_

### Defense-Stats-VO + Live-Computed

- [x] `App\Battle\ValueObject\DefenseStats` readonly-VO (shieldHp/Max +
      turretDamage + sensorRange + aaDamage)
- [x] `Planet::getDefenseStats($now): DefenseStats` — live aus Buildings × Level,
      nur READY + currentHp > 0 (`isOperational`)
- [x] Damaged + Unfinished Buildings ausgeschlossen — Test deckt das ab

### HP-State auf Building

- [x] `Building::currentHp: int` Doctrine field; `maxHp` via `computeMaxHp()`
      live aus Level × `BuildingType::getMaxHpPerLevel()`
- [x] `Building::computeMaxHp(): int` = `level × type.getMaxHpPerLevel()`
- [x] Beim Build-Complete: currentHp = computeMaxHp (BuildBuildingService
      ruft `restoreFullHp()` direkt nach Createt)
- [x] Beim Upgrade: HP wird auf neuen Max-Wert restored
- [x] Migration `Version20260624000002` für `current_hp` + `last_repair_at`

### Repair-Mechanik

- [x] `RepairBuildingCommand(planetId, buildingId)` + Handler + Service
- [x] Validation: building damaged, 24h Cooldown via `lastRepairAt`
- [x] Cost = 30% der existing BuildingCostConfig-Base-Resources (kein Pop)
- [x] Effekt: currentHp = maxHp, lastRepairAt = now
- [ ] Demo-CLI Action "Repair Building" — _deferred: nicht für Foundation
      kritisch, kommt mit T-103 Battle-Demo-Action zusammen_

### Sensor-Range Helper

- [x] `Player::hasSensorInSystem(SolarSystem $sys, int $range, ?$now): bool`
- [x] Iteriert Player-Planeten, prüft same-system + `SENSOR_ARRAY.level >= range`
- [ ] T-007 Galaxy-Map-Adjacency Cross-System-Range — _deferred: Foundation
      same-system only; Cross-System mit T-007b_

### Tests

- [x] `PlanetDefenseStatsTest` (7): shield/turret/sensor/aa-Stats korrekt
      summiert, damaged + unfinished ausgeschlossen
- [x] `RepairBuildingCommandServiceTest` (4): Resource-Cost, Cooldown,
      currentHp restore, Undamaged-Block
- [x] `SensorRangeTest` (4): same-system, cross-system, destroyed sensor

### Docs

- [x] `buildings.md` Defense-Sektion (4 Buildings + Stats-Tabelle + Repair +
      Sensor-Range)
- [x] `decisions.md` Eintrag T-068
- [ ] `combat.md` für T-103 Konsum-Contract — _deferred: T-103-Implementation
      legt combat.md als Top-Level-Doc an, mit T-068 Defense-Section verlinkt_

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

### Implementation Tokens (estimate)
- Input: ~150k
- Output: ~16k

### Deferred / Follow-Ups

- Research-Gate für Defense-Buildings (kein `defense`-Node heute)
- Demo-CLI "Repair Building"-Action (Foundation-Demo deckt's nicht ab)
- Cross-System-Sensor-Range via T-007b Galaxy-Adjacency
- `combat.md` Top-Level-Doc → T-103-Implementation
- `DefenseFixture` (Tests bauen inline)
