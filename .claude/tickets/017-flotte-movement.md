# T-017: Flotte + Bewegung

**Type:** Feature
**Epic:** Ships & Fleet
**Domain:** Fleet
**Blocked By:** T-007, T-012
**Status:** Done
**FX:** No
**MIG:** Yes (`Version20260619000007` — fleets table + ships.fleet_id)
**Depends on:** T-007, T-012

## Description

Persistent-Fleet-Modell: Player legt Fleets manuell an. Schiffe in genau einer Fleet
gleichzeitig. Wallclock-Travel mit Slowest-Ship-bestimmt-Geschwindigkeit. Magic-Dock-
Stub aus T-015 wird durch echtes Movement ersetzt.

## AC

- [x] `Fleet` Entity (id, player, status, originPlanet, targetPlanet, departedAt,
  arrivedAt) + Bidirektionale Helper `attachShip`/`detachShip`
- [x] `FleetStatus` Enum (DOCKED, IN_TRANSIT)
- [x] `FleetId` ValueObject + `FleetIdType` Doctrine-Custom-Type
- [x] `FleetRepository` mit `findArrivedFleets(now)` für Tick-Resolution
- [x] `Ship.fleet` ManyToOne (nullable) — bidirektional via `mappedBy: fleet` auf Fleet.ships
- [x] Migration `Version20260619000007` (fleets + ships.fleet_id)
- [x] `ShipType::getSpeed()` Helper:
  - GENERIC 1.0, COLONY_SHIP 0.7, TRANSPORT_SMALL 1.2, MEDIUM 0.9, LARGE 0.6
- [x] `FleetMovementConfig` mit pauschalen Travel-Times:
  - Intra-System: 1800s (30min)
  - Inter-System: 14400s (4h)
  - Effective duration = baseTime / fleet-min-speed (langsamstes Schiff bestimmt)
  - Min-Clamp 60s
- [x] `CreateFleetCommand` (player, ships) — Validierung: nicht-leer, gleicher Planet,
  ready, nicht in anderer Fleet, gleicher Owner
- [x] `MoveFleetCommand` (fleet, targetPlanet) — Validierung: nicht IN_TRANSIT, target≠origin
  - Setzt status=IN_TRANSIT, ships.planet=null, departedAt/arrivedAt
- [x] `DisbandFleetCommand` (fleet) — nur DOCKED, ships werden ge-detacht, Fleet gelöscht
- [x] `FleetArrivalService::resolveArrivedFleets()` — globaler Tick-Service (kein
  TickProcessorInterface, da nicht Planet-zentriert). Setzt arrivierte Fleets auf
  DOCKED, ships.planet=target, originPlanet=target, targetPlanet=null
- [x] 7 Domain-Exceptions in `src/Fleet/Exception/`
- [x] Cleanup T-015 Magic-Dock: `DockTransportShipCommand` + Handler + Service entfernt;
  `CargoTransferTest` angepasst (test_dock_changes_ships_planet entfernt, _unload_after_dock
  → _unload_after_arrival mit direktem ship.setPlanet)
- [x] `services.yaml`: FleetArrivalService public (Container-Lookup für Tests/Tick-Scheduler)
- [x] Tests: 6 Unit ShipType.getSpeed, 6 Unit FleetMovementConfig, 7 IT CreateFleet,
  6 IT MoveFleet, 3 IT DisbandFleet, 2 IT FleetArrivalService
- [x] Suite grün (335/335, 717 assertions)

## Geklärte Fragen

1. **Fleet-Lifecycle:** Persistent — User legt Fleet manuell an, Fleet bleibt nach
   Ankunft als DOCKED-Container.
2. **Travel-Time:** Slowest-Ship-bestimmt — `min(getSpeed)` × baseTime.
3. **Magic-Dock:** Entfernt. Movement nur via Fleet ab T-017.
4. **Travel-Time-Modell:** Intra-/Inter-System pauschal. Distance-/Hop-Mechanik kommt
   mit T-160 Galaxy-Map.

## Out of Scope (Folge-Tickets)

- **Treibstoff-Verbrauch** → T-066 (Promethium/H2) + T-105 (Maintenance)
- **Inter-System-Distance** → T-160 Galaxy-Map / T-085 Wormhole
- **Combat-Encounter beim Movement** → T-024 / T-103 Battle-Engine + T-074 Pirate-Spawn
- **Auto-Tick-Integration** → T-044 Tick-Scheduler ruft `FleetArrivalService::resolveArrivedFleets`
- **Schiff-Tod bei W/F/O-Mangel im Flug** → T-012 ShipSupplyProcessor handled das schon
  (drain-Logic für undocked Schiffe). Hinweis: die im Flug benötigte Logic war im T-012
  Stub schon enthalten.
- **Antrieb-Tech-Boni** → T-026 + T-128 Schiffbau-Branch

## Files

**Neu:**
- `src/Fleet/ValueObject/{FleetId,FleetStatus}.php`
- `src/Common/Doctrine/Type/FleetIdType.php`
- `src/Fleet/Model/Fleet.php`
- `src/Fleet/Repository/FleetRepository.php`
- `src/Fleet/Service/{FleetMovementConfig,CreateFleetCommandService,MoveFleetCommandService,DisbandFleetCommandService,FleetArrivalService}.php`
- `src/Fleet/Command/{CreateFleet,MoveFleet,DisbandFleet}{Command,CommandHandler}.php`
- `src/Fleet/Exception/{FleetNotFound,FleetAlreadyInTransit,EmptyFleet,InvalidFleetComposition,SameOriginAndTarget}Exception.php`
- `migrations/Version20260619000007.php`
- `tests/Ship/ValueObject/ShipTypeSpeedTest.php`
- `tests/Fleet/Service/{FleetMovementConfigTest,FleetArrivalServiceTest}.php`
- `tests/Fleet/Command/{CreateFleet,MoveFleet,DisbandFleet}CommandTest.php`

**Geändert:**
- `src/Ship/ValueObject/ShipType.php` (+ getSpeed)
- `src/Ship/Model/Ship.php` (fleet ManyToOne)
- `config/packages/doctrine.yaml` (fleet_id type)
- `config/services.yaml` (FleetArrivalService public)
- `tests/Ship/Command/CargoTransferTest.php` (Magic-Dock-Cleanup)

**Gelöscht (T-015 Magic-Dock-Cleanup):**
- `src/Ship/Command/DockTransportShipCommand.php`
- `src/Ship/Command/DockTransportShipCommandHandler.php`
- `src/Ship/Service/DockTransportShipCommandService.php`
