# TD-033: Planet::getResource fragil

**Type:** TechDebt
**Epic:** Tech-Debt & Cleanup
**Domain:** Planet
**Blocked By:** None
**Status:** Done
**Severity:** Medium
**Effort:** S
**Affected Domain(s):** Planet, Resource

## Beschreibung

`Planet::getResource()` und `Planet::getResourceDeposit()` nutzten `current(array_filter(...))`. `current()` returned `false` wenn nichts gefunden — TypeError zur Laufzeit, da Returntype `Resource`/`ResourceDeposit` keine `false` zulässt.

## Risk if ignored

Crash bei Resource-Lookup für nicht-existierenden Type (passiert sicher mit T-001/T-002).

## AC

- [x] `ResourceCollection::getByType(ResourceType): ?Resource`
- [x] `ResourceCollection::getByTypeOrFail(ResourceType): Resource` — wirft `OutOfBoundsException`
- [x] `ResourceDepositCollection::getByType()` + `getByTypeOrFail()` analog
- [x] `Planet::getResource()` nutzt `getByTypeOrFail` (fail-fast)
- [x] `Planet::getResourceDeposit()` nutzt `getByTypeOrFail` (fail-fast)
- [x] IT-Coverage: 7 Tests (4 Resource + 3 Deposit)
- [ ] Variante B (Invariant — Planet hält immer alle ResourceTypes preloaded) — bewusst NICHT umgesetzt: aktuell nur 1 ResourceType (IRON_ORE) im Enum; Invariante macht Sinn sobald T-001/T-002 mehr Types einführen — wird dann dort umgesetzt

## Refactor Strategy

Variante A (Defensive Lookup) gewählt. Variante B (Invariant) wird mit T-001 (Renewable Resources) fällig — Note in TD-033-Folge ergänzt.

### Token Usage (estimate)
- Input: ~3k tokens
- Output: ~2k tokens (3 Edits + 2 Tests)
