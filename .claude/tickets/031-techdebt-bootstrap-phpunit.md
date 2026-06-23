# TD-031: PHPUnit Bootstrap fehlt

**Type:** TechDebt
**Epic:** Tech-Debt & Cleanup
**Domain:** Common
**Blocked By:** None
**Status:** Done
**Severity:** High
**Effort:** M
**Affected Domain(s):** Infrastructure

## Beschreibung

Kein `tests/` Folder. Kein `phpunit.xml`. `composer.json` deklariert `App\Tests\` PSR-4 → tests-Folder war erwartet aber nicht da. Ohne Test-Setup ist jedes weitere Feature-Ticket nicht verifizierbar — Skill verlangt Integration-Tests als Default.

## Risk if ignored

Jeder Feature-Ticket schiebt Test-Schuld vor sich her. Regression-Risk steigt linear mit Code-Wachstum.

## AC

- [x] `phpunit.dist.xml` mit Symfony-Defaults (vom Recipe erzeugt)
- [x] `tests/bootstrap.php` (vom Recipe erzeugt, Standard)
- [x] `tests/Smoke/KernelSmokeTest.php` (2 Smoke-Tests: Kernel boots, CommandBus wired)
- [x] `composer require --dev symfony/test-pack` ausgeführt → installiert phpunit/phpunit ^11, phpunit-bridge, dom-crawler, css-selector etc.
- [x] In-Memory SQLite via `.env.test` (`DATABASE_URL="sqlite:///:memory:"`)
- [x] `vendor/bin/phpunit` läuft grün (PHPUnit 11.5.46, PHP 8.2.27, 2 Tests / 2 Assertions, 0.475s)

## Refactor Strategy

Stock Symfony-Setup. Folge `symfony/test-pack` Recipe.

## Side-Effects / Befunde während Setup

1. **`src/Controller/` fehlte**: `config/routes.yaml` referenziert `../src/Controller/` — Cache-Clear schlug fehl ohne Folder. Workaround: `src/Controller/.gitkeep` angelegt. Wird durch T-034 (Web-Layer Bootstrap) sowieso mit echten Controllern befüllt.
2. **`DATABASE_URL` in `.env`** zeigt aktuell auf PostgreSQL — Architektur-Entscheidung (`docs/decisions.md`) sagt MySQL für Prod. Für Tests irrelevant (SQLite-Override greift), aber für TD-032 (ORM-Mapping) muss `.env` korrigiert werden.
3. **Recipe lieferte `phpunit.dist.xml`** (neue Konvention) statt `phpunit.xml.dist` — beides wird von PHPUnit 11 erkannt; Datei nicht umbenannt.

## Folge-Tickets / Updates

- TD-032 sollte als Vorab-Schritt `.env`-DATABASE_URL auf MySQL umstellen
- T-034 wird `src/Controller/.gitkeep` durch echten `HomeController` ersetzen

### Token Usage (estimate)
- Input: ~7k tokens (composer-output, configs, recipe-files)
- Output: ~1.5k tokens (Edits + Smoke-Test)
