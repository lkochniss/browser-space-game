# T-178: Ship-Cargo-Universal (alle Schiffe haben Cargo)

**Type:** Feature (Refactor)
**Epic:** Storage Vision
**Domain:** Ship
**Blocked By:** T-177, T-180
**Status:** Done
**Effort:** M (~4-5h)
**Depends on:** T-177 (Generic-Storage-Refactor), T-180 (Size-Multiplier-Config)
**Blocks:** T-066 (Fuel-Storage auf Ship), T-105 (Schiff-Maintenance — Fuel-Consumption)

## Beschreibung

Storage-Vision-Pivot (T-177) erweitert sich auf Schiffe: **Jedes Schiff
hat eigenen Cargo-Volumen-Storage**, auch Non-Transporter-Klassen — für
eigene Versorgung (Fuel, Pop-Survival, Notreserven). Transport-Klassen
haben weiterhin den größten Cargo, aber sind nicht mehr exklusiv.

Aktuell (T-015 Done): nur `TransportShipClass` hat `CargoManifest`. Andere
Ships haben keinen Cargo-Slot. Neuer Stand: alle Ships haben Cargo,
volumengebunden.

## Resolved Decisions

### Q1: Cargo-Größe pro Ship-Klasse → **(a) Per-ShipType + ShipClass konstant**

ShipType + Combat-ShipClass haben jeweils eigene `cargoVolume`-Konstante.
Combat-Mk-Tier multipliziert Base mit Mk-Multi.

| ShipType / Class | Cargo (m³) |
|------------------|------------|
| GENERIC | 50 |
| COLONY_SHIP | 300 |
| TRANSPORT_SMALL | 100 |
| TRANSPORT_MEDIUM | 500 |
| TRANSPORT_LARGE | 2000 |
| SALVAGE | 500 |
| PROBE (T-013) | 10 |
| Frigate Mk I | 50 |
| Destroyer Mk I | 80 |
| Cruiser Mk I | 120 |
| Battleship Mk I | 200 |
| Carrier Mk I | 150 |

Mk II = Base × 1.5, Mk III = Base × 2.25 (analog Stats-Scaling).

### Q2: Cargo-Item-Scope → **(d) Komplett offen**

Ship-Cargo akzeptiert alle Items wie Transport-Klassen — Resources,
Pop, Fuel, Munition (T-088), Loot. Volume regelt sich über
`ResourceVolumeConfig` (T-180). Keine Sonderlogik für Non-Transporter —
nur kleineres Volumen-Cap unterscheidet Combat vs. Transport.

### Q3: T-015 CargoManifest Migration → **(a) Ersetzen durch ShipCargo**

`CargoManifest` Embeddable wird gelöscht. Generic `ShipCargo` Embeddable
analog T-177 `StorageInventory`-Pattern wird eingeführt — alle Ships
(Transport + Combat + Spezial) nutzen `ShipCargo`. T-015 Tests
(`LoadCargo` / `UnloadCargo`) ziehen mit. Code-Cleanup im selben Commit.

### Q4: Load/Unload-API → **(a) Generisch für alle Ships**

`LoadCargoCommand` / `UnloadCargoCommand` akzeptieren beliebige Ship-IDs.
Volume-Check intern via `Ship::cargoVolumeCapacity` + `ShipCargo::usedVolume()`.
Keine separaten Convenience-Commands — bei Bedarf später als eigenes Ticket.
T-088 Munition-Loading nutzt gleichen Code-Pfad.

### Q5: Default-Cargo-Content bei Bau → **(a) Leer**

`BuildShipCommand` setzt Ship-Cargo auf leer. Player muss explizit
Resources/Fuel/Pop via `LoadCargoCommand` laden bevor Schiff fliegt.
T-066 (Fuel) und T-105 (Pop-Survival) liefern Auto-Refuel/Provisionierungs-
Hooks später nach — out-of-scope für T-178.

## Acceptance Criteria

### Entity + Embeddable

- [x] `ShipCargo` Embeddable (generic Item-Storage analog T-177 `StorageInventory`):
      - `addItem(ResourceType, qty): void` mit Volume-Check
      - `removeItem(ResourceType, qty): void`
      - `getQuantity(ResourceType): int`
      - `usedVolume(): int` (via `ResourceVolumeConfig`)
      - `canAddItem(ResourceType, qty, capacity): bool`
      - `maxAddableQuantity(ResourceType, capacity): int`
- [x] `Ship::cargo: ShipCargo` Embeddable an allen Ship-Entities (Transport +
      Combat + Spezial)
- [x] `Ship::cargoVolumeCapacity: int` — kommt aus Config (siehe Q1-Tabelle),
      Combat-Klassen via Mk-Multi (Mk II ×1.5, Mk III ×2.25)
- [x] `Ship::cargo` initial leer bei Build

### Config

- [x] `ShipCargoVolumeConfig` (oder Erweiterung `ShipCostConfig`) liefert
      `cargoVolume(ShipType, ?ShipClass, ?MkTier): int` per Q1-Tabelle
- [x] Combat-Klassen-Lookup: `ShipBlueprintRegistry` liefert Mk-Tier-Multi

### Migration (T-015 Refactor)

- [x] `CargoManifest` Embeddable + Field aus `Ship` gelöscht
- [x] Alle Referenzen (`$ship->getCargo()`, `LoadCargoCommandService`,
      `UnloadCargoCommandService`, Tests) auf `ShipCargo` umgestellt
- [x] Doctrine-Migration: `cargo_*`-Columns umbenennen / ersetzen (alte
      `CargoManifest`-Columns → neue `ShipCargo`-Columns + neue
      `cargo_volume_capacity`-Column)
- [x] T-015 IT-Tests (`LoadCargoTest`, `UnloadCargoTest`) grün nach Refactor

### Load/Unload-API generalisiert

- [x] `LoadCargoCommand`/`UnloadCargoCommand` akzeptieren jede Ship-ID
      (kein Transport-Filter)
- [x] Volume-Cap-Check via `Ship::cargoVolumeCapacity` + `ShipCargo::canAddItem()`
- [x] `ShipCargoOverflowException` wenn Volume-Cap überschritten

### Tests

- [x] `ShipCargoVolumeTest` (UT): Volume-Berechnung pro ShipType/ShipClass/Mk
- [x] `ShipCargoEmbeddableTest` (UT): addItem/removeItem/canAddItem/maxAddable
- [x] `LoadCargoOnCombatShipTest` (IT): Combat-Ship lädt Resource — Volume-Check ok
- [x] `LoadCargoOverflowTest` (IT): Overflow wirft `ShipCargoOverflowException`
- [x] `LoadCargoOnAllShipTypesTest` (IT): jede Ship-Klasse kann laden
- [x] T-015 Tests bleiben grün (Regression)

### Fixtures

- [x] `ShipCargoFixture`: 1 Combat-Ship (leer), 1 Transport-Ship (leer), 1
      mit Pre-Load (Resource im Cargo)

### Docs

- [x] `ships.md` Cargo-Sektion komplett überarbeitet (alte CargoManifest-Sektion
      ersetzen; neue Volume-Tabelle aus Q1; ShipCargo-Embeddable beschreiben)
- [x] `resources.md` Hinweis: ResourceVolumeConfig nun auch für Ship-Cargo
- [x] `dependencies.md` Eintrag T-178
- [x] `decisions.md` Eintrag T-178 (Q1-Q5 Resolution)

## Out of Scope

- Fuel-Verbrauch-Logic im Flug (T-066 / T-105)
- Pop-Mortality bei Crew-Mangel (T-105 Folge)
- Auto-Refuel / Auto-Provisioning bei Build (T-066 / T-105 Hook)
- Trade-Routes (T-110) Auto-Cargo-Management
- Convenience-Commands `RefuelShipCommand` etc. (eigenes Folge-Ticket bei Bedarf)

## Notes

- T-015 wird durch T-178 **refactored** (CargoManifest → ShipCargo). T-015
  bleibt als Done-Marker, T-178 erweitert Cargo-Scope auf alle Ship-Klassen.
- T-088 Munition-Cargo nutzt `ShipCargo` direkt — kein Sonder-Storage.
- T-015c (Pop-Transfer Ship↔Station, Draft) profitiert automatisch
  (Pop via T-180 als Cargo-Item mit Volume-Multi 10).

### Refinement Tokens (estimate)
- Input: ~8k
- Output: ~3k

### Implementation Tokens (estimate)
- Input: ~110k
- Output: ~20k

### Implementation Summary

**New Code:**
- `src/Ship/ValueObject/ShipCargo.php` (Embeddable, volume-based)
- `src/Ship/Service/ShipCargoVolumeConfig.php` (Q1-Tabelle als Service)
- `src/Ship/Exception/ShipCargoOverflowException.php`
- `migrations/Version20260624000006.php` (`cargo_capacity` → `cargo_volume_capacity`)

**Refactored:**
- `Ship`-Entity: `CargoManifest` → `ShipCargo` Embeddable, `cargoCapacity` → `cargoVolumeCapacity`
- `Ship::canAddResource/canAddPop/maxAddableResource/maxAddablePop` (Volume-aware API analog T-177)
- `LoadCargoCommandService` / `UnloadCargoCommandService`: Transport-Filter entfernt
- `BuildShipCommandService`: `ShipCargoVolumeConfig` ersetzt `ShipCostConfig::getCargoCapacity`
- `SalvageProcessor`: Volume-aware Cargo-Cap via `maxAddableResource`
- `CreateTradeRouteCommandService` + `TradeRouteProcessor`: m³-Check statt Units
- Demo-CLI + Snapshotter: m³-Anzeige

**Tests:** 748 grün (+15 neue T-178 Tests). Neue UT: `ShipCargoTest`,
`ShipCargoVolumeConfigTest`. Neue IT: `ShipCargoUniversalTest`.

**Out-of-Scope Note:** `SpaceStation` (POI-Domain) nutzt weiter `CargoManifest`
(units-based). Refactor folgt in T-183 Station-Generic-Storage. T-178 ändert
nur Ship-Side. Konsequenz: `CargoCapacityExceededException` bleibt für
Station-Pfad, `ShipCargoOverflowException` ist neu für Ship-Pfad.
