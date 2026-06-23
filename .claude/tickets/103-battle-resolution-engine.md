# T-103 Battle-Resolution-Engine

**Type:** Feature
**Epic:** Combat & Battle
**Domain:** Ship
**Blocked By:** T-102, T-068, T-104b
**Status:** Draft (deferred 2026-06-19 — XL-Deps T-102/T-068/T-104b alle Draft)
**Effort:** XL
**Depends on:** T-102 (Schiff-Klassen), T-068 (Defense), T-104b (Captain-Skills)
**Blocks:** T-074, T-075, T-077, T-080

## Decisions (2026-06-19)

Pre-locked Decisions falls T-103 später aufgenommen wird:

1. **Enemy-Fleet-Sourcing (für Demo-Sandbox-Foundation):** Beides — Sandbox-Path
   (User definiert Enemy-Composition via CLI-Input, transient) UND Mini-NPC-Spawn
   (1 NPC-Ship pro System via Demo-Garantie, persistiert). Sandbox für freies
   Testen, NPC-Spawn für natürliche Begegnung.
2. **Damage-Modell:** Pro Schiff HP-Pool + Damage-Stat aus ShipType (`getMaxHp()`,
   `getDamage()`). Round = jede Seite verteilt Total-Damage gleichmäßig auf
   Gegner-Schiffe; Schiff stirbt bei HP ≤ 0. Aggregierte-Fleet-HP verworfen
   (zu unsauber).
3. **Tactic-RPS:** wie ursprünglicher AC — Front-Assault / Flanking / Hit-and-Run
   / Standoff mit ×1.3/×0.7 Counter-Multiplier (kein Re-Decision).

## Beschreibung
Round-based Auto-Resolution mit Pre-Battle Tactic-Choice. RPS-light Counter-System (Tactic A schlägt B, B schlägt C, C schlägt A).

Tactic-Options:
- Front-Assault: hoher Damage, hohe Loss; counter zu Hit-and-Run
- Flanking: balanced; counter zu Front-Assault
- Hit-and-Run: low-loss escape, low-damage; counter zu Standoff
- Standoff: long-range, niedrige Loss; counter zu Flanking

## Acceptance Criteria
- [ ] BattleEntity (initiator, defender, location, tacticAttacker, tacticDefender, rounds-Array, survivorsAttacker, survivorsDefender, lootRolls, status, startedAt, completedAt)
- [ ] BattleRound-VO (roundNumber, attackerDamage, defenderDamage, attackerLossesPerShip, defenderLossesPerShip, narrativeEvents)
- [ ] Pre-Battle-Phase: Player wählt Tactic, gegnerische NPC wählt Tactic via AI-Heuristik
- [ ] BattleResolver: Round-by-Round, max 10 Rounds, Loser = wer nach 10 weniger HP-% hat
- [ ] Counter-Multiplier: gewinnende Tactic ×1.3 Damage, verlierende ×0.7
- [ ] Captain-Skill (T-104b): Boost zu Tactic (Beam-Master +Damage in Standoff, Shield-Tactician +HP in Front-Assault)
- [ ] Permadeath: zerstörte Schiffe gelöscht (T-105)
- [ ] Battle-Replay-Log: alle Rounds gespeichert für T-164 UI
- [ ] Loot-Trigger (T-080) am Ende

## Affected Tests
- tests/Battle/Service/BattleResolverTest.php (round logic, counter)
- tests/Battle/Service/BattleAiTacticTest.php (NPC-Auswahl)
- tests/Battle/Service/BattleReplayPersistenceTest.php

## Fixtures Needed
Yes — Test-Battles mit beiden Tactic-Combinationen, Captains

## Notes
- "Auto-Resolution" → Spieler stellt Flotten + Tactic ein, System resolved (kein Live-RTS)
- T-024 (existing Raumschlacht-Ticket) muss zu PvE-Pivot updated werden
- Battle-Engine ist PvE-only — Spieler-vs-Spieler explizit ausgeschlossen
