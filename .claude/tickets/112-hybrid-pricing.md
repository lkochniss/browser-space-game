# T-112 Need-Based-Pricing + Statische Handelsposten

**Type:** Feature
**Epic:** Trade & Economy
**Domain:** Trade
**Blocked By:** T-111, T-073, T-007, T-023
**Status:** Draft
**Effort:** XL
**Depends on:** T-111 (Auction-House), T-073 (Merchant-Guild-Faction), T-007 (SolarSystem), T-023 (Raumstation)
**Blocks:** T-114 (Roving-Trader), T-118 (Region-Arbitrage)

## Beschreibung

**Vollständiges Replacement** des ursprünglichen "Magic-Floor/Ceiling-Backstop"-Designs.

NPC-Wirtschaft besteht aus 2 Trader-Layern:

1. **Statische Handelsposten** (dieses Ticket):
   - Auf bestimmten Planeten (NPC-controlled, in System-Init seeded)
   - Auf bestimmten Raumstationen (NPC-controlled, fix in Galaxy)
   - Persistente Inventare (groß: Stationen ~100k Units, Planeten ~500k Units)
   - Verkaufen ihr Inventar + kaufen Resources der Spieler
   - **NICHT übernehmbar** — strukturell NPC-only, kein Battle-Target
   - Need-Based-Pricing: Preis hängt vom aktuellen Inventory-Stand ab
   - Inventory-Cap: voll = kauft nicht mehr, leer = verkauft nicht mehr

2. **Roving-Trader-Schiffe** → eigenes Ticket T-114 (Spawn/Despawn-Cycle, kleinere Inventare).

3. **Geographische Preisarbitrage** → eigenes Ticket T-118 (Region-Need-Profile).

Merchant-Guild-Faction (T-073) ist Identifier — Reputation zur Guild beeinflusst Pricing-Margen, ersetzt aber nicht die Markt-Mechanik.

## Acceptance Criteria

### Trading-Post-Foundation
- [ ] `TradingPost` Entity (id, location: Planet|Station, ownerFactionId = MerchantGuild, inventory: Map<ResourceType, int>, capacityPerResource: int, totalCapacity: int)
- [ ] `StaticTradingPostSeedService`: bei Galaxy-Init seeded fixe Posten (z.B. 1 pro 10 Systems random + 5 große Hub-Stationen)
- [ ] `TradingPostInventory` separat als embedded oder eigene Tabelle (Performance: viele Reads)
- [ ] Posten **nicht im Battle-Target-Pool** (Type-Validation in T-103/T-074/T-075 refused)
- [ ] Posten überleben jedes Galaxy-Event (T-076)

### Need-Based-Pricing
- [ ] `NeedBasedPricingService::computeBuyPrice(post, resourceType): int` (Trader→Player, Player kauft)
  - `basePrice × (1 + (1 - inventoryRatio) × demandFactor)`
  - inventoryRatio = currentInventory / capacityPerResource (clamped 0..1)
  - demandFactor: konfigurierbar pro ResourceType (z.B. 1.5 = max +150% wenn leer)
- [ ] `NeedBasedPricingService::computeSellPrice(post, resourceType): int` (Player→Trader, Player verkauft)
  - `basePrice × (0.4 + inventoryRatio × supplyFactor)`
  - inventoryRatio leer = 1.0× basePrice (Trader zahlt mehr für was er braucht), voll = 0.4× (Trader hat genug)
- [ ] BasePrice-Tabelle pro ResourceType (gleiches Schema wie alte Floor/Ceiling, aber Werte sind Mittel-Anker)
- [ ] Margen-Modifier durch Merchant-Guild-Reputation (T-073): ALLIED → -5% Buy, +5% Sell für Player

### Inventar-Mechanik
- [ ] Player-BUY (Trader→Player) reduziert post-Inventory; Trader hat 0 → keine BUY-Option
- [ ] Player-SELL (Player→Trader) erhöht post-Inventory; bei capacity-erreicht → keine SELL-Option mehr
- [ ] Inventory-Regeneration: pro Tick (oder cron) gleicht post langsam Richtung default-Profile (z.B. 1% pro Stunde)
- [ ] Default-Profile pro Posten-Type (z.B. Industrie-Posten startet hoch in Iron-Bar/Steel, niedrig in Refined-Goods) — finalisiert in T-118

### Trade-Execution
- [ ] `TradeWithPostCommand` (playerId, postId, resourceType, qty, direction: BUY|SELL)
- [ ] Player muss persönlich (Schiff) am Posten sein — kein Remote-Trade
- [ ] Cargo-Transfer via Transportschiff-Cargo (T-015) mit Post-Inventory
- [ ] Trade-Steuer (T-111): 10% gilt auch hier; Diplomat-Track-Modifier (T-098) zieht
- [ ] Trade-Audit-Log: jeder Post-Trade persistiert (für T-096 Stats + Achievements)

### UI / API
- [ ] PostBrowserController: Spieler sieht alle entdeckten Posten (T-087 Fog-of-War-respektiert)
- [ ] Pro Posten: Inventory + aktuelle Buy/Sell-Preise
- [ ] Search/Filter: Resource-Type, "wo billigst Iron-Ore kaufbar / teuerst verkaufbar"

## Affected Tests

- tests/Trading/Service/NeedBasedPricingTest.php (Inventory-Ratio-Curves, basePrice-Modifier)
- tests/Trading/Service/TradingPostInventoryCapTest.php (full = no SELL, empty = no BUY)
- tests/Trading/Service/InventoryRegenerationTest.php (cron-Regeneration)
- tests/Trading/Service/StaticPostBattleImmunityTest.php (Battle-Target refused)
- tests/Trading/Service/MerchantGuildReputationModifierTest.php
- tests/Trading/Service/TradeExecutionTest.php (Cargo-Transfer end-to-end)

## Fixtures Needed

Yes — Trading-Posts seeded auf Test-Galaxy, Default-Profile pro Posten-Type, Test-Player mit Cargo-Schiff

## Open Questions

1. **Capacity-Werte**: Stationen 100k pro Resource? Planeten 500k? Hub-Hauptposten 1M? — Tuning-Question, kann iterativ.
2. **Inventory-Regeneration-Rate**: 1% / Stunde realistisch? Oder pro Tag? Beeinflusst Spieler-Wartezeit nach Buy/Sell-Wave.
3. **Posten-Spawn-Density**: 1 pro 10 Systems + 5 Hub-Stationen — passt das zur Galaxy-Größe (T-007 5-System-Init)? Vermutlich Galaxy-Skalierung später.
4. **Inventory-Profile**: Default-Profil pro Posten-Type (Industrie/Luxus/Border) gehört eigentlich in T-118. Hier: nur uniform-Profile als Fallback.

## Notes

- **Strukturelle Entscheidung**: kein magic-unlimited-NPC mehr. Alle NPC-Trader sind Inventory-bound → Player-Verkauf kann Markt sättigen, Player-Kauf kann ausverkaufen.
- **Anti-Whale**: einzelner Whale kann Posten leerkaufen, aber das bedeutet er hat den Posten temporär "geclear" — andere Spieler haben Inventory-Visibility und sehen Lücke. Faire Mechanik.
- **Persistent World**: Posten-Inventories überleben Sessions, Galaxy-Events.
- **Geo-Arbitrage** (T-118): unterschiedliche Posten haben unterschiedliche Default-Profile → Spieler kann Iron-Bar in Industrie-Region billig kaufen, in Border-Region teuer verkaufen. Reise-Risiko (T-074) + Treibstoff-Kosten (T-105) bilden Gegengewicht.
- **Roving-Trader** (T-114): ergänzt Static-Posts, kleinere Inventories, befristete Anwesenheit, "magisch" gespawnt.
- Player-zu-Player-Markt (T-111 Auction) bleibt parallel — die zwei Systeme konkurrieren im selben Spiel (Galaxy-weite Auction vs lokale NPC-Posten).
