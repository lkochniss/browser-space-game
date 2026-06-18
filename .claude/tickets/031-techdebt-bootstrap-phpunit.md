# TD-031: PHPUnit Bootstrap fehlt

**Type:** TechDebt
**Status:** Open
**Severity:** High
**Effort:** M
**Affected Domain(s):** Infrastructure

## Beschreibung

Kein `tests/` Folder. Kein `phpunit.xml`. `composer.json` deklariert `App\Tests\` PSR-4 → tests-Folder ist erwartet aber nicht da. Ohne Test-Setup ist jedes weitere Feature-Ticket nicht verifizierbar — Skill verlangt Integration-Tests als Default.

## Risk if ignored

Jeder Feature-Ticket schiebt Test-Schuld vor sich her. Regression-Risk steigt linear mit Code-Wachstum.

## AC

- [ ] `phpunit.xml` mit Symfony-Defaults
- [ ] `tests/bootstrap.php`
- [ ] `tests/` Folder mit erstem Smoke-Test (z.B. `KernelSmokeTest`)
- [ ] `composer require --dev phpunit/phpunit symfony/test-pack` falls noch nicht in composer.lock
- [ ] In-Memory SQLite konfiguriert für künftige Doctrine-Tests (siehe TD-032)
- [ ] `bin/phpunit` läuft grün

## Refactor Strategy

Stock Symfony-Setup. Folge `symfony/test-pack` Recipe.
