# T-063: Planet-Boni-System

**Type:** Feature
**Status:** In Progress
**FX:** No
**MIG:** No
**Depends on:** T-008 ✓

## Description

`docs/Planet.md` erwähnt "Boni-System" ohne Spec. T-063 konkretisiert: PlanetType liefert Bonus-Grundwerte; PlanetSize multipliziert Magnitude. Effekte: Mining-Output, Refinement-Output, Pop-Wachstumsrate, Bauzeit.

## Scope

Per `PlanetType`:
- `getMiningBonus(ResourceType): float` — Bonus auf Mining-Output für gegebenen Erz-Typ
- `getRefinementBonus(ResourceType): float` — Bonus auf Refinement-Output (heute alle 0, ausbaubar)
- `getPopGrowthBonus(): float` — Bonus auf Logistic-Growth-Rate
- `getConstructionSpeedBonus(BuildingType): float` — Bonus auf Bau-Geschwindigkeit (faster)

Per `Planet` (Helper, kombiniert Type × Size):
- `getEffectiveMiningMultiplier(ResourceType): float`
- `getEffectiveRefinementMultiplier(ResourceType): float`
- `getEffectivePopGrowthMultiplier(): float`
- `getEffectiveConstructionSpeedMultiplier(BuildingType): float`

Formel: `multiplier = max(0, 1 + typeBonus × sizeFactor)`.
Construction-Spezifikum: `max(0.1, …)` (nie unter 10% baseDuration).
sizeFactor = `PlanetSize::getDepositMultiplier()` (TINY 0.5 → HUGE 2.0).

## AC

- [ ] `PlanetType` mit 4 Bonus-Methoden + Defaults
- [ ] Bonus-Werte pro Type:
  - **TERRAN:** alles 0 (Baseline)
  - **BARREN:** Mining +0.5 für IRON_ORE/COPPER_ORE; Pop-Growth -0.1
  - **DESERT:** Mining +1.0 SILICON, +0.5 TITANIUM_ORE; Pop-Growth -0.2 (W/F-Malus aus T-008 ergänzt)
  - **ICE:** Mining +0.5 SILICON; Pop-Growth -0.3 (W-Bonus aus T-008)
  - **VOLCANIC:** Mining +1.0 URANIUM_ORE, +0.5 IRON_ORE; Pop-Growth -0.1
  - **OCEAN:** Mining +0.5 ALUMINUM_ORE; Pop-Growth +0.1
  - **GAS_GIANT:** Mining -1.0 alles, Pop-Growth -0.5 (kein Habitat)
  - Refinement-Bonus heute überall 0 (Tunbar)
  - Construction-Speed-Bonus: BARREN +0.2 für Mines (Folge-Tuning später)
- [ ] `Planet` Effective-Multiplier-Helpers
- [ ] `ResourceProductionProcessor` multipliziert mit `getEffectiveMiningMultiplier`
- [ ] `RefinementProductionProcessor` multipliziert mit `getEffectiveRefinementMultiplier`
- [ ] `PopulationConsumptionProcessor` multipliziert Logistic-Rate mit `getEffectivePopGrowthMultiplier`
- [ ] `BuildBuildingCommandService` + `UpgradeBuildingCommandService` dividieren Duration durch `getEffectiveConstructionSpeedMultiplier`
- [ ] Tests
- [ ] Bestehende Tests grün

## Geklärte Fragen

1. **Boni-Domain:** Mining + Refinement + Pop-Growth + Construction-Speed (alle 4)
2. **Quelle:** PlanetType-Grundwert × PlanetSize-Multi (size verstärkt Bonus)
3. **Werte:** TERRAN baseline 0, andere wie oben
4. **Stacking:** Multiplikativ (`baseLevel × buildingMulti × planetTypeMulti`)

## Folge-Hinweise

- Refinement-Boni heute alle 0 — User-Vision "TERRAN +25% Smelter" als Tuning-Punkt
- T-064 Construction-Speed-Boost (Forschung+Buildings) komponiert mit T-063 (PlanetType): beide multiplikativ
- T-024 Raumschlacht könnte Planet-Type-Defense-Bonus ergänzen (eigenes Folge-Ticket)
