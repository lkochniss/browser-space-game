# T-012: Raumschiff-Basis + Life-Support

**Type:** Feature
**Status:** Done
**FX:** No
**MIG:** Yes (`Version20260619000004` — ships table)
**Depends on:** T-001, T-011

## Description

Schiff-Foundation: minimaler Stub damit T-013ff (Sonden, Kolo, Transport, Salvage) und T-102 Combat-Klassen darauf aufbauen können. ShipType ist Foundation-Stub mit nur `GENERIC` — Folge-Tickets erweitern den Enum.

## AC

- [x] Domain `src/Ship/` (ValueObject, Model, Repository, Command, Service, Exception)
- [x] `Ship` Entity (id, type, planet als dockedAt, populationAssigned, supplyWater/Food/Oxygen, supplyCapacity, finishedAt für Wallclock-Build)
- [x] `ShipType` Enum (Stub: nur `GENERIC`)
- [x] `ShipId` ValueObject + `ShipIdType` Doctrine-Custom-Type, registriert in `doctrine.yaml`
- [x] `ShipRepository` (mit `findByPlanet`)
- [x] Migration `Version20260619000004` (ships table mit FK auf planets)
- [x] `BuildShipCommand` + `BuildShipCommandHandler` + `BuildShipCommandService`
  - Cost: 100 IRON_BAR, 20 Pop (assigned)
  - Voraussetzung: `Planet::hasShipyard()` (T-011) — sonst `MissingShipyardException`
  - Wallclock-Bauzeit: 30min
  - Validierungen: PlanetNotFound, InsufficientResources, InsufficientPopulation
- [x] `ShipSupplyProcessor` (Tick): docked → drain Planet-Storage; bei Mangel Fallback auf Schiff-Storage; bei Komplett-Mangel → Ship-Death + Pop-Verlust
- [x] Tests: 7 Unit (Ship), 7 IT (BuildShipCommand), 4 IT (ShipSupplyProcessor)
- [x] Bestehende Tests grün (248/248, 530 assertions)

## Out of Scope (Folge-Tickets)

- **Trümmerfeld bei Schiff-Death** → T-021 (entscheidet später, wie Death-Event in DebrisField materialisiert wird)
- **Treibstoff/Promethium** → T-066 + T-105
- **Schiff-Movement / undocked-State** → T-017
- **Mark-Tier / Combat-Klassen / Cost-Variation pro Type** → T-102 + T-128

## Geklärte Fragen

1. **ShipType-Tiefe:** Foundation-Stub mit nur `GENERIC`. Folge-Tickets erweitern.
2. **Trümmerfeld bei Death:** Skip in T-012 — Schiff wird gelöscht, Pop verloren, Trümmerfeld-Spawn kommt mit T-021.
3. **BuildShip-Scope:** Minimal-Build (hardcoded 100 IRON_BAR + 20 Pop + 30min Bauzeit). Cost-Config-Service kommt mit T-102 Mark-Tier-System.
4. **Pop-Death-Mechanik:** `release(20) + kill(20)` — Pop wird entzogen UND verloren (Crew umgekommen).

## Files

**Neu:**
- `src/Ship/ValueObject/ShipId.php`, `ShipType.php`
- `src/Common/Doctrine/Type/ShipIdType.php`
- `src/Ship/Model/Ship.php`
- `src/Ship/Repository/ShipRepository.php`
- `src/Ship/Command/BuildShipCommand.php`, `BuildShipCommandHandler.php`
- `src/Ship/Service/BuildShipCommandService.php`
- `src/Ship/Exception/{PlanetNotFoundException,MissingShipyardException,InsufficientResourcesException,InsufficientPopulationException}.php`
- `src/Tick/Processor/ShipSupplyProcessor.php`
- `migrations/Version20260619000004.php`
- `tests/Ship/Model/ShipTest.php`
- `tests/Ship/Command/BuildShipCommandTest.php`
- `tests/Tick/Processor/ShipSupplyProcessorTest.php`

**Geändert:**
- `config/packages/doctrine.yaml` (ship_id type registriert)
