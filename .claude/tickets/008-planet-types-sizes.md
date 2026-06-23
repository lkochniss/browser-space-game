# T-008: Planet-Typen + Größen

**Type:** Feature
**Epic:** Foundation: Planet Types
**Domain:** Planet
**Blocked By:** None
**Status:** Done
**FX:** No
**MIG:** Yes (`Version20260619000002` — planets.type + planets.size)

## Description

`docs/Planet.md`: 5 Größen + verschiedene Typen mit unterschiedlichen Rohstoffen + Boni. Spieler startet auf erdähnlichem Planet. T-008 fügt Type+Size + Mechanik-Wirkung hinzu.

## Scope (final)

- 7 PlanetTypes (TERRAN/BARREN/ICE/GAS_GIANT/OCEAN/VOLCANIC/DESERT)
- 5 PlanetSizes (TINY/SMALL/MEDIUM/LARGE/HUGE)
- Type → Renewable-Consumption-Multiplier (W/F)
- Type → Deposit-Bias (welche Erze)
- Size → Deposit-Mengen-Multiplier (0.5x–2.0x)
- Start-Planet hard TERRAN+MEDIUM, andere 4 random
- Boni-System spec konkretisieren = T-063 Folge-Ticket

## AC

- [x] `PlanetSize` enum (TINY, SMALL, MEDIUM, LARGE, HUGE) + `getDepositMultiplier()` (0.5/0.75/1.0/1.5/2.0)
- [x] `PlanetType` enum (7 Typen) + `getConsumptionMultiplier(ResourceType)` + `generateDeposits(PlanetSize)`
- [x] `Planet` hält `type` + `size` (+ Migration `planets.type` + `planets.size`)
- [x] `Planet::generatePlanet(id, type=TERRAN, size=MEDIUM)` Factory mit defaults
- [x] `ClaimStartPlanetCommandService`:
  - Start-Planet hard TERRAN + MEDIUM
  - 4 unowned Planeten in anderen Systemen mit random Type + random Size
  - Random-Planet-Deposits via `PlanetType::generateDeposits(size)`
- [x] `PopulationConsumptionProcessor` multipliziert per-capita W/F mit `planet.type.getConsumptionMultiplier()`
  - DESERT: 1.5x Verbrauch (Malus W+F)
  - OCEAN: 0.5x Wasser (Bonus)
  - ICE: 0.5x Wasser, 1.2x Food
  - VOLCANIC: 1.3x Wasser, 1.2x Food
  - BARREN: 1.5x Food
  - TERRAN/GAS_GIANT: neutral
- [x] Bestehende Tests grün (143/143, +21: 5 PlanetSize, 12 PlanetType, 2 PopConsumption type-multi, 2 IT)

## Geklärte Fragen

1. **Type-Liste:** Volle 7 (TERRAN/BARREN/ICE/GAS_GIANT/OCEAN/VOLCANIC/DESERT)
2. **Size-Wirkung:** Deposit-Mengen-Multiplier (Pop-Cap-Base unverändert, kein Building-Slot-System)
3. **Type-Wirkung:** Consumption-Multiplier auf W/F + Deposit-Bias
4. **Deposit-Generation:** Type-basiert via `PlanetType::generateDeposits(PlanetSize)` mit Size-Multi
5. **Boni-System:** Folge-Ticket T-063 (Doc unvollständig)

## Implementation

- `src/Planet/ValueObject/PlanetSize.php` (neu)
- `src/Planet/ValueObject/PlanetType.php` (neu)
- `src/Planet/Model/Planet.php` (+ type/size Felder, Factory mit defaults)
- `src/Tick/Processor/PopulationConsumptionProcessor.php` (Type-Multiplier auf perCapita)
- `src/Planet/Service/ClaimStartPlanetCommandService.php` (random rolls für 4 unowned Planets, Type-basierte Deposits)
- `migrations/Version20260619000002.php` (neu)
- `tests/Planet/ValueObject/PlanetSizeTest.php` (5 DataProvider cases)
- `tests/Planet/ValueObject/PlanetTypeTest.php` (12 cases)
- `tests/Tick/Processor/PopulationConsumptionProcessorTest.php` (+2: DESERT-Malus, OCEAN-Bonus)
- `tests/Planet/Service/ClaimStartPlanetCommandServiceTest.php` (+2: Start TERRAN+MEDIUM, andere random)

## Edge Cases (getestet)

- TERRAN consumption neutral
- DESERT W+F malus 1.5x
- OCEAN water bonus 0.5x, food neutral
- ICE water bonus + food malus
- VOLCANIC W+F malus
- TERRAN MEDIUM Deposits: 500 Iron + 300 Coal
- HUGE doppelt, TINY halbiert
- GAS_GIANT keine Deposits
- BARREN/DESERT/VOLCANIC haben jeweils 2 Deposit-Typen

## Folge-Hinweise

- **T-063 Boni-System:** Doc erwähnt "Boni" — Spec konkretisieren (vielleicht Production-Bonus pro Type, Forschungs-Bonus etc.)
- **T-019/T-020 POIs:** Random-Deposit-Generation hier ist deterministisch (alle Planets gleichen Typs identisch). POIs bringen Variabilität
- **Pop-Cap-Base nicht size-abhängig:** Planet.BASE_POPULATION_CAP = 100 fix. Future: TINY=50, HUGE=200?
- **GAS_GIANT habitability:** Aktuell hat es keine Mechanik die Bewohnung blockiert — bei T-014 Kolonisation prüfen

### Token Usage (estimate)
- Input: ~13k
- Output: ~6k
