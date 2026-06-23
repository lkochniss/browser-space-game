# T-004: Population on Planet

**Type:** Feature
**Epic:** Foundation: Population
**Domain:** Planet
**Blocked By:** None
**Status:** Done
**FX:** No
**MIG:** Yes (`Version20260618000002` — 3 Spalten auf `planets`)

## Description

`docs/Bevölkerung.md`: Population ist Kernzahl pro Planet. `total / assigned / cap`, `free = total - assigned`. Verbrauch / Hungerlogik kommen separat (T-005). T-004 liefert das Datenmodell + Operationen.

## Scope (final)

Embeddable VO `Population` auf `Planet`. assign/release/grow/kill/setCap. Kein Tick-Verbrauch heute.

## AC

- [x] `Population` Embeddable VO unter `src/Planet/Model/` (`#[ORM\Embeddable]`)
- [x] Felder: `total`, `assigned`, `cap`. `free = total - assigned`
- [x] `Planet::population` via `#[ORM\Embedded(columnPrefix: 'population_')]` → Spalten `population_total/_assigned/_cap`
- [x] Methoden: `assign(int)`, `release(int)`, `grow(int)`, `kill(int)`, `setCap(int)`
- [x] Invariants im Constructor + bei jeder Mutation:
  - alle Werte ≥ 0
  - `assigned ≤ total ≤ cap`
- [x] `kill(amount)` killt zuerst aus `free`, dann aus `assigned` (lt. Doc-Reihenfolge)
- [x] `grow(amount)` cappt automatisch bei `cap` (kein Throw)
- [x] `setCap(newCap)` schmiegt `total`/`assigned` runter, falls `newCap < total`
- [x] `Planet::__construct` initialisiert mit `Population::empty()` → `(total=0, assigned=0, cap=100)`
- [x] `ClaimStartPlanetCommandService` ruft `population->grow(50)` nach Claim → Start-Planet hat `(50, 0, 100)`
- [x] Migration `Version20260618000002` fügt 3 Spalten an `planets` an (default-Werte für existierende Rows)
- [x] Bestehende Tests grün (54/54, +15 neue: 13 PopulationTest unit + 2 IT)

## Geklärte Fragen

1. **Modell:** Embeddable VO (Doctrine `#[Embedded]`)
2. **Domain:** Sub von Planet (`src/Planet/Model/Population.php`)
3. **Start-Werte:** total=50, assigned=0, cap=100
4. **Hungerlogik:** Nicht in T-004 (kommt mit T-005)

## Affected

- `src/Planet/Model/Population.php` (neu)
- `src/Planet/Model/Planet.php` (Embedded-Field + Getter)
- `src/Planet/Service/ClaimStartPlanetCommandService.php` (`grow(50)` nach Claim)
- `migrations/Version20260618000002.php` (neu)
- `tests/Planet/Model/PopulationTest.php` (neu, 13 Cases)
- `tests/Planet/Service/ClaimStartPlanetCommandServiceTest.php` (+1 Pop-Assertion)
- `tests/Persistence/PlayerPlanetPersistenceTest.php` (+1 IT für Pop-Persistierung)

## Inconsistencies / Notes

- Embeddable kann (heute) nicht null sein → `Planet::__construct` MUSS Pop initialisieren. `Population::empty()` ist die Lösung.
- Doctrine 3 hydriert Embeddable via `newInstanceWithoutConstructor` — Invariants im Constructor werden bei DB-Reload **nicht** validiert. Akzeptiert: Trust-DB-Konsistenz, alle Mutations gehen durch Methoden mit Guards.

### Token Usage (estimate)
- Input: ~10k
- Output: ~5k
