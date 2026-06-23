# T-016: Bergungsschiff + Salvage

**Type:** Feature
**Epic:** Ships & Fleet
**Domain:** Ship
**Blocked By:** T-012, T-020
**Status:** Done
**FX:** No
**MIG:** Yes (`Version20260619000013` — ships.salvage_*)
**Depends on:** T-012 (Schiff-Foundation), T-020 (AsteroidField done)
**Blocks:** —

## Description

Echtzeit-Salvage. Player startet Salvage explicit (StartSalvageCommand), Schiff
extrahiert mit fixer Rate (Units/Minute) bis Field-Empty oder Cargo-Voll. Manueller
Stop möglich via StopSalvageCommand.

User-Vision: Echtzeit-Mechanik mit Rate × Zeit, Schiff hält Salvage-State persistent.
Tick-Service rechnet pro Tick den delta-Zeit-Auszug.

## AC

- [x] `ShipType::SALVAGE` enum-case + isSalvage()-Helper
- [x] `ShipType::getSalvageRatePerMinute()`: 50 Units/min für SALVAGE, 0 für andere
- [x] `ShipType::getSpeed()`: SALVAGE = 0.8 (mittelträge wegen Bergungs-Equipment)
- [x] ShipCostConfig SALVAGE-Eintrag: 250 Iron-Bar + 50 Aluminum-Ore, 25 Pop, 45min Build, 3000 Cargo
- [x] Ship-Entity erweitert um Salvage-State:
  - `salvageTargetPoiId` (CHAR(36), nullable)
  - `salvageResourceType` (string, nullable, ResourceType-value)
  - `salvageLastTickAt` (DateTimeImmutable, nullable)
  - API: `isSalvaging`, `startSalvage`, `updateSalvageTick`, `stopSalvage`
- [x] Migration `Version20260619000013`
- [x] `StartSalvageCommand` (shipId, poiId, resourceType) + Handler + Service:
  - Validation: Ship existiert, isReady, ist SALVAGE-Type, POI existiert, POI ist
    AsteroidField, Field hat Resource verfügbar, Schiff im selben System
  - Ship.system-Resolution: via Fleet (DOCKED only) oder direct ship.planet
  - IN_TRANSIT-Fleet rejected (Schiff unterwegs)
- [x] `StopSalvageCommand` + Handler + Service (idempotent, no-op wenn nicht aktiv)
- [x] `SalvageProcessor` (globaler Tick-Service analog FleetArrivalService):
  - findet Schiffe mit aktivem Salvage via SalvagingShipRepository
  - delta = now - salvageLastTickAt → extractable = floor(delta-min × rate)
  - Limitiert durch field-amount + ship-cargo-free
  - Stop bei Field-Empty oder Cargo-Voll
  - Field-Cleanup: AsteroidField.isEmpty() → em->remove(field)
- [x] `SalvagingShipRepository` (DQL-Wrapper für Active-Salvager-Query)
- [x] 4 Domain-Exceptions: NotASalvageShip, SalvageTargetNotInSystem,
  InvalidSalvageTarget, PoiNotFound
- [x] `services.yaml`: SalvageProcessor public (Container-Lookup für Tests + T-044)
- [x] Tests: 6 Unit (ShipType.salvageRate, isSalvage), 9 IT (Salvage-Commands),
  4 IT (SalvageProcessor mit Field-Empty / Cargo-Voll / Skip-Tick)
- [x] Suite grün (394/394, 1388 assertions)

## Geklärte Fragen

1. **Schiff-Klasse:** Eigene ShipType::SALVAGE.
2. **Trigger:** Echtzeit, manuell gestartet, läuft bis Field-Empty oder Cargo-Voll.
   Rate: 50 Units/Minute (T-127 Mining-Branch kann skalieren).
3. **Cargo-Mechanik:** Schiff hält geborgene Resources im CargoManifest (T-015 reuse).
   Player nutzt UnloadCargo zum Heim-Transfer.
4. **Asteroid vs Debris:** T-016 Foundation = nur AsteroidField. T-021 ergänzt
   DebrisField-Salvage als eigenes AC (kann den existing SalvageProcessor reuse'n
   per Type-Check + DebrisField-Discriminator).

## Out of Scope (Folge-Tickets)

- **DebrisField-Salvage** → T-021 (Trümmerfeld Foundation + DebrisField-extract)
- **Discovery-Required vor Salvage** → T-087 Fog-of-War
- **Treibstoff während Salvage** → T-066 + T-105
- **Salvage-Effizienz pro Schiff-Klasse / Tier-Forschung** → T-127 Mining/Industrie
- **Auto-Tick-Integration** → T-044 Tick-Scheduler ruft `SalvageProcessor::runTick`

## Files

**Neu:**
- `src/Ship/Repository/SalvagingShipRepository.php`
- `src/Ship/Service/{StartSalvageCommandService,StopSalvageCommandService,SalvageProcessor}.php`
- `src/Ship/Command/{StartSalvageCommand,StartSalvageCommandHandler,StopSalvageCommand,StopSalvageCommandHandler}.php`
- `src/Ship/Exception/{NotASalvageShipException,SalvageTargetNotInSystemException,InvalidSalvageTargetException,PoiNotFoundException}.php`
- `migrations/Version20260619000013.php`
- `tests/Ship/ValueObject/SalvageRateTest.php`
- `tests/Ship/Command/SalvageCommandTest.php`
- `tests/Ship/Service/SalvageProcessorTest.php`

**Geändert:**
- `src/Ship/ValueObject/ShipType.php` (+ SALVAGE + getSalvageRatePerMinute + isSalvage)
- `src/Ship/Service/ShipCostConfig.php` (+ SALVAGE-Eintrag)
- `src/Ship/Model/Ship.php` (+ salvage_target_poi_id/salvage_resource_type/salvage_last_tick_at + API)
- `config/services.yaml` (SalvageProcessor public)
