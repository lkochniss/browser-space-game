# T-015: Transportschiff + Cargo-Transfer

**Type:** Feature
**Status:** Done
**FX:** No
**MIG:** Yes (`Version20260619000006` — ships.cargo_resources/cargo_pop_count/cargo_capacity)
**Depends on:** T-012

## Description

Transport-Foundation. 3 Klassen mit unterschiedlicher Cost+Capacity. Cargo-Manifest VO als Embedded-VO (JSON-Spalte für Resource-Map + integer für Pop-Slot). Inter-Planet-Movement via Magic-Dock-Stub (Schiff wird instant am Ziel angedockt — T-017 ersetzt das durch echtes Wallclock-Travel).

## AC

- [x] `ShipType::TRANSPORT_SMALL`, `TRANSPORT_MEDIUM`, `TRANSPORT_LARGE` + `ShipType::isTransport(): bool`
- [x] `CargoManifest` Embeddable VO: Map<ResourceType, int> (json) + popCount (int) + load/unload-API
- [x] Ship-Entity erweitert: embedded `cargo` + `cargoCapacity` Spalte + `loadResourceCargo`/`unloadResourceCargo`/`loadPopCargo`/`unloadPopCargo` Methoden mit Hard-Reject bei Capacity-Überschreitung
- [x] `ShipCostConfig` erweitert um Transport-Klassen + `getCargoCapacity()`-Methode:
  - TRANSPORT_SMALL: 150 IRON_BAR, 15 Pop, 30min, capacity 1000
  - TRANSPORT_MEDIUM: 400 IRON_BAR + 50 ALUMINUM_ORE, 30 Pop, 60min, capacity 5000
  - TRANSPORT_LARGE: 1000 IRON_BAR + 200 ALUMINUM_ORE + 50 TITANIUM_ORE, 100 Pop, 120min, capacity 20000
- [x] `BuildShipCommandService` setzt `cargoCapacity` beim Build via Cost-Config
- [x] Migration `Version20260619000006` für ships.cargo_*-Spalten
- [x] `LoadCargoCommand` + Handler + Service (Hard-Reject bei Capacity/Insufficient/non-Transport/not-docked)
- [x] `UnloadCargoCommand` + Handler + Service (Hard-Reject bei InsufficientCargo)
- [x] `DockTransportShipCommand` + Handler + Service (Magic-Dock zwischen Planeten)
- [x] 6 Domain-Exceptions (ShipNotFound, NotATransportShip, ShipNotDocked, ShipNotReady, CargoCapacityExceeded, InsufficientCargo)
- [x] Tests: 7 Unit (CargoManifest), 5 Unit (ShipCostConfig erweitert), 11 IT (CargoTransfer)
- [x] Suite grün (306/306, 668 assertions)

## Geklärte Fragen

1. **Cargo-Modell:** Map<ResourceType, int> + separater Pop-Slot. Capacity ist gemeinsam (`sumResources + popCount <= cargoCapacity`).
2. **Capacity-Verhalten:** Hard-Reject bei Überschreitung — predictable, einfach testbar.
3. **Movement:** Magic-Dock-Stub via separaten `DockTransportShipCommand`. Schiff wird instant am Ziel angedockt. T-017 ersetzt das durch echtes Wallclock-Movement.

## Out of Scope (Folge-Tickets)

- **Echtes Movement / Reise-Zeit** → T-017 Flotte-Movement
- **Treibstoff-Verbrauch beim Transport** → T-066 (Treibstoff) + T-105 (Maintenance)
- **Auto-Transfer-Routen** (Recurring) → T-110 Trade-Routes
- **Pirat-Encounter beim Transport** → T-074 Pirate-Encounter-Spawn
- **Combat-Vulnerability-Stat** → T-103 Battle-Resolution-Engine
- **Pop-W/F/O-Verbrauch im Cargo** → T-105 Crew-Versorgung erweitert das

## Files

**Neu:**
- `src/Ship/ValueObject/CargoManifest.php` (Embeddable VO)
- `src/Ship/Command/{LoadCargoCommand,LoadCargoCommandHandler}.php`
- `src/Ship/Command/{UnloadCargoCommand,UnloadCargoCommandHandler}.php`
- `src/Ship/Command/{DockTransportShipCommand,DockTransportShipCommandHandler}.php`
- `src/Ship/Service/{LoadCargoCommandService,UnloadCargoCommandService,DockTransportShipCommandService}.php`
- `src/Ship/Exception/{ShipNotFoundException,NotATransportShipException,ShipNotDockedException,ShipNotReadyException,CargoCapacityExceededException,InsufficientCargoException}.php`
- `migrations/Version20260619000006.php`
- `tests/Ship/ValueObject/CargoManifestTest.php`
- `tests/Ship/Command/CargoTransferTest.php`

**Geändert:**
- `src/Ship/ValueObject/ShipType.php` (3 Transport-Cases + `isTransport()`)
- `src/Ship/Service/ShipCostConfig.php` (3 Transport-Configs + `getCargoCapacity()`)
- `src/Ship/Service/BuildShipCommandService.php` (cargoCapacity beim Build)
- `src/Ship/Model/Ship.php` (CargoManifest embedded + cargoCapacity + Cargo-Methoden)
- `tests/Ship/Service/ShipCostConfigTest.php` (4 neue Test-Methoden)
