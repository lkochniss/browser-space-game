# T-103b Battle-Tactic-RPS-System (Folge zu T-103)

**Type:** Feature
**Epic:** Combat & Battle
**Domain:** Ship
**Blocked By:** T-103
**Status:** Draft
**Effort:** M
**Depends on:** T-103 (Battle-Foundation)
**Blocks:** T-103c (NPC-AI braucht Tactic-Pool)

## Beschreibung

Erweitert T-103 Battle-Engine um RPS-light Tactic-Counter-System. Player wählt
vor Battle eine Tactic, beeinflusst Damage und Loss.

Tactic-Options + RPS-Cycle:
- **Front-Assault**: hoher Damage, hohe Loss. Counter zu Hit-and-Run.
- **Flanking**: balanced. Counter zu Front-Assault.
- **Hit-and-Run**: low-loss escape, low-damage. Counter zu Standoff.
- **Standoff**: long-range, niedrige Loss. Counter zu Flanking.

Counter-Multiplier: Winning Tactic ×1.3 Damage, losing ×0.7.

## Open Questions

### Q1: Tactic-Selection-UX (Demo-CLI)
- Pre-Battle Prompt? Default-Tactic per Setting? Auto-Pick basierend auf Schiff-Composition?

### Q2: Captain-Skill-Synergy (T-104b Hook-Vorbereitung)
- Bestimmte Captain-Skills (Beam-Master, Shield-Tactician) sollen Tactics
  boosten. Wie wird das Mapping definiert? T-103b oder T-104b?

### Q3: Mid-Battle-Tactic-Switch erlaubt?
- Aktuell: pre-Battle Tactic fix für alle 10 Rounds. Switch wäre per-Round
  oder per-3-Round-Phase möglich. Komplexität-Tradeoff.

## Acceptance Criteria (Draft — final nach Q1-Q3)

- [ ] `App\Battle\ValueObject\Tactic` Enum: FRONT_ASSAULT, FLANKING,
      HIT_AND_RUN, STANDOFF
- [ ] `Tactic::beats(Tactic $other): bool` — RPS-Cycle
- [ ] `Battle::attackerTactic` + `defenderTactic` Fields
- [ ] `BattleResolver` liest Tactics, applied `×1.3 / ×0.7` Counter-Multi
      auf Damage-Pools
- [ ] `StartBattleCommand` erweitert um attackerTactic-Param
- [ ] Tests: Counter-Cycle (4 Cases), Damage-Multi wirkt

## Out of Scope

- NPC-AI für Defender-Tactic → T-103c
- Captain-Skill-Tactic-Boost → T-104b (Hook in T-104b)
- Mid-Battle-Switch (Q3 offen)

## Notes

- Tactic-Selection muss VOR T-103c (NPC-AI) refined sein — NPC braucht
  Tactic-Pool zur Auswahl
