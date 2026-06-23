# TD-030: Deposit kann negativ werden + Level-Math fragwürdig

**Type:** TechDebt
**Epic:** Tech-Debt & Cleanup
**Domain:** Resource
**Blocked By:** None
**Status:** Done
**Severity:** Medium
**Effort:** S
**Affected Domain(s):** Tick, Resource, Building

## Beschreibung

`src/Tick/Processor/ResourceProductionProcessor.php` hatte zwei Probleme:

1. **Deposit-Negativ:** Kein Clamp. Wenn extrahierte Menge > Deposit-Bestand → Deposit wurde negativ, Resource bekam trotzdem volle Menge → Cheating-Vektor.
2. **Level off-by-one:** `($building->getLevel() + 1)` als Multiplikator → Level-1-Building produzierte 2× Base.

## Risk if ignored

Wirtschafts-Bug, Balance-Bug, Cheating-Vektor.

## AC

- [x] Extraktion clamped: `(int) min($desired, $deposit->getAmount())` — nur tatsächlich extrahierte Menge wird abgezogen UND gutgeschrieben
- [x] Level-Math korrigiert: `* $building->getLevel()` (ohne +1). Konvention: **Level 1 = 1× Base**.
- [x] Kommentar im Code (clamp-Begründung)
- [x] Leerer Deposit → 0 extrahiert (Policy + Processor decken das ab)
- [x] IT-Coverage:
  - Level 1 → 1× Base
  - Level 3 → 3× Base
  - Deposit < desired → clamped
  - Empty Deposit → 0/0
- [ ] DepositEmptyEvent — bewusst NICHT umgesetzt, gehört zu T-057 (Domain-Events Foundation) bzw. T-020 (Asteroidenfeld)

## Refactor Strategy

Pure Logik-Fix in `ResourceProductionProcessor::process()`. 4 Integration-Tests in `tests/Tick/Processor/ResourceProductionProcessorTest.php`.

### Token Usage (estimate)
- Input: ~2k tokens
- Output: ~1.5k tokens (Edit + Test)
