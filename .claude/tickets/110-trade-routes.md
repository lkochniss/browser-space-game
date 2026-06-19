# T-110 Trade-Routes (Auto-Transport eigene Planeten)

**Type:** Feature
**Status:** Draft
**Effort:** L
**Depends on:** T-015 (Transportschiff), T-014 (Kolonisationsschiff)
**Blocks:** T-095 (Auto-Routing), T-111 (Auction)

## Beschreibung
Player setzt fixe Trade-Route zwischen 2 eigenen Planeten. Schiff bound an Route, transportiert Resources automatisch.

Priorität 1 in Cluster B: erst eigene-Planet-Logistik, danach Auction-House.

## Acceptance Criteria
- [ ] TradeRoute-Entity (id, ownerPlayerId, sourcePlanet, targetPlanet, resourceType, qtyPerTrip, boundShipId, status, lastTripAt)
- [ ] Schiff-Bind: Schiff ist während Route-Aktivität nicht für andere Aufgaben verfügbar
- [ ] Trip-Cycle: Source → Target (laden/transport/entladen) → Idle bis next Trip
- [ ] Trip-Time = Hin + Rück Movement-Time (T-017 Flotte)
- [ ] Treibstoff-Verbrauch (T-105) wird auf Source-Planet gebucht
- [ ] Pause/Resume per Route
- [ ] Route-Cancel: Schiff freigegeben
- [ ] Max Routes pro Player: 5 (erweiterbar via Forschung Logistics-Branch)

## Affected Tests
- tests/Trade/Service/TradeRouteCycleTest.php (full trip sequence)
- tests/Trade/Service/TradeRouteShipBindingTest.php

## Fixtures Needed
Yes — Multi-Planet-Player + Schiff-Pool

## Notes
- Foundation für T-095 Auto-Routing (Threshold-Trigger erweitert dies)
- Keine Trade-Routes zwischen Spielern — das ist T-111 (Auction-House)
