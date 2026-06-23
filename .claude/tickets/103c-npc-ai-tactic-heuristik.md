# T-103c NPC-AI-Tactic-Heuristik (Folge zu T-103b)

**Type:** Feature
**Epic:** Combat & Battle
**Domain:** Ship
**Blocked By:** T-103b
**Status:** Draft
**Effort:** S
**Depends on:** T-103 (Battle-Foundation), T-103b (Tactic-RPS-System)
**Blocks:** —

## Beschreibung

NPC-Defender-Side wählt Tactic per Heuristik beim Battle-Start. Kein
Machine-Learning, deterministisches Regel-System.

## Open Questions

### Q1: Heuristik-Komplexität

- **(a) Random** — NPC picks zufällig aus 4 Tactics. Einfachste Foundation.
- **(b) Composition-Based** — NPC analysiert eigene Fleet (Frigate-heavy →
  Hit-and-Run, Battleship-heavy → Front-Assault). Deterministisch.
- **(c) Counter-Aware** — NPC sieht Player-Tactic (post-Selection) und
  picks Counter. Unfair? Vorlauf-Information für NPC.
- **(d) Mixed** — Threat-Level-skaliert (T-099). Schwächere NPCs random,
  stärkere Composition-Based, Elite-Crusade-Bosse Counter-Aware.

### Q2: Tactic-Reveal-Timing

- Player-Tactic VOR oder NACH NPC-Tactic-Pick sichtbar?
- Beide gleichzeitig (Hidden-Lock-In)? Klassisches RPS-Pattern.

## Acceptance Criteria (Draft — final nach Q1-Q2)

- [ ] `NpcTacticHeuristik` Service
- [ ] `BattleResolver` callt Heuristik zur Auswahl der NPC-Tactic
- [ ] Tests: Heuristik-Output je Composition / Threat-Level

## Out of Scope

- Player-AI (PvP-only Feature; PvP ist explicit excluded T-103)
- Adaptive-Learning / ML
