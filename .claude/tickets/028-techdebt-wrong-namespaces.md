# TD-028: Falsche Namespace-Imports (PlanetId / PlayerId)

**Type:** TechDebt
**Epic:** Tech-Debt & Cleanup
**Domain:** Planet
**Blocked By:** None
**Status:** Done
**Severity:** High
**Effort:** S
**Affected Domain(s):** Planet, Player

## Beschreibung

`src/Planet/Model/Planet.php:13` importierte `use ValueObject\PlanetId;` — **kein** `App\Planet\` Prefix. Gleiches in `src/Player/Model/Player.php:7` (`use ValueObject\PlayerId;`). Ursache war: die ValueObject-Klassen selbst hatten `namespace ValueObject;` deklariert (statt `App\Planet\ValueObject` bzw. `App\Player\ValueObject`). Damit war das Composer-PSR-4-Mapping verletzt — Klassen wurden vom Autoloader effektiv nicht via PSR-4 aufgelöst.

## Risk if ignored

Bricht sobald strikte Static-Analyse oder andere PHP-Versionen nutzen; verwirrt jeden neuen Entwickler; fehlerhafte ValueObject-Verwendung kann zu Type-Bugs führen.

## AC

- [x] `Planet.php` import: `use App\Planet\ValueObject\PlanetId;`
- [x] `Player.php` import: `use App\Player\ValueObject\PlayerId;`
- [x] Alle anderen Files mit gleichem Pattern korrigieren (10 Dateien insgesamt)
- [x] `PlanetId.php` namespace: `App\Planet\ValueObject`
- [x] `PlayerId.php` namespace: `App\Player\ValueObject`
- [x] `composer dump-autoload --classmap-authoritative` ohne Warnings für betroffene Klassen
- [x] `php bin/console --version` bootet sauber
- [ ] `PlayerStartUpScenario` läuft unverändert — *nicht verifiziert: Symfony bootet aber Scenario wurde nicht ausgeführt; voller Run blocked durch fehlende Doctrine-DB-Konfig (separates Thema, TD-032)*
- [ ] Existierende Tests grün — *kein Test-Setup vorhanden, siehe TD-031*

## Refactor Strategy

Mechanische Edits in 10 Dateien:

**Namespace-Deklarationen gefixt:**
- `src/Planet/ValueObject/PlanetId.php`
- `src/Player/ValueObject/PlayerId.php`

**Imports gefixt:**
- `src/Planet/Model/Planet.php`
- `src/Planet/Command/ClaimStartPlanetCommand.php`
- `src/Planet/Command/GeneratePlanetCommandHandler.php`
- `src/Planet/Service/GeneratePlanetCommandService.php`
- `src/Planet/Service/ClaimStartPlanetCommandService.php`
- `src/Player/Model/Player.php`
- `src/Player/Command/CreateNewPlayerCommandHandler.php`
- `src/Player/Service/CreateNewPlayerService.php`

## Findings during fix

Bei `composer dump-autoload --classmap-authoritative` traten Warnings auf für `BuildingId`/`BuildingType` — selbe Klasse von Bug, gehört zu TD-029. TD-029 wurde direkt anschließend gefixt, da Symfony sonst nicht mehr bootete (Container-Compiler läuft inzwischen weiter und stolperte nun über die Building-Files).

### Token Usage (estimate)
- Input: ~6k tokens (Reads + Greps)
- Output: ~2k tokens (Edits + Analyse)
