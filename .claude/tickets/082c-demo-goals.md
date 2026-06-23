# T-082c: Demo-Goals (Mini-Quest-Display)

**Type:** Feature
**Epic:** Demo CLI
**Domain:** Demo
**Blocked By:** T-082b, T-021, T-014
**Status:** Done
**Effort:** S (~45min)
**Depends on:** T-082b (Demo-CLI), T-021 (Recycling), T-014 (Colonize)
**Blocks:** —

## Beschreibung

Demo-CLI braucht Spielziel ohne Forschung/Combat. T-082c fügt 5 fixe Mini-Goals
hinzu, die als On-Demand-Checks im Demo-Menü ✓/✗ angezeigt werden.

## Decisions (2026-06-19)

1. **Scope:** 5 fixe Goals, hardcoded — kein Goal-Entity, kein Yaml.
2. **Trigger:** On-Demand — eigene `Goals` Menu-Action im Demo, listet alle 5 Goals.
3. **State:** Stateless — Re-Compute bei jedem Check, keine DB-Persistierung.

## Acceptance Criteria

- [x] `DemoGoalChecker` Service mit `check(Player): list<DemoGoal>`
- [x] 5 Goals: Hub L2, 3 Basic-Mines, Recycling-Plant, 50+ Debris, 2. Planet
- [x] Demo-CLI Menu-Action `Goals` mit ✓/✗ + Progress-Hint
- [x] 5 Unit-Tests grün
- [x] Suite grün (446/446)

## Files

**Neu:**
- `src/Demo/Service/DemoGoalChecker.php`
- `src/Demo/ValueObject/DemoGoal.php`
- `tests/Demo/Service/DemoGoalCheckerTest.php`

**Geändert:**
- `src/Demo/Command/InteractiveDemoCommand.php` (`Goals` Menu-Action)

## Out of Scope

- Persistierung (Goal-completedAt-Timestamps)
- Generic Goal-Framework (eigener Folge-Ticket falls je benötigt)
- Goals für Forschung / Battle / Trade
