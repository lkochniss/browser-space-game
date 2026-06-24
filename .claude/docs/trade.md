# Trade

T-110 Foundation Auto-Transport zwischen eigenen Planeten via TradeRoutes.
Cross-Player-Trade (Auction-House) = T-111, kein Scope hier.

## TradeRoute-Entity

`App\Trade\Model\TradeRoute` — bindet ein Schiff dauerhaft an eine Loop
zwischen zwei eigenen Planeten.

| Feld | Wirkung |
|------|---------|
| `owner: Player` | Player (intra-player) |
| `sourcePlanet`, `targetPlanet: Planet` | Endpunkte |
| `boundShip: Ship` | gebundenes Schiff (1:1) |
| `outboundResource`, `outboundQty` | Hinflug-Cargo |
| `returnResource?`, `returnQty?` | Rückflug-Cargo (NULL = One-Way Fixed) |
| `status: TradeRouteStatus` | ACTIVE / SINGLE_TRIP / PAUSED / CANCELLED |
| `currentLeg: TradeRouteLeg` | AT_SOURCE / GOING_TO_TARGET / AT_TARGET / GOING_TO_SOURCE |
| `lastTripAt`, `tripCounter` | History |

## Trip-Modi

| Modus | Verhalten |
|-------|-----------|
| `ACTIVE` (Fixed-Route) | source→target→source-Loop bis Cancel. Return optional |
| `SINGLE_TRIP` | One-Way source→target. Ship bleibt am Target. Route auto-cancels nach Outbound-Delivery |
| `PAUSED` | Processor skipped. Ship parkt an aktuellem Planeten oder bleibt in Transit (kein Halt mitten im Flug) |
| `CANCELLED` | Terminal. Ship aus Fleet entfernt + Solo-Fleet gelöscht |

## State-Machine (TradeRouteProcessor)

`TradeRouteProcessor` globaler Tick-Service (analog FleetArrivalService).
Pro Tick + Route: Loop bis State stabil (max 5 Iterations/Route).

```
AT_SOURCE        → load outbound + MoveFleet → GOING_TO_TARGET
GOING_TO_TARGET  → wait for Fleet.DOCKED at target
AT_TARGET        → unload outbound + recordTrip
                   Single-Trip → CANCEL
                   has return → load return + Move → GOING_TO_SOURCE
                   no return → Move empty → GOING_TO_SOURCE
GOING_TO_SOURCE  → wait for Fleet.DOCKED at source
                   unload return (if any) → AT_SOURCE → loop
```

Loop-Mode: Wenn `AT_TARGET` → unload → load return → Move, dann ist
`GOING_TO_SOURCE` der neue Stand. Im selben Tick kann der Processor diese
Transitions chainen wenn alle Bedingungen erfüllt sind.

Graceful stops (kein State-Change, kein Throw):

- Source-Resource leer → Route bleibt in AT_SOURCE
- Target-Volume voll → Outbound partial unload, Route bleibt in AT_TARGET
- Return-Resource am Target leer → Route bleibt in AT_TARGET

## Fleet-Binding

Beim Route-Create wird das Schiff in eine **dedizierte Solo-Fleet** gewrappt
(direkt im Service ohne `CreateFleetCommand`-Validation-Stack). Cancel-Route
detached das Ship aus dieser Fleet — wenn die Fleet danach leer ist, wird
sie removed.

Ship-Lock-Validation:

- `Ship.getFleet() === null` (kein Manual-Move parallel)
- `TradeRouteRepository::findByShip(ship) === null` (keine 2. Route)

## Commands

| Command | Effekt |
|---------|--------|
| `CreateFixedRouteCommand` | Erzeugt ACTIVE-Route + Solo-Fleet |
| `CreateSingleTripCommand` | Erzeugt SINGLE_TRIP-Route + Solo-Fleet |
| `PauseRouteCommand` | status → PAUSED |
| `ResumeRouteCommand` | PAUSED → ACTIVE |
| `CancelRouteCommand` | terminal; detached Ship + cleanup Fleet |

## Validation (Create)

- Source ≠ Target
- Source.player == playerId && Target.player == playerId
- Ship.player == playerId
- Ship.getFleet() === null (kein Double-Binding)
- Ship docked an Source
- Ship.cargoCapacity ≥ outboundQty

Exceptions: `InvalidTradeRouteException`, `ShipAlreadyBoundException`,
`TradeRouteNotFoundException`.

## Out of Scope (Folge-Tickets)

| Ticket | Scope |
|--------|-------|
| T-110b | Munition/Supplies Refill am Source-Planet (für Pirat-Threat-Zonen) |
| T-095 | Auto-Production-Routing (Threshold-getriggerte Routes) |
| T-111 | Auction-House Cross-Player-Trade |
| T-105 | Fuel-Verbrauch pro Route |

## Files

- `src/Trade/Model/TradeRoute.php`
- `src/Trade/ValueObject/{TradeRouteId,TradeRouteStatus,TradeRouteLeg}.php`
- `src/Trade/Service/{CreateTradeRouteCommandService,TradeRouteProcessor}.php`
- `src/Trade/Command/{Create*,Pause,Resume,Cancel}*.php`
- `src/Trade/Repository/TradeRouteRepository.php`
- `src/Trade/Exception/*.php`
- Migration `Version20260624000005`

## Cross-Domain

| Domain | Wirkung |
|--------|---------|
| Ship (T-012) | Ship.fleet wird durch Solo-Fleet gesetzt; Lock-Check |
| Fleet (T-017) | TradeRouteProcessor dispatcht `MoveFleetCommand`; FleetArrivalService materialisiert Ankünfte |
| Planet (T-177) | Volume-Cap clampt Unload-Quantity via `maxAddableQuantity` |
| Resource (T-002) | Cargo-Loading/-Unloading via `Ship::loadResourceCargo` |
