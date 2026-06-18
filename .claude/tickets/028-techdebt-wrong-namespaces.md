# TD-028: Falsche Namespace-Imports (PlanetId / PlayerId)

**Type:** TechDebt
**Status:** Open
**Severity:** High
**Effort:** S
**Affected Domain(s):** Planet, Player

## Beschreibung

`src/Planet/Model/Planet.php:13` importiert `use ValueObject\PlanetId;` — **kein** `App\Planet\` Prefix. Gleiches in `src/Player/Model/Player.php:7` (`use ValueObject\PlayerId;`). Code läuft scheinbar weil PSR-4-Autoloader keinen `ValueObject\*` Namespace registriert hat — d.h. diese Klassen werden in der Praxis nicht aufgelöst, oder nur über Composer-Class-Loader-Quirks. Klar inkorrekt.

## Risk if ignored

Bricht sobald jemand strikte Static-Analyse oder andere PHP-Versionen nutzt; verwirrt jeden neuen Entwickler; fehlerhafte ValueObject-Verwendung kann zu Type-Bugs führen.

## AC

- [ ] `Planet.php` import: `use App\Planet\ValueObject\PlanetId;`
- [ ] `Player.php` import: `use App\Player\ValueObject\PlayerId;`
- [ ] Alle anderen Files mit gleichem Pattern korrigieren (grep `use ValueObject\\`)
- [ ] `PlayerStartUpScenario` läuft unverändert
- [ ] Existierende Tests grün (sobald T-031 abgeschlossen)

## Refactor Strategy

`grep -rn "use ValueObject\\\\" src/` → alle Stellen anpassen.
