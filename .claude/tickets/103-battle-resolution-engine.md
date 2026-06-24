# T-103 Battle-Resolution-Engine (Foundation)

**Type:** Feature
**Epic:** Combat & Battle
**Domain:** Ship
**Blocked By:** T-102, T-068
**Status:** Done
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

- [x] `App\Battle\Model\Battle` Entity (id, attacker, attackerFleet,
      defenderFleet / defenderPlanet (XOR), location, status, rounds,
      startedAt, endedAt)
- [x] `App\Battle\ValueObject\BattleStatus` Enum mit 4 Werten
- [x] Migration `Version20260624000003` + Doctrine ORM + Repository

### Round-Engine

- [x] `BattleResolver::resolve(Battle $battle): void` (Clock injected)
- [x] Loop max 10 Rounds, break wenn eine Seite leer
- [x] Pro Round: Σ Damage gleichmäßig auf alive Enemy-Ships,
      Captain-Boost via Crew.getStatsMultiplier × Blueprint-Damage
- [x] Killed Ships via `em->remove($ship)`
- [x] PLANETARY_SHIELD-Pool absorbiert Defender-Side-Damage VOR Ship-Loss
      (Foundation: Shield-Pool in-memory tracked, kein Persist auf Building)
- [ ] PLANETARY_SHIELD-Building-HP-Sync — _deferred: in-memory Shield-Pool
      reicht für Foundation; Persistence-Sync auf Building.currentHp kann
      mit T-103d Replay-Log zusammen ergänzt werden_

### Captain-Permadeath-Roll

- [x] Pro Killed-Ship mit Captain (`CrewRepository::findByAssignedShip`):
      `BattleRandomizer::roll() < ship.escapePodSurvivalChance`
- [x] Survive → `Crew::unassign()` (status=IDLE, assignedShip=null)
- [x] Fail → `Crew::markDead()` (status=DEAD)
- [ ] Survival-Placement auf `nearestHomePlanet` (T-081) — _deferred:
      Captain landet aktuell als IDLE ohne Planet-Binding (Crew hat keine
      Planet-Relation); Folge-Ticket falls Crew-Planet-Binding gewünscht_

### Command + Trigger

- [x] `StartBattleCommand(attackerFleetId, defenderFleetId|defenderPlanetId)`
- [x] Validation: XOR auf Defender, Same-System, Non-Empty
- [x] BattleResolver synchron beim Dispatch

### Tests

- [x] `BattleResolverTest` (7): Class-Difference-Win, Draw-after-10,
      Shield-Absorbtion (Frigate vs L5 Shield = Draw), Captain-Boost,
      Captain-Permadeath (mocked Randomizer roll=99/0)

### Docs

- [x] `combat.md` (neu) — Battle-Foundation
- [x] `decisions.md` Eintrag T-103
- [x] `README.md` (docs) Eintrag combat.md

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

### Implementation Tokens (estimate)
- Input: ~200k
- Output: ~22k

### Deferred / Follow-Ups

- Persist Shield-Pool-Depletion auf PLANETARY_SHIELD.currentHp (heute
  in-memory). Folge mit T-103d Replay-Log.
- Captain-Survival landing auf nearestHomePlanet (T-081 Helper) — Crew hat
  heute keine Planet-Relation; Foundation lässt überlebenden Captain einfach
  IDLE ohne Planet.
- `BattleFixture` für T-103-Folge-Tickets (heute Tests bauen inline)
- Demo-CLI Action "Start Battle" mit Defender-Picker — Foundation-Demo deckt's
  nicht ab
