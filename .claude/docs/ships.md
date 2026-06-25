# Ships

## ShipTypes

| Type | Purpose | Pop-Cost | Cargo (m³) | Salvage-Rate |
|------|---------|----------|------------|--------------|
| `GENERIC` | Foundation-Stub (T-012) | 20 | 50 | — |
| `COLONY_SHIP` | Kolonisation (T-014) | 50 | 300 | — |
| `TRANSPORT_SMALL` | Cargo (T-015) | 15 | 100 | — |
| `TRANSPORT_MEDIUM` | Cargo (T-015) | 30 | 500 | — |
| `TRANSPORT_LARGE` | Cargo (T-015) | 100 | 2000 | — |
| `SALVAGE` | Bergung (T-016) | 25 | 500 | 50/min |

`ShipType::isTransport()` / `isSalvage()` für Type-Klassifizierung. Cost +
Bauzeit via `ShipCostConfig`; Cargo-Volume (T-178) via `ShipCargoVolumeConfig`.

## Bauprozess (T-012)

`BuildShipCommand(planetId, shipType, propulsion=HYDROGEN)` → `BuildShipCommandService`:
- Voraussetzung: `Planet::hasShipyard($now)` (T-011)
- T-026c: Propulsion-Research-Gate — Player muss
  `propulsion->getRequiredResearchSlug()` auf Lvl 1+ haben (HYDROGEN ohne Gate)
- Resource + Pop-Cost via `ShipCostConfig::getResourceCost/getPopulationCost`
- `finishedAt = now + duration`; Ship docked auf Planet
- Schiff isReady analog Building (T-062)

## Combat-Ship-Klassen (T-102)

5 Familien × 3 Mark-Tiers = 15 Combat-Klassen. Parallel zu `ShipType` (das die
Spezial-Schiffe abbildet) — Combat-Schiffe haben `ShipType::GENERIC` und
`Ship.shipClass != null`. Stats kommen aus `ShipBlueprintRegistry`.

### Base-Stats (Mk I)

| Familie | HP | Damage | Schild | Pop | Build (h) | Cost (Top-Items) |
|---------|----|--------|--------|-----|-----------|------------------|
| Frigate | 1000 | 200 | 300 | 30 | 6 | 500 Steel + 200 IB |
| Destroyer | 2500 | 400 | 800 | 60 | 12 | 1500 Steel + 500 IB + 50 Chip |
| Cruiser | 5000 | 800 | 1500 | 120 | 36 | 4000 Steel + 1500 IB + 200 Chip + 50 Composite |
| Battleship | 12000 | 1500 | 3000 | 250 | 72 | 10000 Steel + 3000 IB + 500 Chip + 200 Composite + 50 Hull-Plate |
| Carrier | 8000 | 1800 | 1800 | 180 | 60 | 7000 Steel + 2500 IB + 400 Chip + 150 Composite + 30 Hull-Plate |

Mk II = Mk I × 1.5 Stats × 3× Cost. Mk III = Mk II × 1.5 × 3 → Mk III ≈ 2.25×
Stats / 9× Cost vs. Mk I.

### Build-Gates

| Gate | Anforderung |
|------|-------------|
| Shipyard-Level | Frigate ≥ 1 / Destroyer ≥ 3 / Cruiser ≥ 5 / Battleship ≥ 8 / Carrier ≥ 10 |
| Mark-Research | Mk II/III braucht `<family>_mk<tier>` Research-Node Lvl 1 |
| Captain (T-104a) | Alle Combat-Klassen brauchen IDLE-Captain auf Player |
| Resources + Pop | Wie Blueprint-Cost |

`MissingShipyardLevelException` / `ShipClassResearchNotMetException` /
`MissingCaptainException` werfen bei Verletzung.

### Escape-Pod-Survival-Chance (Q3)

Für T-104a Captain-Permadeath. Bei Schiff-Verlust roll'd Battle-Resolver
diesen Wert.

| Familie | Pod-Chance |
|---------|------------|
| Frigate | 30% |
| Destroyer | 50% |
| Cruiser | 65% |
| Battleship | 80% |
| Carrier | 70% |

`Ship::getEscapePodSurvivalChance()` liefert ShipClass-Wert wenn gesetzt,
sonst ShipType-Stub (0 für alle existing non-combat).

### Carrier-Squadrons (Q4)

Stats-only. Carrier hat höheren Damage-Stat als balanced peers (1800 vs.
Battleship 1500). Keine separate Fighter-Entity, kein Squadron-Management.

### Research-Branch (Q5)

10 Mark-Tier-Nodes (5 Familien × {mk2, mk3}) registriert in `ResearchTree`:
- `frigate_mk2` … `carrier_mk2` (Lab-Lvl 2, prereq `shipbuilding` 1)
- `frigate_mk3` … `carrier_mk3` (Lab-Lvl 3, prereq `<family>_mk2` 1)

T-128 Schiffbau-Branch wird zusätzliche Bonus-Nodes (Cost-Mult, Speed-Boost)
draufpacken.

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

## Cargo (T-178 — Universal Volume-Cargo)

**Seit T-178**: alle Ships haben Cargo, Capacity ist m³-basiert.
`ShipCargo` Embeddable (Replacement von `CargoManifest` für Ship-Side):

- `Ship.cargo: ShipCargo` Embeddable (json `cargo_resources` + `cargo_pop_count`)
- `Ship.cargoVolumeCapacity: int` (m³, gesetzt beim Build via `ShipCargoVolumeConfig`)
- `Ship.getCargoVolumeUsed/Free` — live aus `cargo.usedVolume()` (Sum × `ResourceVolumeConfig`-Multi + Pop × 10 m³)
- `Ship.canAddResource(type, qty)` / `canAddPop(qty)` / `maxAddableResource(type, qty)`
- Validation-Exception: `ShipCargoOverflowException`

**Cargo-Volume-Tabelle** (`ShipCargoVolumeConfig`, m³):

| ShipType / Class | Cargo (m³) | Quelle |
|------------------|------------|--------|
| GENERIC | 50 | T-178 |
| COLONY_SHIP | 300 | T-178 |
| TRANSPORT_SMALL | 100 | T-178 |
| TRANSPORT_MEDIUM | 500 | T-178 |
| TRANSPORT_LARGE | 2000 | T-178 |
| SALVAGE | 500 | T-178 |
| PROBE | 0 | (Probe-Domain) |
| Frigate Mk I | 50 | T-178 |
| Destroyer Mk I | 80 | T-178 |
| Cruiser Mk I | 120 | T-178 |
| Battleship Mk I | 200 | T-178 |
| Carrier Mk I | 150 | T-178 |

Mk II = Base × 1.5, Mk III = Base × 2.25 (analog T-102 Stats-Scaling).

**Load/Unload-API generisch (T-178):**
- `LoadCargoCommand` / `UnloadCargoCommand` akzeptieren **jede** Ship-ID
  (kein Transport-Filter mehr — Combat-Schiffe können Salvage/Munition tragen)
- Volume-Cap-Check via `Ship::canAddResource(type, qty)` bzw. `canAddPop(qty)`

**T-015b Station-Cargo (Foundation):**
- Ship hat `station: ?SpaceStation`-Field (XOR mit `planet`); via `setStation()` umgeschaltet
- `LoadCargo` / `UnloadCargo` branchen je nach Dock-Target
- Station-Storage nutzt weiterhin `CargoManifest` (units-based) — Refactor in T-183
- Owner-Restriction: nicht enforced auf Foundation; T-093 Allianz-Stationen ergänzt das

**T-015c Station-Pop-Transfer:**
- LoadCargo: zieht `popCount` aus `station.populationOnStation`, lädt ins Ship-Cargo
- UnloadCargo: pusht Ship-Cargo-Pop nach `station.populationOnStation`
- Cap-Check für Station-Pop-Max: defer (T-023b Station-Maintenance liefert das)
- Insufficient-Pop-Check bei Load (Station-Pop muss reichen)

**Initial-Cargo bei Build (T-178):**
- `BuildShipCommand` setzt Ship-Cargo immer leer
- Auto-Refuel (T-066) / Auto-Provisioning (T-105) sind Folge-Tickets

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
| `CargoCapacityExceededException` | Station-Storage Load > free units (T-015b/Station-Pfad) |
| `ShipCargoOverflowException` | Ship-Volume-Cap überschritten (T-178) |
| `ShipNotFoundException` / `ShipNotReadyException` / `ShipNotDockedException` | Ship-State |
| `PropulsionResearchNotMetException` | Build mit Antriebs-Typ, dessen Required-Research dem Player fehlt (T-026c) |
| `MissingShipyardLevelException` | Combat-Schiff-Build unter dem nötigen Shipyard-Level (T-102) |
| `ShipClassResearchNotMetException` | Combat-Mk-II/III-Build ohne `<family>_mk<n>` Research (T-102) |
| `MissingCaptainException` | Combat-Build ohne IDLE-Captain (T-102 × T-104a) |
| `ShipBlueprintNotFoundException` | ShipBlueprintRegistry hat keinen Eintrag für die ShipClass |

## Files

- `src/Ship/Model/Ship.php` (Entity, finishedAt, supplies, cargo, salvage-state, propulsion, shipClass)
- `src/Ship/ValueObject/{ShipId,ShipType,ShipCargo,CargoManifest,PropulsionType,ShipClass,ShipBlueprint}.php`
- `src/Ship/Service/ShipCostConfig.php` (Cost/Duration je Type — non-combat)
- `src/Ship/Service/ShipCargoVolumeConfig.php` (T-178 m³-Cap per ShipType + ShipClass+Mk)
- `src/Ship/Service/ShipBlueprintRegistry.php` (T-102 Combat-Class Stats)
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
