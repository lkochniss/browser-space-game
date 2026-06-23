# T-001: Renewable Rohstoffe

**Type:** Feature
**Epic:** Foundation: Resources
**Domain:** Resource
**Blocked By:** None
**Status:** Done
**FX:** No
**MIG:** No (enum-Erweiterung; Spalte bleibt `string length 32`)

## Description

`docs/Rohstoff.md` listet erneuerbare Rohstoffe Wasser/Nahrung/Sauerstoff. Diese 3 als `ResourceType` ergänzt — Voraussetzung für Pop-Verbrauch (T-005), Storage-System (T-061) und Ship-Life-Support (T-012).

## Scope (final)

Daten-Setup + Base-Werte. Keine Tick-Produktion (T-005/T-006). Keine Storage-Caps (T-061). Kein Deposit für renewables.

## AC

- [x] `ResourceType` enum erweitert um `WATER`, `FOOD`, `OXYGEN`
- [x] `ClaimStartPlanetCommandService` legt für jeden renewable einen `Resource`-Eintrag mit `amount = 100` an
- [x] Keine `ResourceDeposit` für renewables (Vorkommen-Konzept gilt nur für endliche Rohstoffe)
- [x] `ResourceProductionConfig` kennt Base-Werte: `WATER=5.0`, `FOOD=3.0`, `OXYGEN=0.0`
- [x] Bestehende Tests grün (21/21, vorher 17 + 4 neue Service-IT)

## Implementation

- `Resource::generateWithAmount(ResourceType, int)` neu — Factory für initialisierte Resources
- `ClaimStartPlanetCommandService::RENEWABLES` Konstanten-Liste; Loop legt Resources mit `RENEWABLE_START_AMOUNT = 100` an
- IT `tests/Planet/Service/ClaimStartPlanetCommandServiceTest`:
  - IRON_ORE bleibt bei 0 + Deposit 1000
  - 3 renewables bei 100
  - Renewables ohne Deposit (Deposit-Count = 1)
  - Persistierung über `clear() + find()` reloaded korrekt 4 Resources

## Folge-Tickets

- T-061 Storage-System (Lager-Kapazität, Speicher-Gebäude)
- T-005 Pop-Verbrauch (verknüpft Resource-Diff mit Population)

### Token Usage (estimate)
- Input: ~6k
- Output: ~3k
