# POIs (Points of Interest)

## STI-Foundation (T-019)

Single-Table-Inheritance auf Tabelle `pois`. Discriminator-Column `type`.

| Discriminator | Class | Ticket | Zweck |
|---------------|-------|--------|-------|
| `asteroid_field` | `AsteroidField` | T-020 ✓ | Endliche Erz-Vorkommen, Salvage-Target |
| `debris_field` | `DebrisField` | T-021 ✓ | Trümmer-Aggregat, Salvage-Target → Recycling |
| `nebula` | `Nebula` | T-022 ✓ | Stealth (concealmentLevel 1-10) |
| `wormhole` | `Wormhole` | T-085 ✓ | Bidirektionales Pair (twin), tech-locked |
| `station` | `SpaceStation` | T-023 ✓ | Pop + Storage + Cargo, max 1/System |
| `unknown_fleet` | (Stub→T-074) | offen | NPC-Pirate-Fleets |
| `black_hole` | (Stub→T-086) | offen | — |

## SalvageableField-Interface (T-021)

Gemeinsame Extract-API für `AsteroidField` + `DebrisField`:

```
getSolarSystem(): SolarSystem
getAmount(ResourceType): int
getContents(): array<string,int>
setAmount(ResourceType, int): void
extract(ResourceType, int): int   // returns taken (≤ amount, ≤ available)
getTotalAmount(): int
isEmpty(): bool
```

`SalvageProcessor` + `StartSalvageCommandService` checken `instanceof SalvageableField`,
nicht mehr nur `AsteroidField`.

## AsteroidField (T-020)

- Map `asteroid_contents: array<ResourceType-value, int>` als JSON
- Nur **FINITE-Resources** (Erze) erlaubt
- Galaxy-Init: 0-2 pro System, 1-3 zufällige Erze, 500-2000 Amount

## DebrisField (T-021)

- Map `debris_contents: array<DEBRIS_*-value, int>` als JSON
- Nur **DEBRIS-ResourceCategory** erlaubt (DEBRIS_LOW/MEDIUM/HIGH)
- Spawn-Quellen: ShipSupplyProcessor.killShip (Mini-2-LOW), WorldFixture (Sol-Gamma 8/4/1),
  Demo-Galaxy-Garantie. T-103 Battle-Spawn folgt.

## Nebula (T-022)

- `concealmentLevel: 1-10` Stat. Effekt-Hooks später (T-018 Detection, T-103 Battle-Modifier)
- Galaxy-Init: 30%-Chance pro System, Concealment 3-9

## Wormhole (T-085)

- `twin: ?Wormhole` (OneToOne self-reference) — bidirektional via `pairWith()` idempotent gepairt
- `requiredTechSlug: ?string` (T-026 FTL-Tier-2 Lock)
- Galaxy-Init: 1 Pair/Galaxy, Demo-Garantie pairt Heimat-System ↔ Asteroid-System

## SpaceStation (T-023)

- `status: ACTIVE | ABANDONED` (T-023b later)
- Owner-Player + eigene Pop + Storage + Cargo
- Max 1/System

### Build-Path soft-deprecated (T-174, Lost-Tech-Lore)

`BuildSpaceStationCommand` wirft nur noch `StationConstructionDeprecatedException`.
Stations sind nicht baubar — die Tech ist im Universum verschollen. Quellen:

- **Galaxy-Bootstrap-Spawn** (T-175, Draft): pirate-owned + ABANDONED Spawns
- **Claim-ABANDONED** (T-023b, Draft): Player nimmt ABANDONED über
- **Combat-Capture** (T-176, Draft): Player kämpft Pirate-owned ab

Command/Handler/Service-Klassen bleiben als Stub bestehen bis T-175 deployt ist;
danach kann der ganze Build-Path hart entfernt werden.

## Files

- `src/POI/Model/Poi.php` (STI-Base, DiscriminatorMap)
- `src/POI/Model/SalvageableField.php` (Interface, T-021)
- `src/POI/Model/{AsteroidField,DebrisField,Nebula,Wormhole,SpaceStation}.php`
- `src/POI/Repository/PoiRepository.php` (`findBySolarSystem`, `findAll`)
- `src/POI/ValueObject/{PoiId,PoiType,StationStatus}.php`

## Cross-Domain

- **SolarSystem**: Poi.solarSystem ManyToOne (FK), `SolarSystem.addPoi`
- **Ship/Salvage**: SalvageableField-Targets (Asteroid + Debris)
- **Discovery (T-018/T-087)**: aktuell zeigt Galaxy-Overview alle POIs in entdeckten Systemen — POI-Discovery kommt mit T-087 separat

## Geplant

- **T-074/T-075** UnknownFleet-POI als NPC-Pirate-Encounter-Container
- **T-086** BlackHole-POI mit eigenen Mechaniken
- **T-021 Battle-Spawn** wartet auf T-103 BattleResolver
