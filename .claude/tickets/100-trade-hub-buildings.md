# T-100 Trade-Hub-Buildings (Marketplace / Spaceport / Customs-House)

**Type:** Feature
**Status:** Blocked (by T-177 — WAREHOUSE überschneidet sich mit Generic-Storage-Refactor)
**Effort:** M (TBD)
**Depends on:** T-110 (Trade-Routes), T-111 (Auction), T-112 (NPC-Posts), T-177 (Generic-Storage-Refactor)
**Blocks:** —

## Beschreibung

Spieler-eigene Trade-Buildings auf eigenen Planeten. Nicht zu verwechseln mit T-112 NPC-Handelsposten — diese hier sind Player-controlled-Boost-Buildings.

Neue Buildings:
- MARKETPLACE: -2%/lvl Auction-Steuer (T-111) für Trades aus diesem Planeten (Cap -20%)
- SPACEPORT: +Cargo-Capacity für andockende Schiffe + Trade-Volume-Boost
- CUSTOMS_HOUSE: Reduziert Roving-Trader-Pricing-Markup (T-114) bei Trades auf eigenem Planet
- WAREHOUSE: Universal-Storage (für alle REFINED-Goods, kein Resource-Spezifisch — kompakter als Single-Storage)
- BAZAAR: zieht mehr Roving-Trader an (T-114 Spawn-Bonus für eigenen Planet)

## Acceptance Criteria

- [ ] TBD: Neue BuildingType-Werte
- [ ] TBD: Auction-Service (T-111) liest Marketplace-Level beim Trade-Tax-Compute
- [ ] TBD: Roving-Trader-Spawn (T-114) liest Bazaar-Level für Spawn-Bonus
- [ ] TBD: Warehouse-Universal-Storage als Add-on zu Single-Storage (T-061)
- [ ] TBD: Spaceport als Trade-Volume-Multiplier integriert in TradeRoute-Service (T-110)

## Open Questions

- Marketplace-Discount auch auf Player-zu-Player-Trades?
- Spaceport-Trade-Volume-Boost: pro Stunde oder pro Trip?
- Bazaar-Spawn-Bonus-Magnitude: ×2 Trader-Frequenz?

## Notes

- Diplomatie-Track-Synergie (T-098): Diplomat + Trade-Hub-Buildings = wirtschaftlicher Powerhouse
- Wirtschaftliche Identität: Industrie-Spieler haben Refineries, Diplomatie-Spieler haben Trade-Hubs
