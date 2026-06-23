# T-005: Population-Verbrauch pro Tick

**Type:** Feature
**Epic:** Foundation: Population
**Domain:** Planet
**Blocked By:** T-001, T-004
**Status:** Done
**FX:** No
**MIG:** No
**Depends on:** T-001 ✓, T-004 ✓

## Description

`docs/Bevölkerung.md`: Pro Tick verbraucht Pop Wasser + Nahrung. Bei Mangel: Wachstum stoppt + Menschen sterben (free first, dann assigned). Bei Überschuss: logistisches Wachstum bis Cap.

## Scope (final)

`PopulationConsumptionProcessor` als TickProcessor. Konsum + Mangel + Wachstum. Sauerstoff bleibt für T-008. "Gebäude wieder versorgen" beim Recovery bleibt für T-009.

## AC

- [x] `PopulationConsumptionProcessor` (`TickProcessorInterface`)
- [x] `PopulationConsumptionConfig` mit per-capita Wasser=0.1, Nahrung=0.1; Wachstumsrate r=0.1
- [x] Pro Tick: `pop.total * waterPerCapita` aus Wasser-Resource ziehen (clamped)
- [x] Pro Tick: `pop.total * foodPerCapita` aus Nahrung-Resource ziehen (clamped)
- [x] Bei Mangel:
  - [x] Wachstum stoppt (early return nach `kill`)
  - [x] `kill(deaths)` → erst free, dann assigned (via `Population::kill`)
  - [x] `deaths = max(deathsFromWater, deathsFromFood)`; `deathsFromX = ceil(shortage / perCapita)`
- [x] Bei Überschuss: logistic `delta = round(r * P * (1 - P/cap))`; `grow(delta)` (Population cappt automatisch)
- [x] Sauerstoff: out of scope (T-008)
- [x] TickEngine-Reihenfolge: Production VOR Consumption (im Scenario explizit so übergeben)
- [x] `PlayerStartUpScenario` injectet beide Processors
- [x] Bestehende Tests grün (64/64, +10 neue: 9 Unit + 1 IT)

## Geklärte Fragen

1. **Per-capita:** Wasser=0.1 / Nahrung=0.1 (symmetrisch)
2. **Sauerstoff:** Out of scope T-005 → kommt mit T-008 (Planet-Typen)
3. **Wachstumsformel:** Logistisch r=0.1 → `delta = (int) round(r * P * (1 - P/cap))`
4. **Recovery 'Gebäude versorgen':** Out of scope → assigned bleibt unverändert (T-009 wird Building-Pop-Cost tracken)

## Implementation

- `src/Resource/Service/PopulationConsumptionConfig.php` (neu)
- `src/Tick/Processor/PopulationConsumptionProcessor.php` (neu)
- `src/Simulation/Scenario/PlayerStartUpScenario.php` (Constructor + run nutzen beide Processors)
- `tests/Tick/Processor/PopulationConsumptionProcessorTest.php` (neu, 9 Cases)
- `tests/Persistence/TickPersistenceTest.php` (+1 IT für End-to-End persist)

## Edge Cases (getestet)

- Pop=0 → no-op (kein Verbrauch, kein Tot)
- Pop=cap → keine Wachstum (delta=0)
- Wasser-Mangel → Tod-Berechnung aus Wasser-Shortage
- Nahrung-Mangel → Tod-Berechnung aus Food-Shortage
- Limiting-Resource: max(water-deaths, food-deaths)
- Kill-Reihenfolge: free first, dann assigned
- Severe shortage → kill bis assigned

## Folge-Hinweise

- T-006 Hub setzt `population->setCap(newCap)` nach Build/Upgrade
- T-009 Building-Cost ruft `population->assign(cost)` (consumes free) → später auch Recovery-Logic
- T-061 Storage-System wird Cap auf Resource-Lager einführen (heute keine Cap → Wasser/Nahrung kann unendlich akkumulieren wenn Production > Consumption)

### Token Usage (estimate)
- Input: ~12k
- Output: ~5k
