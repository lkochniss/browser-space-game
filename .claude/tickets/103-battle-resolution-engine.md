# T-103 Battle-Resolution-Engine (Foundation)

**Type:** Feature
**Epic:** Combat & Battle
**Domain:** Ship
**Blocked By:** T-102, T-068
**Status:** Ready
**Effort:** L (~6-8h)
**Depends on:** T-102 (Ready), T-068 (Ready)
**Blocks:** T-103b, T-103c, T-103d, T-103e, T-074, T-075, T-077, T-080, T-104b

## Beschreibung

**Foundation-Only Battle-Resolver.** Round-by-Round Combat zwischen 2 Fleets.
Damage-Model = Ship-Blueprint-Stats (T-102) + Defense-Buildings (T-068) +
Captain-Bonus (T-104a).

**Splits aus original T-103** (siehe Folge-Tickets):
- **T-103b** Tactic-RPS-System (Front-Assault/Flanking/Hit-Run/Standoff + ×1.3/×0.7 Counter)
- **T-103c** NPC-AI-Tactic-Heuristik
- **T-103d** Battle-Replay-Persistence (für T-164 UI)
- **T-103e** Loot-Trigger-Hook (T-080 Integration)

T-103 = nackte Round-Engine ohne Tactic, NPC-AI, Replay-Log, Loot.

## Resolved Decisions (Foundation)

1. **Enemy-Fleet-Sourcing:** Sandbox-Path (CLI) + Mini-NPC-Spawn (1 NPC-Ship
   pro System via Demo-Garantie).
2. **Damage-Modell:** Pro Schiff HP-Pool + Damage-Stat. Round = Σ Damage
   gleichmäßig auf Gegner-Schiffe verteilt; Schiff stirbt bei HP ≤ 0.
3. **Round-Limit:** Max 10 Rounds. Beide alive nach 10 → DRAW.
4. **Permadeath:** Zerstörte Schiffe `em->remove()`. Captain-Roll per T-104a +
   T-102 escapePodChance.
5. **Shield-Absorption:** PLANETARY_SHIELD HP absorbiert Damage VOR
   Pop/Building-Loss bei Planet-Defense.

## Acceptance Criteria

### Battle-Entity + Status

- [ ] `App\Battle\Model\Battle` Entity: id, initiator, defender (nullable
      für NPC), location, attackerFleet, defenderFleet (nullable),
      defenderPlanet (nullable für Planet-Defense), status, startedAt,
      endedAt, rounds (int)
- [ ] `App\Battle\ValueObject\BattleStatus` Enum:
      `RUNNING / ENDED_ATTACKER_WIN / ENDED_DEFENDER_WIN / DRAW`
- [ ] Migration + Doctrine ORM-Mapping + Repository

### Round-Engine

- [ ] `BattleResolver::resolve(Battle $battle, ?DateTimeImmutable $now): void`
- [ ] Loop max 10 Rounds, break wenn eine Seite leer
- [ ] Pro Round:
  - `damageA = Σ ship.effectiveDamage()` (mit Captain × `1 + 0.03 × lvl`)
  - `damageD = Σ ship.effectiveDamage() + turretDamage + aaDamage` (T-068)
  - Per-Ship-Damage = `floor(totalDamage / N_enemy_ships)`
  - Pro Ship: HP -= damage_per_ship; bei HP ≤ 0 → kill-marker
- [ ] Nach Round: `em->remove($ship)` für gestorbene
- [ ] Shield-Pool reduziert Damage zuerst (Defense-Battle)
- [ ] Defense-Building-HP reduziert nach Shield (T-068 Damage-State-Contract)

### Captain-Permadeath-Roll

- [ ] Für jeden Killed-Ship mit Captain:
      `random(100) < ship.shipClass.escapePodSurvivalChance`
- [ ] Survive → Captain.status = IDLE, Captain.assignedShip = null,
      Captain auf nearestHomePlanet (T-081 Helper)
- [ ] Fail → Captain.status = DEAD (T-104a Hook)

### Command + Trigger

- [ ] `StartBattleCommand(attackerFleetId, defenderTargetId)`:
      target = Fleet ODER Planet (Polymorph via discriminator)
- [ ] Validation: Fleets im selben System, beide non-empty
- [ ] BattleResolver läuft synchron beim Dispatch

### Tests

- [ ] `BattleResolverFoundationTest`: 2v2 gleicher Damage → DRAW; High-HP wins
- [ ] `CaptainStatBoostInBattleTest`: Captain L10 = ×1.30 effective Damage
- [ ] `PlanetDefenseBattleTest`: Shield-HP absorbiert; Turret+AA-Damage zählt
- [ ] `CaptainPermadeathRollTest`: Escape-Pod % per ShipClass (mock random)
- [ ] `BattleStatusTest`: Win/Loss/Draw nach 10 Rounds

### Docs

- [ ] `combat.md` (neu) — Battle-Foundation
- [ ] `decisions.md` Eintrag T-103

## Out of Scope (Folge-Tickets)

- **T-103b** Tactic-RPS-System + Tactic-Counter-Multi
- **T-103c** NPC-AI-Tactic-Heuristik
- **T-103d** Battle-Replay-Log-Persistence
- **T-103e** Loot-Drop-Trigger (T-080 Integration)
- T-104b Captain-Skill-Trees (Folge zu T-104a) — separat refined

## Fixtures Needed

Yes — `BattleFixture` (1v1, Multi-Ship, Planet-Defense, NPC-vs-Player Test-Battles).

## Notes

- PvE-only — Spieler-vs-Spieler explizit ausgeschlossen
- Captain-Skill-Hooks (T-104b) erweitern Damage-Model multiplikativ — kein
  T-103-Refactor nötig
- Replay-Log Out-of-Scope (T-103d) — Foundation persistiert nur Battle-End-Resultat

### Refinement Tokens (estimate)
- Input: ~10k
- Output: ~4k
