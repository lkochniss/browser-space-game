# T-103d Battle-Replay-Persistence (Folge zu T-103)

**Type:** Feature
**Epic:** Combat & Battle
**Domain:** Ship
**Blocked By:** T-103
**Status:** Draft
**Effort:** M
**Depends on:** T-103 (Battle-Foundation)
**Blocks:** T-164 (Battle-Replay-UI)

## Beschreibung

T-103 Foundation speichert nur Battle-End-Resultat. Replay-UI (T-164) braucht
Per-Round-Daten. T-103d persistiert alle Battle-Rounds für späteres Replay.

## Open Questions

### Q1: Persistence-Format

- **(a) BattleRound-Entity** — pro Round eine DB-Row. SQL-queryable, but
  pro Battle 10× Insert.
- **(b) JSON-Blob** — `battle.rounds_json` als JSON-Array. Single column,
  schreib-effizient, kein Per-Round-Query.
- **(c) Hybrid** — Battle hat `rounds_summary_json` (compact) + on-demand
  Detail-Load.

### Q2: Retention

- Speichern für IMMER? Oder Auto-Cleanup nach X Tagen?
- Replay sinnvoll nur für recent battles, alte = Stats-only.

### Q3: Captured-Felder pro Round

- attacker_damage, defender_damage, ships_lost_attacker, ships_lost_defender,
  narrative_events (Schiff-X killed, Captain-Y survived) — wie tief?

## Acceptance Criteria (Draft — final nach Q1-Q3)

- [ ] Battle-Round-Persistence-Format (Q1)
- [ ] Replay-Reader: `Battle::getReplay(): BattleReplay` für UI
- [ ] Retention-Policy (Q2)
- [ ] Tests: Round-Daten korrekt persistiert + reloaded

## Out of Scope

- Replay-UI selbst (T-164)
- Animated Replay (T-166)
