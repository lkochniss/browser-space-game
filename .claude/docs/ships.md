# Ships

## ShipTypes

| Type | Purpose | Pop-Cost | Cargo | Salvage-Rate |
|------|---------|----------|-------|--------------|
| `GENERIC` | Foundation-Stub (T-012) | 20 | 0 | — |
| `COLONY_SHIP` | Kolonisation (T-014) | 100 | 0 | — |
| `TRANSPORT_SMALL` | Cargo (T-015) | 10 | 100 | — |
| `TRANSPORT_MEDIUM` | Cargo (T-015) | 25 | 500 | — |
| `TRANSPORT_LARGE` | Cargo (T-015) | 50 | 2000 | — |
| `SALVAGE` | Bergung (T-016) | 30 | 500 | 50/min |

`ShipType::isTransport()` / `isSalvage()` für Type-Klassifizierung. Cost +
Bauzeit +Cargo via `ShipCostConfig`.

## Bauprozess (T-012)

`BuildShipCommand(planetId, shipType)` → `BuildShipCommandService`:
- Voraussetzung: `Planet::hasShipyard($now)` (T-011)
- Resource + Pop-Cost via `ShipCostConfig::getResourceCost/getPopulationCost`
- `finishedAt = now + duration`; Ship docked auf Planet
- Schiff isReady analog Building (T-062)

## Life-Support (T-012)

`ShipSupplyProcessor` (Tick): jedes ready Schiff verbraucht 1 W/F/O pro Tick:
- Docked → drain Planet-Storage; Fallback Schiff-Eigen-Storage
- Bei Mangel beider → `killShip()`: Pop-Slots verloren + Schiff entfernt
- T-021: killShip spawnt **kleines DebrisField** (2 DEBRIS_LOW) im Heim-System

## Cargo (T-015)

`CargoManifest` als Embeddable auf Ship: `ResourceType → amount` Map + Pop-Slots.
- `LoadCargoCommand` zieht aus Planet-Storage in Ship-Cargo
- `UnloadCargoCommand` umgekehrt
- Capacity via `ShipType::getCargoCapacity`; Validation in `CargoCapacityExceededException`

## Salvage (T-016)

Aktiv-Salvage Polymorph (T-021):
- `StartSalvageCommand(shipId, poiId, resourceType)`: Validation = Salvage-Schiff
  isReady + POI ist `SalvageableField` (AsteroidField **oder** DebrisField) +
  Ship in selbem System
- `SalvageProcessor` (global, kein TickProcessor): pro Tick `extractable = floor(deltaMinutes × 50)`
  vs. Field-Bestand vs. Cargo-Frei
- Stop-Conditions: Field empty, Cargo voll, oder explizit via `StopSalvageCommand`
- Field-Cleanup bei `isEmpty()` (`em->remove`)

## Kolonisation (T-014)

`ColonizePlanetCommand(shipId, targetPlanetId)`:
- Schiff = COLONY_SHIP, isReady, am selben System wie Target oder docked
- Target-Planet unclaimed
- Pop-Transfer aus Schiff in neuen Planet, Player.claimPlanet, Schiff entfernt

## Domain-Exceptions

| Exception | Trigger |
|-----------|---------|
| `MissingShipyardException` | Kein fertiger SHIPYARD auf Planet |
| `NotASalvageShipException` | Salvage-Action mit non-SALVAGE-Ship |
| `NotATransportShipException` | Cargo-Action mit non-Transport-Ship |
| `InvalidSalvageTargetException` | POI ist kein SalvageableField oder leer |
| `SalvageTargetNotInSystemException` | Ship ist nicht im POI-System |
| `CargoCapacityExceededException` | Load > free units |
| `ShipNotFoundException` / `ShipNotReadyException` / `ShipNotDockedException` | Ship-State |

## Files

- `src/Ship/Model/Ship.php` (Entity, finishedAt, supplies, cargo, salvage-state)
- `src/Ship/ValueObject/{ShipId,ShipType,CargoManifest}.php`
- `src/Ship/Service/ShipCostConfig.php` (Cost/Cargo/Duration je Type)
- `src/Ship/Service/{Build,LoadCargo,UnloadCargo,StartSalvage,StopSalvage}CommandService.php`
- `src/Ship/Service/SalvageProcessor.php` (global, vom Tick-Loop gerufen)
- `src/Tick/Processor/ShipSupplyProcessor.php` (T-012 Life-Support)
- `src/Ship/Repository/{ShipRepository,SalvagingShipRepository}.php`
- `src/Ship/Exception/*.php`

## Cross-Domain

- **Building/SHIPYARD** (T-011): Voraussetzung für Bau
- **POI/SalvageableField** (T-020/T-021): Salvage-Targets
- **Fleet** (T-017): Ship kann zur Fleet gehören (FK `fleet_id`)
- **Player** (T-014 Colonize): Ship erschafft neuen Planet im Player-Aggregat
