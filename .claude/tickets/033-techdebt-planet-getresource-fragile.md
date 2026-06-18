# TD-033: Planet::getResource fragil

**Type:** TechDebt
**Status:** Open
**Severity:** Medium
**Effort:** S
**Affected Domain(s):** Planet, Resource

## Beschreibung

`src/Planet/Model/Planet.php:87-93`:
```php
public function getResource(ResourceType $resourceType): Resource
{
    return current(array_filter(
        $this->resources->toArray(),
        fn(Resource $resource) => $resource->getType() === $resourceType
    ));
}
```

`current()` returnt `false` wenn nichts gefunden — TypeError zur Laufzeit (Methode deklariert `Resource` als Returntype, `false` ist kein `Resource`). Gleiches Problem in `getResourceDeposit()`.

## Risk if ignored

Crash sobald Resource-Type abgefragt wird, der noch nicht im Planet-Lager existiert (z.B. nach T-001/T-002 Erweiterung). Passiert garantiert.

## AC

- [ ] Methode auf `ResourceCollection` verschoben: `ResourceCollection::getByType(ResourceType): ?Resource` oder `getByTypeOrFail()`
- [ ] Verwendung in Processor explizit fail-fast (`getByTypeOrFail`)
- [ ] Initialisierung bei Planet-Generation füllt **alle** ResourceTypes auf (auch wenn 0) → keine Misses zur Laufzeit
- [ ] Gleiche Behandlung für `ResourceDepositCollection::getByType()`
- [ ] Integration-Test deckt fehlende Resource ab (sobald T-031 fertig)

## Refactor Strategy

Variante A (defensiv): jede Collection bietet beide APIs (`getByType` nullable + `getByTypeOrFail`).
Variante B (invariant): Planet hält IMMER alle ResourceTypes als Resource(0) — Lookup fail-fast garantiert.

Empfehlung: B — saubere Invariante.
