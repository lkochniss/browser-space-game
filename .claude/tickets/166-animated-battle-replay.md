# T-166 Animated-Battle-Replay (Folge zu T-164)

**Type:** Feature
**Epic:** Game UI
**Domain:** UI
**Blocked By:** T-164, T-103
**Status:** Draft
**Effort:** L (TBD)
**Depends on:** T-164 (Battle-Replay-Table-MVP), T-103 (Battle-Engine)
**Blocks:** —

## Beschreibung

Folge-Ticket zu T-164 (Table-MVP). Animated-View mit Schiff-Icons + HP-Bars + Round-by-Round-Animation.

Zeigt Battle visuell: Schiffe positioniert auf Canvas/SVG, Damage-Effekte, Schiff-Loss-Visualisierung, Captain-Skill-Ankündigungen.

## Acceptance Criteria

- [ ] TBD: Stimulus-Animation-Controller mit Round-Playback
- [ ] TBD: Schiff-Icons pro Klasse (T-102) als Asset-Library
- [ ] TBD: HP-Bar-Animation pro Schiff
- [ ] TBD: Pause/Resume/Speed-Up-Controls
- [ ] TBD: Tactic-Indikator + Captain-Skill-Trigger-Events visuell

## Open Questions

- Tech-Stack: Canvas vs SVG (analog T-160-Decision)?
- Performance bei 30+ Schiff-Battles?
- Mobile-Tauglichkeit?

## Notes

- Falls Spieler-Feedback nach T-164 will → dieses Ticket aktivieren
- Engagement-Booster, kein Mechanik-Impact
