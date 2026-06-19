# T-002: Endliche Rohstoffe — Extend

**Type:** Feature
**Status:** Done
**FX:** No
**MIG:** No (enum-Erweiterung; Spalte bleibt `string length 32`)

## Description

`docs/Rohstoff.md` definiert endliche Rohstoffe: Eisenerz (✓), Kohle, Kupfererz, Silizium, Aluminiumerz, Titanerz, Uranerz. Alle 6 fehlenden als `ResourceType` ergänzt + dedizierte Mines + Base-Werte.

## AC

- [x] `ResourceType`: `COAL`, `COPPER_ORE`, `SILICON`, `ALUMINUM_ORE`, `TITANIUM_ORE`, `URANIUM_ORE`
- [x] `BuildingType`: `COAL_MINE`, `COPPER_MINE`, `SILICON_MINE`, `ALUMINUM_MINE`, `TITANIUM_MINE`, `URANIUM_MINE` (1 Mine pro Erz, lt. Entscheidung)
- [x] `ResourceProductionConfig` Base-Werte gestaffelt nach Seltenheit:
  - COAL=15, COPPER_ORE=8, SILICON=6, ALUMINUM_ORE=8, TITANIUM_ORE=4, URANIUM_ORE=2
- [x] `ResourceBuildingMap` mit allen 7 Erz↔Mine-Mappings (Multiplier 1.0)
- [x] Pre-existing Bug in `ResourceBuildingMap::canProduce` gefixt (Enum vs String-Compare)
- [x] Start-Planet bleibt nur mit IRON_ORE-Deposit (lt. Entscheidung — andere Erze kommen mit T-008/T-019/T-020)
- [x] Bestehende Tests grün (39/39, +18 neue Unit-Tests via DataProvider für Map + Config)

## Geklärte Fragen

1. **Mine-Modell:** 1 Mine pro Erz (spezialisiert) — gut für T-009 / T-025
2. **Forschung-Gating:** Nicht in T-002 — kommt mit T-025/T-026
3. **Start-Deposits:** Nur IRON_ORE — andere mit Planet-Typen / POIs
4. **Base-Werte:** gestaffelt nach Seltenheit

## Implementation

- 6 enum cases in `ResourceType` + `BuildingType`
- Map + Config-Erweiterung
- DataProvider-Tests (PHPUnit 11 `#[DataProvider]` Attribut, nicht doc-comment) für regression-safe lock-in

## Bug Fix (in scope)

`ResourceBuildingMap::canProduce(BuildingType, ResourceType)` verglich `BuildingType`-Enum gegen Array von `string`-Values mit `strict=true` → immer false. Fix: `$buildingType->value` vergleichen.

Vorher unentdeckt, weil `canProduce` nirgends genutzt wurde. Mit T-002 gibt's jetzt Tests dafür.

## Folge

- `ResourceProductionHelper` umgeht das Map-API mit `->value` direkt — könnte auf `canProduce` umsteigen (TechDebt-Kandidat, aber low impact)

### Token Usage (estimate)
- Input: ~7k
- Output: ~3k
