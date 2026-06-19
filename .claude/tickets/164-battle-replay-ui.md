# T-164 Battle-Replay UI (Table vs Animated)

**Type:** Feature
**Status:** Draft
**Effort:** L
**Depends on:** T-103 (Battle-Engine)
**Blocks:** —

## Beschreibung
Battle-Replay-Visualisierung. Spieler sieht abgelaufene Schlacht detailliert.

**Decision pending**: Table-View (compact, fast) vs Animated-View (Stimulus-Animation der Rounds).

Empfehlung: Table-MVP, Animated-Folge-Ticket.

## Acceptance Criteria
- [ ] BattleReplayController + DTO-API (lädt Battle-Entity inkl. allen Rounds)
- [ ] Table-View: 1 Zeile pro Round, Columns: Attacker-Damage, Defender-Damage, Losses (gegliedert nach Schiff)
- [ ] Final-Result-Banner: Sieger + Loot-Breakdown
- [ ] Tactic-Choices anzeigen (welche Tactic vom Attacker/Defender)
- [ ] Captain-Skill-Effects markiert (welche Skills wirkten)
- [ ] Optional Animated-View als Folge-Ticket: Stimulus-Animation mit Schiff-Icons + HP-Bars
- [ ] Mobile-Layout (Table responsive)

## Affected Tests
- tests/Battle/Controller/BattleReplayControllerTest.php (DTO-Format)

## Fixtures Needed
Yes — Battle-Samples mit verschiedenen Outcomes

## Notes
- Replay = Engagement-Booster (Spieler lernt aus Fehlern)
- Animated-Folge nur falls Spieler-Feedback es will
