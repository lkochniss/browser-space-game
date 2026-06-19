# TD-059: PlanetCollection löschen (unused)

**Type:** TechDebt
**Status:** Done
**Severity:** Low
**Effort:** S (< 1h)
**Affected Domain(s):** Planet

## Beschreibung

Nach TD-032 (Phase 3) nutzt `Player::planets` das Doctrine `Collection`-Interface direkt. `App\Planet\Model\PlanetCollection` (extends `ArrayCollection`) wurde nirgends mehr referenziert.

## Acceptance Criteria

- [x] `src/Planet/Model/PlanetCollection.php` gelöscht
- [x] `grep -rn "PlanetCollection"` über `src/` und `tests/` zeigt 0 Treffer
- [x] Alle bestehenden Tests grün (15/15)
- [x] `doctrine:mapping:info` weiterhin OK (5 Entities)

## Refactor Strategy

- File gelöscht
- grep-Verifikation: clean
- Tests laufen lassen: clean

### Token Usage (estimate)
- Input: ~1k
- Output: ~0.5k
