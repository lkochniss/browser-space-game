# TD-029: BuildingId / BuildingType im falschen Namespace

**Type:** TechDebt
**Status:** Open
**Severity:** Medium
**Effort:** S
**Affected Domain(s):** Building, Resource

## Beschreibung

`src/Resource/ValueObject/BuildingId.php` und `src/Resource/ValueObject/BuildingType.php` liegen unter Resource — gehören aber zur Building-Domain. Cross-Domain-Verschmutzung, schwächt Domain-Boundaries.

## Risk if ignored

Confusing. Sobald mehrere Devs an Building/Resource arbeiten → Konflikte + falsche Mental Models. Refactor wird teurer je mehr Code drauf aufbaut.

## AC

- [ ] `BuildingId.php` → `src/Building/ValueObject/BuildingId.php`
- [ ] `BuildingType.php` → `src/Building/ValueObject/BuildingType.php`
- [ ] Namespace-Header anpassen
- [ ] Alle Imports in `src/Building/` updaten
- [ ] Alle Imports in `src/Tick/`, `src/Planet/` etc. updaten
- [ ] Build/Run grün

## Refactor Strategy

Move + Rename + Search/Replace all `App\Resource\ValueObject\Building*` → `App\Building\ValueObject\Building*`. Pure mechanical.
