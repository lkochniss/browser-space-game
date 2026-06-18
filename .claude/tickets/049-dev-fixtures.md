# T-049: Dev-Fixtures

**Type:** Feature
**Status:** Open
**FX:** Yes (das ist der Ticket-Inhalt)
**MIG:** No
**Depends on:** TD-031, TD-032, T-036, T-043

## Description

Lokale Entwicklung braucht Demo-Daten: Test-User mit Player + Planeten + Resources, Admin-User. Auch Basis für IT-Setup.

## AC

- [ ] `composer require --dev doctrine/doctrine-fixtures-bundle`
- [ ] `src/DataFixtures/`:
  - `UserFixtures` (admin, normaler User, gelöschter User)
  - `PlayerFixtures` (mit Start-Planet-Setup)
  - `WorldFixtures` (1+ SolarSystems mit POIs für Discovery-Tests)
- [ ] Pwds dokumentiert in `README.md` (DEV-only)
- [ ] `bin/console doctrine:fixtures:load` läuft grün
- [ ] IT-Bootstrap nutzt dieselben Fixtures

## Affected

- `composer.json`
- Neu: `src/DataFixtures/*`

## Open Questions

1. Fixtures auch in Prod-Tests nutzbar oder strikt Dev-only?
2. Reproduzierbar (fixe Seeds) oder zufällig pro Run?
