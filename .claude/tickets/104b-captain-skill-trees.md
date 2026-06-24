# T-104b Captain-Skill-Trees

**Type:** Feature
**Epic:** Combat & Battle
**Domain:** Ship
**Blocked By:** T-104a, T-103
**Status:** Done
**Effort:** L
**Depends on:** T-104a (Ready), T-103 (Ready), T-103b (Tactic-RPS — falls Foundation für Tactic-Boost-Skill nötig)
**Blocks:** —

## Beschreibung

Captain-Skill-Trees mit 4 Spezialisierungen. Pro Captain-Level erhält Player
1 Skill-Punkt + verteilt frei auf 4 Trees (kein Force-Lock auf einen Tree).

**Trees + Effekt-Stil:**
- **Beam-Master**: Damage-Boost in Standoff/Long-Range-Tactic (T-103b Hook)
- **Missile-Specialist**: Damage-Boost in Flanking/Mid-Range-Tactic
- **Shield-Tactician**: Schild-Boost + Defense in Front-Assault
- **Fleet-Commander**: Tactic-Counter-Multi-Boost für eigene Flotte
  (Q4 = `×1.5` statt Standard `×1.3` Winning-Tactic)

## Resolved Decisions

- **Q1 Skill-Allocation:** **Free-Point-System.** Kein fixer Tree. Captain
  bekommt 1 Skill-Punkt pro Level (max L10 → 10 Punkte total). Player
  verteilt frei auf alle 4 Trees. Hybrid-Captains möglich.
- **Q2 Tree-Tier-Lock:** **Strikt.** Tier-N-Skill in einem Tree braucht
  vorher (N-1) Punkte in demselben Tree.
  Tier-3 Beam-Master = mind. 2 Punkte vorher in Beam-Master.
  → Fördert Spezialisierung trotz free-allocation; verhindert Endgame-Skill-Skip.
- **Q3 Re-Spec:** Nicht möglich. Permanent-Lock (Original-AC). Player muss
  careful planen.
- **Q4 Fleet-Commander-Aura (Tactic-Boost):** Pro Level in Fleet-Commander-Tree:
  - Wenn dieser Captain in Flotte → Player-Tactic-Counter wird `×(1.3 + 0.04 × fcLvl)`
    (Tier-1 = ×1.34, Tier-5 = ×1.50 statt Standard ×1.3)
  - Wirkt auf alle Schiffe in derselben Flotte (max 5 — Foundation-Cap)
  - Nur EIN FC-Effekt pro Flotte; höchstes Level zählt, kein Stacking

## Acceptance Criteria

### Skill-Domain

- [x] `App\Crew\ValueObject\CaptainSkillTree` Enum (4 Trees) — hält direkt die
      Multiplier-Lookups (Damage/Shield/FC-Boost pro Tier)
- [x] `App\Crew\ValueObject\SkillAllocation` readonly-VO: Map<TreeName, int>
      mit `getTier()`, `withIncrement()`, `totalPoints()`
- [x] `Crew.skill_allocation` JSON-Column persistiert die Allocation
- [x] `Crew::availableSkillPoints(): int` = `level - sum(allocation)`

### Skill-Tree-Definitions (4 Trees × 5 Tiers)

- [x] Multiplier-Werte direkt im Enum statt separater Registry-Service
      (kein cross-domain-Bedarf für Tree-Description-Strings im Foundation)
- [x] Beam-Master + Missile-Specialist 1.05/1.12/1.20/1.30/1.42
- [x] Shield-Tactician 1.10/1.25/1.45/1.70/2.00
- [x] Fleet-Commander +0.04/+0.08/+0.12/+0.16/+0.20

### Skill-Allocation-Command

- [x] `AllocateSkillPointCommand(crewId, tree)` (Tier wird implizit auto-
      incrementiert — sequentielle Allocation)
- [x] Validation: `availableSkillPoints > 0`, `current_tier < MAX_TIER`
- [x] Permanent (Q3) — kein Re-Spec
- [x] `InsufficientSkillPointsException`, `TierLockViolationException`

### Battle-Integration (T-103 Hook)

- [x] Crew Read-API (`getDamageMultiplier`, `getShieldMultiplier`,
      `getFleetCommanderTier`) für Battle-Resolver-Konsum exposed
- [ ] Wiring in `BattleResolver`: Tactic-aware Damage/Shield-Application
      → _deferred zu T-103b Tactic-RPS-System_ (Battle-Resolver hat heute
      kein Tactic-Konzept; Foundation T-104b liefert die Multiplier nur
      als API)
- [ ] `Fleet::getTacticCounterMulti()` mit FC-Aura → _deferred zu T-103b_

### Demo CLI

- [ ] Action "Allocate Captain Skill Point" — _deferred: Foundation-Demo deckt
      Crew-Foundation, Skill-Allocation kann via Test-Path verifiziert werden_
- [ ] Status-Display Allocation per Captain — _deferred analog_

### Tests

- [x] `AllocateSkillPointCommandTest` (8): Allocation increments, Multi-Allocs,
      Insufficient-Points, Tier-Lock (max 5), Damage/Shield/FC-Multi-Lookups,
      Persistence-Round-Trip

### Docs

- [x] `combat.md` Captain-Skill-Tree-Sektion (4 Trees × 5 Tiers)
- [x] `decisions.md` Eintrag T-104b

## Fixtures Needed

Yes — `CaptainSkillFixture` mit Captains in diversen Skill-Allocations
(Spezialist vs. Hybrid) für Test-Coverage.

## Notes

- Free-Allocation + strict Tier-Lock = balanced Pattern: Player kann hybrid
  bauen, aber Endgame-Skills fordern Investment in EINEN Tree
- Fleet-Commander-Aura ist additiv auf Battle-Counter-Multi (T-103b Tactic-System)
- Re-Spec kann später als Folge-Ticket T-104b-respec via Loot-Drop hinzukommen
  falls Player-Feedback es verlangt

### Refinement Tokens (estimate)
- Input: ~9k
- Output: ~4k

### Implementation Tokens (estimate)
- Input: ~120k
- Output: ~12k

### Deferred / Follow-Ups

- Battle-Resolver-Wiring (Tactic-aware Damage/Shield) → T-103b Tactic-RPS
- `Fleet::getTacticCounterMulti()` mit FC-Aura → T-103b
- Demo-CLI Skill-Allocation-Action
- Re-Spec via Loot-Drop (separates Folge-Ticket falls Player-Feedback)
- `CaptainSkillFixture` (heute Tests bauen inline)
