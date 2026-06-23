# T-104b Captain-Skill-Trees

**Type:** Feature
**Epic:** Combat & Battle
**Domain:** Ship
**Blocked By:** T-104a, T-103
**Status:** Ready
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

- [ ] `App\Crew\ValueObject\CaptainSkillTree` Enum: `BEAM_MASTER`,
      `MISSILE_SPECIALIST`, `SHIELD_TACTICIAN`, `FLEET_COMMANDER`
- [ ] `App\Crew\ValueObject\SkillAllocation` readonly-VO:
      `array<TreeName.value, int>` (4 Trees → Points; Sum ≤ Captain.level)
- [ ] `Crew::skillAllocation: SkillAllocation` Field (Embeddable, JSON-Persisted)
- [ ] `Crew::availableSkillPoints(): int` = `level - sum(allocation)`

### Skill-Tree-Definitions (4 Trees × 5 Tiers)

- [ ] `SkillTreeRegistry` Service mit allen 4 Trees + 5 Tiers each (20 Skills)
- [ ] Pro Skill: `tree`, `tier (1-5)`, `name`, `description`,
      `damageMultiplier` / `shieldMultiplier` / `tacticCounterBoost` (optional je nach Tree)
- [ ] **Beam-Master Tiers** (jeder Tier +X% Damage in Standoff-Tactic):
      T1 +5%, T2 +12%, T3 +20%, T4 +30%, T5 +42%
- [ ] **Missile-Specialist Tiers** (Flanking-Tactic):
      T1 +5%, T2 +12%, T3 +20%, T4 +30%, T5 +42%
- [ ] **Shield-Tactician Tiers** (Front-Assault):
      T1 +10% Shield-HP, T2 +25%, T3 +45%, T4 +70%, T5 +100%
- [ ] **Fleet-Commander Tiers** (Tactic-Counter-Boost auf Flotte):
      T1 +4%, T2 +8%, T3 +12%, T4 +16%, T5 +20% (additiv auf 1.3 Base)

### Skill-Allocation-Command

- [ ] `AllocateSkillPointCommand(captainId, tree, tier)`:
      - Validation: Captain hat available point
      - Validation: Tree-Tier-Lock (Tier-N braucht (N-1) Punkte in Tree)
      - Permanent (Q3) — kein Re-Spec
- [ ] `InsufficientSkillPointsException`, `TierLockViolationException`

### Battle-Integration (T-103 Hook)

- [ ] `Ship::getEffectiveDamage(?Tactic $currentTactic)`:
      - Base × Captain-Stat-Bonus (T-104a `0.03 × lvl`)
      - × Skill-Multi je nach Tree + currentTactic-Matching
- [ ] `Ship::getEffectiveShieldHp()`:
      - Base × Shield-Tactician-Multi falls Skill allocated
- [ ] `Fleet::getTacticCounterMulti()`:
      - `1.3 + 0.04 × max(fc_lvl_aller_captains_in_fleet)` falls Winning-Tactic
      - Kein Stacking; höchstes FC-Level zählt

### Demo CLI

- [ ] Action "Allocate Captain Skill Point" (Captain + Tree + Tier picker)
- [ ] Status-Display: pro Captain `Allocation: BM:3 MS:1 SH:2 FC:0`

### Tests

- [ ] `SkillAllocationCommandTest`: available-points, Tier-Lock, permanent
- [ ] `BeamMasterSkillEffectTest`: Standoff-Tactic-Damage-Boost
- [ ] `MissileSpecialistEffectTest`: Flanking-Tactic-Damage-Boost
- [ ] `ShieldTacticianEffectTest`: Shield-HP-Multi
- [ ] `FleetCommanderAuraTest`: Tactic-Counter-Multi-Boost auf Flotte
- [ ] `TierLockViolationTest`: kann nicht T-3 ohne vorher T-1 + T-2

### Docs

- [ ] `combat.md` Captain-Skill-Tree-Sektion (4 Trees × 5 Tiers)
- [ ] `decisions.md` Eintrag T-104b

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
