# T-110 Trade-Routes (Auto-Transport eigene Planeten)

**Type:** Feature
**Epic:** Trade & Economy
**Domain:** Trade
**Blocked By:** T-015, T-014
**Status:** Ready
**Effort:** L (~6-8h)
**Depends on:** T-015 (Done), T-014 (Done)
**Blocks:** T-095 (Auto-Routing-Folge), T-111 (Auction-House), T-104c (Crew-Role-Diplomat)

## Beschreibung

Zwei Trade-Modi:

1. **Fixed-Route**: Player weist einem Schiff dauerhaft eine Route A ↔ B zu.
   Schiff transportiert Lass auf Hinflug, optional auch auf Rückflug
   (bidirektional konfigurierbar). Auto-Repeat bis Cancel.
2. **Single-Trip**: One-way-Lieferung A → B. Schiff bleibt bei B nach Ablieferung,
   ist danach frei für andere Aktionen.

Refill-Logik (Munition/Supplies während Fixed-Route) → **T-110b Folge-Ticket**
(Out-of-Scope hier).

## Resolved Decisions

- **Q1 Ship-Lock:** Strikt während Fixed-Route. Schiff erscheint nicht in
  Manual-Move/Cargo/Salvage-Pickern, bis Route-Cancel oder Pause. Single-Trip
  lockt das Schiff nur bis Trip-Ende.
- **Q2 Trip-Modes (zwei):**
  - **Fixed-Route:** source→target, optional return-cargo (Config-Flag),
    Auto-Loop bis Cancel. Stops bei Resource-Knappheit (Source leer).
  - **Single-Trip:** source→target, Schiff bleibt bei target docked.
    Kein Auto-Return.
- **Q3 Max-Routes pro Player:** **Unbegrenzt.** Cap regelt sich durch Schiff-Pool
  (jede Route bindet ein Schiff).

## Acceptance Criteria

### Domain

- [ ] `App\Trade\` Domain-Folder (neu): Model/TradeRoute, ValueObject/TradeRouteId,
      Repository/TradeRouteRepository, Service
- [ ] `TradeRoute` Entity:
  - `id: TradeRouteId`
  - `owner: Player`
  - `sourcePlanet: Planet`, `targetPlanet: Planet`
  - `outboundResource: ResourceType`, `outboundQty: int` (laden auf Hinflug)
  - `returnResource: ?ResourceType`, `returnQty: ?int` (NULL = One-Way ohne Return)
  - `boundShip: Ship`
  - `status: TradeRouteStatus` (ACTIVE / PAUSED / CANCELLED / SINGLE_TRIP)
  - `lastTripAt: ?DateTimeImmutable`, `tripCounter: int`
- [ ] `TradeRouteStatus` Enum
- [ ] Migration + ORM-Mapping

### Commands

- [ ] `CreateFixedRouteCommand(playerId, shipId, sourceId, targetId, outboundResource, outboundQty, ?returnResource, ?returnQty)`
- [ ] `CreateSingleTripCommand(playerId, shipId, sourceId, targetId, resource, qty)`
- [ ] `PauseRouteCommand(routeId)` / `ResumeRouteCommand(routeId)`
- [ ] `CancelRouteCommand(routeId)` (löscht Route + gibt Ship frei)

### Trip-Cycle (Tick-Service)

- [ ] `TradeRouteProcessor` (global, vom Tick-Loop gerufen, kein Per-Planet-Tick):
  - Pro ACTIVE Fixed-Route: prüft `boundShip.status` / `boundShip.planet`
  - Wenn Ship docked an `sourcePlanet`: lädt `outboundResource` × `outboundQty`
    (T-015 LoadCargoCommand intern) + dispatcht `MoveFleetCommand` zu `target`
  - Wenn Ship docked an `targetPlanet`: entlädt outboundCargo +
    falls returnResource gesetzt → lädt return + zurück zu source
  - Single-Trip: nach erstem unload → status=CANCELLED (Auto-Cleanup); Ship frei
- [ ] Trip-Time = Movement-Time (T-017 `FleetMovementConfig`)
- [ ] Stops gracefully bei: Source-Resource leer / Target-Volume voll
  (T-177 canAddItem)

### Validation

- [ ] Both Planets gehören Player (T-110 ist intra-player; T-111 deckt
      Cross-Player-Trade ab)
- [ ] Ship existiert + isReady + nicht bereits in andere Route gebunden
- [ ] Ship.cargoCapacity ≥ outboundQty × multi (T-177 Volume)

### Demo CLI

- [ ] Action "Create Fixed Route"
- [ ] Action "Create Single Trip"
- [ ] Action "Pause/Resume/Cancel Route"
- [ ] Status-Display: pro Player aktive Routes mit Trip-Counter, lastTripAt

### Tests

- [ ] `FixedRouteCycleTest` (IT): Schiff source→target→source, repeats
- [ ] `SingleTripTest`: One-way, Ship bleibt am Target
- [ ] `RouteShipLockTest`: Schiff in Route nicht für Manual-Move pickbar
- [ ] `PauseResumeTest`: Pause hält Trip-Loop, Resume reaktiviert
- [ ] `CancelTest`: Ship freigegeben, Route deleted
- [ ] `EmptySourceStopsTest`: Source-Resource leer → Route pausiert/wartet

### Docs

- [ ] `trade.md` (neu) — Trade-Domain-Doku
- [ ] `decisions.md` Eintrag T-110

## Out of Scope (Folge-Tickets)

- **T-110b** Refill-Logik (Munition/Supplies während Fixed-Route — Auto-Top-Up am Source-Planet)
- **T-095** Auto-Production-Routing (Threshold-getriggerte Routes)
- **T-111** Auction-House (Cross-Player-Trade)
- **T-105** Treibstoff-Verbrauch (Routes konsumieren Fuel — Foundation ohne)

## Fixtures Needed

Yes — `TradeRouteFixture` mit Multi-Planet-Player + Schiff-Pool + diverse
Route-Konfigurationen (bidirektional, one-way, paused).

## Notes

- Fixed-Route ist Logistik-Backbone für Industrie-Player
- Single-Trip ist taktisches Tool (z.B. Kolonisations-Schiff Pre-Position)
- Refill-Logic (T-110b) kritisch wenn Routes durch Pirat-Threat-Zonen
  (T-074) führen — Munition-Auto-Refill verhindert Captain-Loss in Combat

### Refinement Tokens (estimate)
- Input: ~8k
- Output: ~3k
