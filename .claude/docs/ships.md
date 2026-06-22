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

`BuildShipCommand(planetId, shipType, propulsion=HYDROGEN)` → `BuildShipCommandService`:
- Voraussetzung: `Planet::hasShipyard($now)` (T-011)
- T-026c: Propulsion-Research-Gate — Player muss
  `propulsion->getRequiredResearchSlug()` auf Lvl 1+ haben (HYDROGEN ohne Gate)
- Resource + Pop-Cost via `ShipCostConfig::getResourceCost/getPopulationCost`
- `finishedAt = now + duration`; Ship docked auf Planet
- Schiff isReady analog Building (T-062)

## Propulsion (T-026c)

Jedes Ship hat einen `PropulsionType`. Default = HYDROGEN (Foundation-Antrieb,
keine Forschung nötig). Andere Antriebe erfordern die entsprechende T-026
Forschung beim Build:

| PropulsionType | Speed-Multi (×ShipType.getSpeed) | Max-System-Range | Required Research |
|----------------|----------------------------------|------------------|-------------------|
| `HYDROGEN`     | 1.0× | 0 (kein FTL) | — (Foundation) |
| `ION`          | 1.3× | 0 | `propulsion_ion` |
| `FUSION`       | 1.7× | 0 | `propulsion_fusion` |
| `ANTIMATTER`   | 2.2× | 0 | `propulsion_antimatter` |
| `HYPERDRIVE`   | 1.5× | 1 (FTL) | `ftl_hyperdrive` |
| `WARP`         | 2.0× | 3 (FTL) | `ftl_warp` |
| `JUMPDRIVE`    | 2.5× | 10 (FTL) | `ftl_jumpdrive` |

`Ship::getEffectiveSpeed()` = `type.getSpeed() × propulsion.getSpeedMultiplier()`.
`Fleet::getMinSpeed()` nutzt das (T-017 langsamstes-Schiff-Pattern).

`PropulsionType::getMaxSystemRange()` ist heute **informativ** — Inter-System-
Travel-Gate läuft weiter über die Player-Forschung `ftl_hyperdrive` Lvl 1+ in
`MoveFleetCommandService`. Per-Move-Range-Enforcement (Schiff kann nur X
Systeme weit jumpen) folgt mit T-026d.

**Out-of-Scope:** Fuel-Verbrauch (T-066), Refit auf bestehenden Schiffen.

## Life-Support (T-012)

`ShipSupplyProcessor` (Tick): jedes ready Schiff verbraucht 1 W/F/O pro Tick:
- Docked → drain Planet-Storage; Fallback Schiff-Eigen-Storage
- Bei Mangel beider → `killShip()`: Pop-Slots verloren + Schiff entfernt
- T-021: killShip spawnt **kleines DebrisField** (2 DEBRIS_LOW) im Heim-System

## Cargo (T-015 + T-015b)

`CargoManifest` als Embeddable auf Ship: `ResourceType → amount` Map + Pop-Slots.
- `LoadCargoCommand` zieht aus **Planet-Storage** ODER **Station-Storage** in Ship-Cargo
- `UnloadCargoCommand` umgekehrt
- Capacity via `ShipType::getCargoCapacity`; Validation in `CargoCapacityExceededException`

**T-015b Station-Cargo (Foundation):**
- Ship hat `station: ?SpaceStation`-Field (XOR mit `planet`); via `setStation()` umgeschaltet
- `LoadCargo` / `UnloadCargo` branchen je nach Dock-Target
- Owner-Restriction: nicht enforced auf Foundation; T-093 Allianz-Stationen ergänzt das

**T-015c Station-Pop-Transfer:**
- LoadCargo: zieht `popCount` aus `station.populationOnStation`, lädt ins Ship-Cargo
- UnloadCargo: pusht Ship-Cargo-Pop nach `station.populationOnStation`
- Cap-Check für Station-Pop-Max: defer (T-023b Station-Maintenance liefert das)
- Insufficient-Pop-Check bei Load (Station-Pop muss reichen)

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
| `PropulsionResearchNotMetException` | Build mit Antriebs-Typ, dessen Required-Research dem Player fehlt (T-026c) |

## Files

- `src/Ship/Model/Ship.php` (Entity, finishedAt, supplies, cargo, salvage-state, propulsion)
- `src/Ship/ValueObject/{ShipId,ShipType,CargoManifest,PropulsionType}.php`
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
