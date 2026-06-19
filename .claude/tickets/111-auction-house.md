# T-111 Auction-House (Galaxy-weit + Lieferzeit via Transportschiff)

**Type:** Feature
**Status:** Draft
**Effort:** XL
**Depends on:** T-110 (Trade-Routes), T-073 (Faction für Trade-Steuer)
**Blocks:** T-112, T-113

## Beschreibung
Galaxy-weiter Marktplatz für Resources. Buy/Sell-Orders. Transport via Transportschiff erforderlich (Lieferzeit ist Teil der Strategie).

## Acceptance Criteria
- [ ] AuctionOrder-Entity (id, playerId, type: BUY/SELL, resourceType, qty, pricePerUnit, sourcePlanet (für SELL) / targetPlanet (für BUY), status, expiresAt)
- [ ] Order-Matching-Engine: bei Match → erstellt Trade + bindet Transportschiff (von Source nach Target)
- [ ] Trade-Steuer (Merchant-Guild): 10% des Trade-Werts → fließt aus dem System
- [ ] Diplomat-Track-Modifier (T-098): -5% Steuer
- [ ] Order-Expiry: Default 7 Tage, dann auto-cancel + Resource-Refund
- [ ] Spieler ohne Transportschiff kann nicht selbst transportieren → Order erfordert Schiff-Reserve oder Allianz-Hilfe
- [ ] UI (sobald Web-Layer): Auction-Browser mit Filter (Resource, Price, Region)
- [ ] Search/Filter API: Resource-Type, Price-Range, Source-Region

## Affected Tests
- tests/Auction/Service/OrderMatchingTest.php (BUY/SELL match)
- tests/Auction/Service/TradeTaxTest.php
- tests/Auction/Service/OrderExpiryTest.php
- tests/Auction/Service/TransportShipBindingTest.php

## Fixtures Needed
Yes — Multi-Player mit Orders, Transportschiffen

## Notes
- Lieferzeit = strategischer Faktor: nahe Trades schnell, Galaxy-weit langsam
- Hostile-Faction-Outposts (T-075) können Trade-Routes überfallen — Gefahr beim Transport
