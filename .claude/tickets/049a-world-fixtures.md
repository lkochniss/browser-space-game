# T-049a: World-Fixtures (deterministische Galaxy)

**Type:** Feature
**Epic:** Foundation: Galaxy
**Domain:** SolarSystem
**Blocked By:** None
**Status:** Done
**Effort:** S (~1.5h)
**FX:** Yes (Ticket-Inhalt)
**MIG:** No
**Depends on:** keine harten Deps (Galaxy-Domain done)
**Blocks:** —
**Spaltet ab:** T-049 (Player + User defer bis T-036)

## Beschreibung

Schmaler Scope von T-049: **nur World-Fixtures** (5 SolarSystems mit deterministischer
POI-Mischung). Player- und UserFixtures werden in T-049 verschoben sobald T-036
(User-Entity) Done.

Zweck:
- Reproduzierbare Galaxy für IT-Tests (statt zufällige Galaxy-Generation)
- `bin/console doctrine:fixtures:load` als one-liner für Local-Setup
- Optionaler Bootstrap-Pfad neben `ClaimStartPlanetCommand` (random)

## Acceptance Criteria

- [x] `composer require --dev doctrine/doctrine-fixtures-bundle` (^4.3)
- [x] `src/DataFixtures/WorldFixture.php` mit 5 Systems + 11 Planets + 5 Asteroids
  + Wormhole-Pair (Alpha↔Epsilon) + 1 Nebula (Beta). Alle UUIDs fix, asserzbar.
- [x] Bundle für `dev`, `test`, `demo` registriert (production-safe)
- [x] `bin/console doctrine:fixtures:load --no-interaction --env=demo` läuft grün
  (verified: 5 systems, 11 planets, 5 asteroids, 1 nebula, 2 wormholes in `var/demo.db`)
- [x] 4 IT-Tests in `tests/DataFixtures/WorldFixtureTest.php`:
  count-Assertions, fixed-UUIDs-resolvable, asteroid-contents-deterministic,
  planets-unclaimed
- [x] Suite grün (427/427, +4 neu)
- [x] Stub `AppFixtures.php` (Bundle-Install-Default) entfernt

## Decisions (2026-06-19)

1. **Demo-CLI:** Pfade getrennt — Demo bleibt bei `ClaimStartPlanetCommand` (random).
   Fixtures sind nur für IT-Tests + manuelles `doctrine:fixtures:load`.
2. **Player:** strikt nur Welt — Player-Anlage in Tests/Demo separat. Sauberer Cut
   zu T-049.

## Files

**Neu:**
- `src/DataFixtures/WorldFixture.php`
- `tests/DataFixtures/WorldFixtureTest.php`

**Geändert:**
- `composer.json` (require-dev)
- `composer.lock`
- `config/bundles.php` (DoctrineFixturesBundle für dev/test)

## Fixtures Needed

Yes — das ist der Ticket-Inhalt.

## Notes

- Fixed UUIDs via Hard-Coded String-Konstanten (z.B. `'00000000-0000-0000-0000-000000000001'`)
  → Tests können gegen sie asserten, IDs bleiben stabil.
- Fixture-Bundle macht Fixtures NUR in `dev`/`test`-env verfügbar (production-safe).
