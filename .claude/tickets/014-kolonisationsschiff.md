# T-014: Kolonisationsschiff + Besiedlungsflow

**Type:** Feature
**Status:** Done
**FX:** No
**MIG:** No (ShipType ist string-Spalte, neue Enum-Werte ohne Schema-Change)
**Depends on:** T-007, T-012

## Description

Kolonisations-Foundation. COLONY_SHIP wird beim Kolonisieren verbraucht (User-Decision: Single-Use macht das Asset wertvoll). Pop wird vom Heimatplaneten zum Zielplaneten transferiert.

Refactoring im Zuge: BuildShipCommandService nutzt jetzt `ShipCostConfig` statt Konstanten — Foundation für T-015/T-016.

## AC

- [x] `ShipType::COLONY_SHIP` enum-case
- [x] `ShipCostConfig` Service: Cost+Pop+Duration pro ShipType
  - GENERIC: 100 IRON_BAR, 20 Pop, 30min (T-012-konform)
  - COLONY_SHIP: 300 IRON_BAR, 50 Pop, 60min (Strategic-Tier)
- [x] `BuildShipCommandService` refactored: nutzt `ShipCostConfig` (Konstanten entfernt)
- [x] `ColonizePlanetCommand` (shipId, targetPlanetId)
- [x] `ColonizePlanetCommandHandler` + `ColonizePlanetCommandService`
- [x] Validation: ShipNotFound, NotAColonyShip, ShipNotReady, ColonyShipNotDocked, PlanetNotFound, PlanetAlreadyClaimed
- [x] Pop-Transfer: Heimat verliert assigned-Pop (release+kill), Target erhält Start-Pop via grow
- [x] Player claimt Target-Planet (über Player::claimPlanet)
- [x] Schiff wird nach erfolgreicher Kolonisation aus DB entfernt
- [x] Tests: 2 Unit (ShipCostConfig), 1 IT (Build-Colony-Ship), 7 IT (ColonizePlanet)
- [x] Suite grün (284/284, 615 assertions)

## Out of Scope (Folge-Tickets)

- **Erkundungs-Check** (Discovery-Required vor Kolonisation) → T-087 Fog-of-War
- **Movement-Time** (Schiff fliegt zum Ziel statt magisch zu kolonisieren) → T-017 Flotte-Movement
- **Mark-Tier Colony-Ships** (Mk II behält Schiff) → T-102 Mark-Tier-Pattern
- **Bubble-Trigger** (T-150: Bubble endet bei 2. Planet) → T-150 nutzt `Player::claimPlanet`-Hook später

## Geklärte Fragen

1. **Ship-Lifecycle:** Verbraucht beim Kolonisieren — Schiff weg, Pop transferiert.
2. **Erkundungs-Check:** Skip in T-014 — kommt mit T-087.
3. **Cost-Service:** ShipCostConfig erstellt (analog ProbeCostConfig), GENERIC + COLONY_SHIP konfiguriert. Pattern ready für T-015/T-016.

## Files

**Neu:**
- `src/Ship/Service/ShipCostConfig.php`
- `src/Planet/Command/{ColonizePlanetCommand,ColonizePlanetCommandHandler}.php`
- `src/Planet/Service/ColonizePlanetCommandService.php`
- `src/Planet/Exception/{ShipNotFoundException,NotAColonyShipException,ShipNotReadyException,ColonyShipNotDockedException,PlanetAlreadyClaimedException,PlanetNotFoundException}.php`
- `tests/Ship/Service/ShipCostConfigTest.php`
- `tests/Planet/Command/ColonizePlanetCommandTest.php`

**Geändert:**
- `src/Ship/ValueObject/ShipType.php` (COLONY_SHIP enum-case)
- `src/Ship/Service/BuildShipCommandService.php` (Konstanten weg, ShipCostConfig injiziert)
- `src/Ship/Model/Ship.php` (DEFAULT_POPULATION_COST-Konstante entfernt — jetzt in ShipCostConfig)
- `tests/Ship/Command/BuildShipCommandTest.php` (+1 Test für COLONY_SHIP-Build)
