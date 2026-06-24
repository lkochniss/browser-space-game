# Combat

T-103 Foundation Battle-Resolver. PvE-only (NPC-vs-Player + Player-vs-NPC im
selben System). PvP wird in einem späteren Epic geöffnet.

## Battle-Entity

`App\Battle\Model\Battle` — persistiert nur End-Resultat (Status + Round-Count
+ Timestamps). Round-by-Round Replay-Log ist T-103d Out-of-Scope.

Felder:

| Feld | Wirkung |
|------|---------|
| `attacker: ?Player` | Angreifer (NULL für NPC-Player) |
| `attackerFleet: ?Fleet` | Fleet die Angriff initiiert |
| `defenderFleet: ?Fleet` | Defender-Fleet ODER |
| `defenderPlanet: ?Planet` | Planet-Defense-Target (mit T-068 Stats) |
| `location: ?SolarSystem` | System wo der Battle stattfindet |
| `status: BattleStatus` | RUNNING / ENDED_ATTACKER_WIN / ENDED_DEFENDER_WIN / DRAW |
| `rounds: int` | Anzahl gespielter Rounds (0..10) |

Genau eines der beiden Defender-Felder ist gesetzt — `InvalidBattleTargetException`
sonst.

## Round-Engine (BattleResolver)

Synchroner Resolver. Loop bis max 10 Rounds oder eine Seite leer:

1. `attackerDamage = Σ ship.effectiveDamage` über alive Ships (mit Captain-Boost)
2. `defenderDamage = Σ ship.effectiveDamage` + Planet-`turretDamage + aaDamage` (T-068)
3. Damage gegen Defender:
   - Wenn `shieldHp > 0`: absorbed first, rest auf Ships
   - Pro alive Ship: `damage_per_ship = floor(remaining / N_alive)`
4. Damage gegen Attacker: gleich, aber ohne Shield-Buffer (asymmetrisch
   zugunsten Defender)
5. Kill-Marker: `Ship.battleCurrentHp <= 0` → `em->remove($ship)`
6. Captain-Permadeath-Roll pro Killed Ship
7. Increment Round + Win-Check

Round-Limit (10) ohne Sieger → DRAW.

## Captain-Stats + Permadeath

`Ship::effectiveDamage = blueprint.damage × Crew.getStatsMultiplier()`. Captain
L10 = ×1.30 Damage. Crew-Boost ist nur aktiv wenn `Crew.status = ASSIGNED`.

Beim Schiff-Loss:

- `BattleRandomizer::roll() < ship.escapePodSurvivalChance` →
  Captain.unassign() (status=IDLE, assignedShip=null)
- sonst → `Captain.markDead()` (status=DEAD, assignedShip=null)

Pod-Chance per ShipClass-Familie (T-102):
Frigate 30 / Destroyer 50 / Cruiser 65 / Battleship 80 / Carrier 70.

`BattleRandomizer` ist injectable → Tests können mocken (roll=99 = stets fail,
roll=0 = stets survive).

## Defense-Buildings im Battle (T-068 Konsum)

`Planet::getDefenseStats($now)` liefert pro-Round `shieldHp` (live), `turretDamage`,
`aaDamage`, `sensorRange`. Resolver:

- Snapshotted `shieldHp` zu Battle-Start, reduziert ihn pro Round bei Defender-
  Treffer (Shield absorbiert vor Ship-Damage)
- Addiert `turretDamage + aaDamage` zur Defender-Side-Damage-Bilanz
- Wenn `Building.currentHp = 0` (durch Battle-Damage), zählt das Building nicht
  mehr zu `DefenseStats` — `Planet::getDefenseStats` filtert via `isOperational`

Foundation: `shieldHp` wird intern im Resolver tracked, NICHT auf
PLANETARY_SHIELD-Building.currentHp persistiert. T-103-Folge-Ticket (oder T-103d
Replay) kann das ergänzen.

## StartBattleCommand

`StartBattleCommand(attackerFleetId, defenderFleetId?, defenderPlanetId?)`:

- Validation:
  - genau eines der beiden Defender-Felder gesetzt
  - Attacker-Fleet non-empty
  - Defender-Fleet non-empty (wenn gesetzt)
  - Attacker + Defender im selben SolarSystem
- Persist Battle-Entity (status=RUNNING)
- Synchron `BattleResolver::resolve($battle)` aufrufen

## Out of Scope (Folge-Tickets)

| Ticket | Scope |
|--------|-------|
| T-103b | Tactic-RPS-System (Front-Assault/Flanking/Hit-Run/Standoff ×1.3/×0.7) |
| T-103c | NPC-AI-Tactic-Heuristik |
| T-103d | Battle-Replay-Log-Persistence (Round-by-Round Events) |
| T-103e | Loot-Drop-Trigger (T-080 Integration) |
| T-088 | Munition-Verbrauch (BALLISTIC_AMMO/WARHEAD/PLASMA_CHARGE) |
| T-104b | Captain-Skill-Trees (Beam-Master/Missile-Spec/Shield-Tactician) |

## Files

- `src/Battle/Model/Battle.php`
- `src/Battle/ValueObject/{BattleId,BattleStatus,DefenseStats}.php`
- `src/Battle/Service/{BattleResolver,BattleRandomizer,StartBattleCommandService}.php`
- `src/Battle/Command/StartBattleCommand{,Handler}.php`
- `src/Battle/Repository/BattleRepository.php`
- `src/Battle/Exception/InvalidBattleTargetException.php`
- Ship-Erweiterung: `Ship.battleCurrentHp` (T-103 Battle-State zwischen Rounds)

## Cross-Domain

| Domain | Wirkung |
|--------|---------|
| Ship (T-012/T-102) | Schiffe sind die Combat-Units; ShipBlueprint liefert hp/damage |
| Crew (T-104a) | Captain-Stats-Boost + Permadeath-Roll |
| Building (T-068) | Defense-Buildings (Shield/Turret/AA/Sensor) für Planet-Defense |
| Fleet (T-017) | Battle läuft zwischen Fleets im selben System |
| Planet (T-068) | `getDefenseStats` liefert Defender-Stats |
