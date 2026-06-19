# T-104b Captain-Skill-Trees

**Type:** Feature
**Status:** Draft
**Effort:** L
**Depends on:** T-104a (Crew-Foundation), T-103 (Battle-Engine)
**Blocks:** —

## Beschreibung
Captain-Skill-Trees mit 4 Spezialisierungen. Per Level-Up wählt Spieler 1 Skill-Punkt aus 1 Tree.

Trees:
- **Beam-Master**: Damage-Boost in Standoff/Long-Range
- **Missile-Specialist**: Damage-Boost in Flanking/Mid-Range
- **Shield-Tactician**: Schild + Defense in Front-Assault
- **Fleet-Commander**: Boni für andere Schiffe in derselben Flotte (Aura)

## Acceptance Criteria
- [ ] CaptainSkillTree-Enum (BEAM_MASTER, MISSILE_SPECIALIST, SHIELD_TACTICIAN, FLEET_COMMANDER)
- [ ] Pro Captain: `chosenTree: ?CaptainSkillTree` (nullable bis erste Wahl)
- [ ] Pro Tree: Skill-Liste mit 5 Levels (Tier 1-5), je nächst-höhere braucht Captain-Level-Schwelle
- [ ] Skill-Effekt-Resolver: applies Stat-Multiplier zu Schiff in Battle-Engine
- [ ] Re-Spec-Mechanik: nicht möglich (Permanent-Choice)
- [ ] UI: Skill-Tree-View pro Captain
- [ ] Fleet-Commander-Aura wirkt auf maximal 5 Schiffe in Flotte (T-017)

## Affected Tests
- tests/Crew/Service/CaptainSkillEffectTest.php (Battle-Multiplier)
- tests/Crew/Service/FleetCommanderAuraTest.php

## Fixtures Needed
Yes — Captains mit verschiedenen Trees + Schiff-Setups

## Notes
- "Forced Specialization auf Captain-Ebene": einer kann nicht Beam UND Shield gleichzeitig sein
- Fleet-Commander = Multiplikator-Rolle, eigentlich Allianz-Vorteil (mehrere Captains in Coalition)
