# Crew (T-104a Foundation)

Captain als limited Resource für Combat-Schiffe. Akademie produziert Crew,
Officer-Quarters cappt die Gesamtzahl.

## CrewType (T-104a)

| Type | Foundation | Folge |
|------|------------|-------|
| `CAPTAIN` | T-104a Done | Skill-Trees → T-104b |
| `ENGINEER` | — | T-104c |
| `DIPLOMAT` | — | T-104c |
| `SPY` | — | T-131 |

## CrewStatus

```
TRAINING → IDLE → ASSIGNED → IDLE → DEAD
         (Tick)            (Battle-Loss ohne Escape-Pod)
```

| Status | Effekt |
|--------|--------|
| `TRAINING` | Wallclock-Akademie; `trainingFinishedAt` ≤ now → IDLE |
| `IDLE` | Verfügbar, ohne Ship; zählt zum Cap |
| `ASSIGNED` | Auf Ship; Stats-Multiplier `1 + 0.03 × level` aktiv |
| `DEAD` | Permanente nach Schiff-Loss; zählt NICHT mehr im Cap |

## Cap-Mechanik

- `OFFICER_QUARTERS` Building: **5 Crew-Slots / Level** (multi-instance, Slot-2)
- `Player::getCrewCap($now)` summiert über alle ready Officer-Quarters auf
  allen Planeten
- Cap deckt ALLE Crew-Types (Captain heute, T-104c Engineer/Diplomat shared)

## Training

`StartCrewTrainingCommand(playerId, type)` → `StartCrewTrainingCommandService`:
1. Player existiert
2. Mindestens eine ready `ACADEMY` (Player::hasAnyAcademy)
3. Cap-Check via `getCrewCap()` (Q3 ohne Type-Differenzierung)
4. Duration = `CrewType::getTrainingDurationSeconds(existingCountOfType)`:
   - Captain: `3600 × 2^count` (60min × 2^N)
5. Crew::startTraining → Status TRAINING, finishedAt fixed

`CrewTrainingCompletionService::runTick()` (global, vom Tick-Loop gerufen):
- iteriert alle TRAINING-Crew
- bei `finishedAt ≤ now` → `completeTraining()` → IDLE

## Level-Up (XP-System)

```php
// Threshold-Tabelle (cumulative)
L1=0, L2=100, L3=250, L4=500, L5=1000, L6=1750, L7=2500, L8=3250, L9=4000, L10=5000
```

- Battle-XP-Hook (T-103 Folge): nach Battle-Survival +XP basierend auf Enemy-Power
- `BoostCrewCommand`: Player gibt 500 IRON_BAR + 100 CHIP → +500 XP, 24h Cooldown

## Assignment

`AssignCrewCommand(crewId, shipId)` → `AssignCrewCommandService`:
- Crew status IDLE
- Captain-Type: Schiff hat keinen anderen Captain
- `crew.assignToShip($ship)` → Status ASSIGNED

`UnassignCrewCommand(crewId)`: Status → IDLE, assignedShip = null.

## Stats-Multiplier auf Ship

Bei ASSIGNED-Captain:
```
ship.effectiveDamage = base × (1 + 0.03 × captain.level)
ship.effectiveHp     = base × (1 + 0.03 × captain.level)
ship.effectiveShield = base × (1 + 0.03 × captain.level)
```

Battle-Engine (T-103) liest `Crew::getStatsMultiplier()` für ASSIGNED-Crew des Schiffs.

## Permadeath + Escape-Pod (T-104a Q4 + T-102 Q3)

Bei Schiff-Loss (T-103 Battle-Resolver):
1. `ship.shipClass.escapePodSurvivalChance` per Class (T-102):
   - Frigate 30%, Destroyer 50%, Cruiser 65%, Battleship 80%, Carrier 70%
   - Existing ShipTypes (Generic/Cargo/Salvage): 0%
2. `random(100) < chance` → Captain.status = IDLE, ship-loss überlebt
3. sonst → Captain.status = DEAD (nicht mehr im Cap)

T-103 Battle-Resolver implementiert den Roll; T-104a stellt `markDead()` API.

## Buildings (T-104a)

| Building | Slot-Size | Cost | Duration | Unique |
|----------|-----------|------|----------|--------|
| `ACADEMY` | 2 | 300 IRON_BAR + 80 SILICON + 30 Pop | 60min | non-unique |
| `OFFICER_QUARTERS` | 2 | 200 IRON_BAR + 50 COPPER_ORE + 20 Pop | 40min | non-unique |

Beide multi-instance — Player kann Capacity skalieren.

## Demo-CLI Actions

- `Crew: Train Captain` — startet Training (Akademie-/Cap-Check)
- `Crew: Assign to Ship` — IDLE-Captain → Schiff binden
- `Crew: Boost` — 500 IRON_BAR + 100 CHIP → +500 XP (24h Cooldown)
- Tick-Forward zeigt `Crew-trained: N` (Wallclock-Completions)

## Files

- `src/Crew/Model/Crew.php` (Entity, XP-System, Stats-Multi)
- `src/Crew/ValueObject/{CrewId, CrewType, CrewStatus}.php`
- `src/Crew/Repository/CrewRepository.php`
- `src/Crew/Service/{StartCrewTraining,CrewTrainingCompletion,AssignCrew,BoostCrew}CommandService.php`
- `src/Crew/Command/{StartCrewTraining,AssignCrew,UnassignCrew,BoostCrew}Command(+Handler).php`
- `src/Crew/Exception/{CrewCapReached,MissingAcademy,CrewNotFound,CrewNotIdle,ShipAlreadyHasCaptain,BoostCooldownActive}Exception.php`
- `src/Common/Doctrine/Type/CrewIdType.php`
- `migrations/Version20260623000001.php` (crew table)

## Cross-Domain

- **Building**: ACADEMY + OFFICER_QUARTERS BuildingTypes (T-104a)
- **Ship**: Crew.assignedShip ManyToOne; ShipType::getEscapePodSurvivalChance Stub
- **Player**: Player::getCrewCap, Player::hasAnyAcademy
- **Resource**: BoostCrew consumes IRON_BAR + CHIP aggregated über alle Player-Planeten
- **Battle (T-103 Folge)**: Stats-Multi via Crew::getStatsMultiplier; Permadeath via Crew::markDead

## Geplant

- **T-104b** Captain-Skill-Trees (free-allocation, strict Tier-Lock)
- **T-104c** Engineer + Diplomat (Forscher gestrichen via T-025c)
- **T-103** Battle-Resolver konsumiert Stats-Multi + dispatches Permadeath-Roll
- **T-131** Spy-Crew (komplexe Mission-Logik)
