# Population

## Modell

`Population` ist ein Embeddable Value Object (`src/Planet/Model/Population.php`), `#[ORM\Embeddable]`. Eingebettet in `Planet` über `#[ORM\Embedded(columnPrefix: 'population_')]` → Spalten auf `planets`-Tabelle.

| Feld | DB-Spalte | Typ | Beschreibung |
|------|-----------|-----|--------------|
| `total` | `population_total` | integer | Gesamtbevölkerung |
| `assigned` | `population_assigned` | integer | gebunden durch Buildings/Schiffe |
| `cap` | `population_cap` | integer | maximale Pop auf dem Planet (Hub erhöht — T-006) |
| `free` (computed) | — | int | `total - assigned` |

## Invarianten

- Alle Werte ≥ 0
- `assigned ≤ total ≤ cap`
- Verletzung im Constructor / Mutator → `InvalidArgumentException`

## Operationen

| Methode | Effekt |
|---------|--------|
| `Population::empty(int $cap = 100)` | Factory: `(0, 0, $cap)` |
| `assign(int $amount)` | Erhöht `assigned`. Wirft, wenn `> free` |
| `release(int $amount)` | Reduziert `assigned`. Wirft, wenn `> assigned` |
| `grow(int $amount)` | Erhöht `total`. Cappt automatisch bei `cap` |
| `kill(int $amount)` | Tötet zuerst aus `free`, dann aus `assigned`. Cappt bei 0 |
| `setCap(int $cap)` | Senkt `total`/`assigned` mit, falls `cap` darunter sinkt |

## Defaults

- Neuer Planet (generate): `(0, 0, 100)`
- Claim Start-Planet: `grow(50)` → `(50, 0, 100)`

## Tick-Verbrauch (T-005)

Implementiert via `PopulationConsumptionProcessor` (`src/Tick/Processor/PopulationConsumptionProcessor.php`):

| Aspekt | Wert |
|--------|------|
| Wasser per capita / Tick | 0.1 |
| Nahrung per capita / Tick | 0.1 |
| Wachstumsrate (logistic) | r = 0.1 |
| Wachstumsformel | `delta = round(r * P * (1 - P/cap))` |
| Mangel → Tote | `max(ceil(shortage / perCap))` über W und F |
| Tot-Reihenfolge | free first, dann assigned (`Population::kill`) |
| Mangel + Wachstum | Mangel stoppt Wachstum (early return nach kill) |
| Sauerstoff | NICHT in T-005 — kommt mit T-008 (Planet-Typen) |
| Type-Multiplier (T-008) | `effPerCap = baseCap × planet.type.getConsumptionMultiplier(resource)` |

`PopulationConsumptionConfig` als injizierbarer Service. Reihenfolge im TickEngine: Production VOR Consumption.

### Planet-Type Consumption-Multiplier (T-008)

| Type | Wasser | Nahrung |
|------|--------|---------|
| TERRAN | 1.0 | 1.0 |
| BARREN | 1.0 | 1.5 |
| ICE | 0.5 | 1.2 |
| GAS_GIANT | 1.0 | 1.0 |
| OCEAN | 0.5 | 1.0 |
| VOLCANIC | 1.3 | 1.2 |
| DESERT | 1.5 | 1.5 |

## Cap-Berechnung (T-006)

`Planet::BASE_POPULATION_CAP = 100`. Effektiver Cap = `BASE + Σ(building.type.populationCapBonusPerLevel * building.level)`.

| Building | Bonus / Level |
|----------|---------------|
| `HUB` | +50 |
| Mines (alle) | 0 |

`Planet::recalculatePopulationCap()` wird auto-getriggert in `addBuilding()`. Level-Änderungen via `Building::setLevel()` müssen explizit `Planet::recalculatePopulationCap()` rufen — T-010 Upgrade übernimmt das.

## Geplant

- **T-024:** Raumschlacht ruft `kill()` für verlorene Schiffe.
- **T-061:** Storage-System cappt Resource-Mengen.
- **T-063:** Boni-System (Planet-Type-Bonus auf Production etc.)
- Pop-Cap-Base size-abhängig (heute fix 100; zukünftig TINY=50, HUGE=200)
- Sauerstoff-Verbrauch je Planet-Type (heute alle Types O2-neutral)

## Hydration-Caveat

Doctrine erstellt Embeddables via `newInstanceWithoutConstructor` beim Reload — Constructor-Invariants werden NICHT geprüft. Konsistenz wird durch Mutator-Guards gewährleistet, DB-Konsistenz wird vertraut.
