# T-110b Trade-Route Refill-Logik (Munition + Supplies)

**Type:** Feature
**Epic:** Trade & Economy
**Domain:** Trade
**Blocked By:** T-110, T-088, T-105
**Status:** Draft
**Effort:** M
**Depends on:** T-110 (Trade-Routes Foundation), T-088 (Munition), T-105 (Schiff-Maintenance/Fuel)
**Blocks:** —

## Beschreibung

Fixed-Routes (T-110) müssen langfristig versorgt werden. Wenn Schiff durch
Pirat-Threat-Zone (T-074) fährt, kann Combat passieren → Munition wird
verbraucht. Ohne Refill: Schiff verliert Combat → Captain-Permadeath.

T-110b ergänzt T-110: am Source-Planet (oder Target, je nach Config) wird
auto-refilled — Munition, Treibstoff, Pop-Supplies.

## Open Questions

### Q1: Refill-Source-Selection
- Nur Source-Planet, oder beide Endpunkte?
- Player-Config pro Route?

### Q2: Refill-Threshold
- Auto-Refill nur wenn Munition < X% (z.B. 30%)?
- Oder volle Top-Off jedes Mal?

### Q3: Refill-Cost
- Kostenlos (Resource wird aus Planet-Storage gezogen)?
- Mit Tax / Maintenance-Fee (Resource-Sink-Mechanik)?

### Q4: Multi-Resource-Refill-Priority
- Wenn Source-Storage knapp: welcher Refill zuerst (Fuel vs Munition vs Pop)?

## Acceptance Criteria (Draft — final nach Q1-Q4)

- [ ] `TradeRoute::refillConfig: RefillConfig` (Embeddable: refill-on-source / refill-on-target / threshold)
- [ ] `TradeRouteProcessor` checkt Refill am Dock-Event, lädt Resources nach
- [ ] Defaults: Refill bei Munition < 30% UND Fuel < 30% UND Pop-Cap < 90%
- [ ] Tests: Refill-Cycle bei verschiedenen Threshold-Setups

## Out of Scope

- Refill aus Allianz-Station (T-093 Folge)
- Refill via Trade-Hub-Purchase (T-112 Folge)
