# Fleets

## Status-Machine

`FleetStatus::DOCKED` ↔ `IN_TRANSIT`:

```
DOCKED ──MoveFleet──▶ IN_TRANSIT ──FleetArrival(when arrivedAt ≤ now)──▶ DOCKED (target)
DOCKED ──Disband────▶ (deleted, ships docked at originPlanet)
```

## CommandFlow (T-017)

| Command | Service | Effekt |
|---------|---------|--------|
| `CreateFleetCommand(playerId, shipIds)` | `CreateFleetCommandService` | Fleet aggregiert ≥1 Ships am selben Planet (alle DOCKED, isReady), Status=DOCKED, originPlanet gesetzt |
| `MoveFleetCommand(fleetId, targetPlanetId)` | `MoveFleetCommandService` | Status→IN_TRANSIT; `arrivedAt = now + travelDuration` (slowest-ship-speed via `FleetMovementConfig`); Ships werden vom origin entkoppelt |
| `DisbandFleetCommand(fleetId)` | `DisbandFleetCommandService` | Fleet=DOCKED, sonst reject. Schiffe bleiben am originPlanet zurück, Fleet entfernt |

## FleetArrivalService (Tick-Loop)

Globaler Service (kein TickProcessor). Demo-CLI + T-044 Scheduler rufen
`resolveArrivedFleets()` auf:

- Sucht alle Fleets mit `IN_TRANSIT && arrivedAt <= clock.now()`
- Pro Fleet: setzt Status=DOCKED, dockt alle Ships an `targetPlanet`, originPlanet = targetPlanet
- Returns Anzahl resolved

## Travel-Duration

`FleetMovementConfig::computeDuration(originPlanet, targetPlanet, ships)`:
- Slowest-Ship-Speed = MIN über alle Ship-Type-Speeds (T-017)
- Distance: aktuell konstant per Inter-System-Hop (Foundation, T-160 macht echte Galaxy-Map)
- Folge-Modifier (T-017b Draft): Nebel-Detection, Wormhole-Travel-Reduktion, Treibstoff

## Files

- `src/Fleet/Model/Fleet.php` (Entity, ManyToMany Ships via Ship.fleet_id, originPlanet, arrivedAt, status)
- `src/Fleet/ValueObject/{FleetId,FleetStatus}.php`
- `src/Fleet/Command/{CreateFleet,MoveFleet,DisbandFleet}Command.php` + Handler
- `src/Fleet/Service/FleetMovementConfig.php` (Speed pro ShipType, Distance-Stub)
- `src/Fleet/Service/FleetArrivalService.php` (Tick-Resolver, public, vom Demo-CLI/T-044 gerufen)

## Cross-Domain

- **Ship**: 1:N zu Fleet via `Ship.fleet_id`. Ship.isReady-Gate gilt für Create
- **Planet**: originPlanet (FK), Move-Target ist PlanetId
- **POI/SpaceStation**: optional Fleet-Origin (T-023b später)

## Geplant

- **T-017b** Movement-Modifier (Nebel/Wormhole/Treibstoff)
- **T-103** Battle-Resolution-Engine konsumiert Fleets als Side-Aggregat
- **T-044** Tick-Scheduler ersetzt Demo-CLI als Caller von `resolveArrivedFleets()`
