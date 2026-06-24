# T-110 Trade-Routes (Auto-Transport eigene Planeten)

**Type:** Feature
**Epic:** Trade & Economy
**Domain:** Trade
**Blocked By:** T-015, T-014
**Status:** Done
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

- [x] `App\Trade\` Domain-Folder (neu)
- [x] `TradeRoute` Entity mit allen Feldern (owner / source / target /
      boundShip / outboundResource+qty / returnResource?+qty? / status /
      currentLeg / lastTripAt / tripCounter)
- [x] `TradeRouteStatus` Enum (ACTIVE / SINGLE_TRIP / PAUSED / CANCELLED)
- [x] `TradeRouteLeg` Enum (4-State-Machine)
- [x] Migration `Version20260624000005` + ORM-Mapping + Repository

### Commands

- [x] `CreateFixedRouteCommand` + Handler + Service
- [x] `CreateSingleTripCommand` + Handler
- [x] `PauseRouteCommand` + `ResumeRouteCommand` + Handler
- [x] `CancelRouteCommand` (detached Ship + löscht Solo-Fleet)

### Trip-Cycle (Tick-Service)

- [x] `TradeRouteProcessor` globaler Tick-Service (analog FleetArrival)
- [x] State-Machine über `TradeRouteLeg`: AT_SOURCE → GOING_TO_TARGET →
      AT_TARGET → GOING_TO_SOURCE → AT_SOURCE-Loop
- [x] Inner-Loop max 5 Iterations/Route — erlaubt mehrere Transitions
      pro Tick (Arrival → Unload → Move-Next direkt)
- [x] Single-Trip: nach AT_TARGET-Unload → CANCEL
- [x] Trip-Time via FleetMovementConfig (geerbt via MoveFleetCommand)
- [x] Graceful stops bei Source-leer / Target-Volume-voll

### Validation

- [x] Both Planets player-owned + Source ≠ Target
- [x] Ship vorhanden + nicht in anderer Fleet/Route gebunden
- [x] Ship docked an Source-Planet
- [x] Ship.cargoCapacity ≥ outboundQty

### Demo CLI

- [ ] Action "Create Fixed Route" / "Single Trip" / "Pause/Resume/Cancel"
      — _deferred: Foundation-Demo deckt's nicht; Trade ist Phase-2-Player-Feature_
- [ ] Status-Display Route-Liste — _deferred analog_

### Tests

- [x] `TradeRouteTest` (11): Create-Fixed + Single-Trip, Bind-Block,
      Same-Source-Target-Throw, Processor-AT_SOURCE→GOING_TO_TARGET,
      Single-Trip-Completion (incl. Cancel), Fixed-Route-Full-Loop mit
      Return-Cargo, Pause+Resume, Cancel-Releases-Ship, Empty-Source-Graceful

### Docs

- [x] `trade.md` (neu) — Trade-Domain-Doku
- [x] `decisions.md` Eintrag T-110
- [x] `README.md` (docs) Eintrag trade.md

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

### Implementation Tokens (estimate)
- Input: ~220k
- Output: ~24k

### Deferred / Follow-Ups

- Demo-CLI Trade-Actions (Create / Pause / Resume / Cancel + Status-Display)
- `TradeRouteFixture` für T-110-Folge-Tests (heute Tests bauen inline)
- Manual-Move/Cargo-Picker-Integration: filter `ship.getFleet() !== null` —
  heute kein explizites Picker-Service. Wenn Player das aushebelt
  würde, ergänzt T-110-Folge eine `ship.tradeRoute`-Read-API in den
  Picker-Validators.
- T-110b Refill-Logic (Munition/Supplies am Source-Planet, T-088-Hook)
