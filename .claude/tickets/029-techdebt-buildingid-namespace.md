# TD-029: BuildingId / BuildingType im falschen Namespace

**Type:** TechDebt
**Epic:** Tech-Debt & Cleanup
**Domain:** Building
**Blocked By:** None
**Status:** Done
**Severity:** Medium → High (real-world: blockierte Symfony-Boot nach TD-028)
**Effort:** S
**Affected Domain(s):** Building, Resource

## Beschreibung

Files lagen physisch korrekt in `src/Building/ValueObject/`, aber der `namespace`-Header im File deklarierte `App\Resource\ValueObject` — d.h. PSR-4-Verletzung (Composer skipped die Klassen mit `--classmap-authoritative`).

Während TD-028 fiel auf, dass Symfony nach dem Re-Dump nicht mehr bootete: Container-Compiler stolperte über die nicht-PSR-4-konformen Building-VOs. TD-029 wurde daher direkt nachgezogen.

## Risk if ignored

Confusing für Devs. Nach TD-028 sogar Boot-Blocker — alle weitere Arbeit blockiert.

## AC

- [x] `BuildingId.php` namespace: `App\Building\ValueObject`
- [x] `BuildingType.php` namespace: `App\Building\ValueObject`
- [x] Namespace-Header angepasst (Files lagen schon im richtigen Folder)
- [x] Imports in `src/Building/Model/Building.php` korrigiert
- [x] Imports in `src/Building/Service/ResourceBuildingMap.php` korrigiert
- [x] Imports in `src/Planet/Service/ClaimStartPlanetCommandService.php` korrigiert
- [x] `composer dump-autoload --classmap-authoritative` ohne Warnings
- [x] `php bin/console --version` bootet → Symfony 7.3.7 OK

## Refactor Strategy

Mechanische Edits in 5 Dateien (kein Move nötig — Files lagen schon korrekt):

**Namespace-Deklarationen gefixt:**
- `src/Building/ValueObject/BuildingId.php`
- `src/Building/ValueObject/BuildingType.php`

**Imports gefixt:**
- `src/Building/Model/Building.php`
- `src/Building/Service/ResourceBuildingMap.php`
- `src/Planet/Service/ClaimStartPlanetCommandService.php`

### Token Usage (estimate)
- Input: ~3k tokens (zusätzliche Reads/Greps oben drauf)
- Output: ~1k tokens (Edits)
