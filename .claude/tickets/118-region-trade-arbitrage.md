# T-118 Region-Trade-Arbitrage (Geographische Need-Profile)

**Type:** Feature
**Status:** Draft
**Effort:** L
**Depends on:** T-112 (Static-Posts), T-114 (Roving-Trader), T-007 (SolarSystem)
**Blocks:** —

## Beschreibung

Galaxy wird in **Trade-Regionen** unterteilt, jede Region hat ein **Need-Profile** für Resources. Static-Posts (T-112) und Roving-Trader (T-114) in Region X haben Default-Inventory + Pricing-Bias entsprechend Region-Profile.

**Effekt für Spieler**: Iron-Bar in Industrie-Region billig kaufen, in Border-Region teuer verkaufen → Profit.

**Gegengewicht**:
- Längere Reise = mehr Treibstoff (T-105)
- Pirate-Encounter-Risk steigt mit Distance/Region-Threat-Level (T-074)
- Wartezeit (Trade-Routes T-110 oder Player-Schiff-Bewegung)

Region-Definitionen:
- **Core / Industrie-Region**: dichte Mine-Aktivität (NPC-Mines), niedrige Erz-Preise, hohe Refined-Goods-Preise
- **Civilian / Luxus-Region**: hohe Pop-Density, hohe Pop-Goods-Preise (Food, Luxury-Items), niedrige Tech-Resources
- **Border / Threat-Region**: nahe Pirate/Renegade/Xenos-Outposts (T-074/T-075), Premium-Preise für Waffen/Treibstoff/Defense, riskante Reise
- **Frontier / Wild-Region**: kaum Posten, hohe Variabilität, höchste Profite bei höchstem Risiko

## Acceptance Criteria

### Region-Foundation
- [ ] `TradeRegion` Entity (id, name, type: CORE_INDUSTRIAL|CIVILIAN_LUXURY|BORDER_THREAT|FRONTIER_WILD, systems: Set<SolarSystemId>, threatLevel: int)
- [ ] Galaxy-Init: SolarSystems werden Regionen zugewiesen (Algorithmus: zentrale Systems = CORE, Outer = FRONTIER, Systems nahe NPC-Faction-Outposts = BORDER)
- [ ] Region-Boundaries persistent — keine Auto-Reklassifikation nach Init
- [ ] Region-Tag pro System sichtbar in UI (T-160 Galaxy-Map mit Region-Coloring)

### Region-Need-Profile
- [ ] `RegionNeedProfile` (regionType, resourceTypePreferences: Map<ResourceType, NeedLevel>) als Konstante/Konfiguration
- [ ] NeedLevel = enum SURPLUS|NORMAL|SCARCE|CRITICAL
- [ ] Beispiel CORE_INDUSTRIAL: Iron-Ore = SURPLUS, Steel = NORMAL, AI-Core = SCARCE, Food = SCARCE
- [ ] Beispiel BORDER_THREAT: Hull-Plate = CRITICAL, Promethium = CRITICAL, Iron-Bar = NORMAL
- [ ] Profile-Konstanten in Service oder Config — keine DB-Tabelle (Tuning per Code)

### Pricing-Bias-Integration
- [ ] `NeedBasedPricingService` (T-112) erweitert: nutzt Region-Profile als Modifier
- [ ] BuyPrice (Trader→Player) in CRITICAL-Region: ×2.0 Multiplier on top of Inventory-Need
- [ ] BuyPrice in SURPLUS-Region: ×0.5 Multiplier
- [ ] SellPrice (Player→Trader) analog spiegelnd: SURPLUS = Trader zahlt wenig, CRITICAL = Trader zahlt viel
- [ ] Region-Modifier-Stacking mit Inventory-Ratio + Reputation: alle multiplikativ

### Static-Post-Default-Inventory
- [ ] Beim Static-Post-Seed (T-112): Inventory-Default-Ratio richtet sich nach Region-Profile
- [ ] CORE-Industrie-Posten startet voll mit Erzen (SURPLUS), leer in Refined-Goods/Tier-3
- [ ] BORDER-Posten startet voll mit Defense-Goods, leer in Erzen
- [ ] Inventory-Regeneration (T-112) konvergiert zurück zu Region-Default

### Roving-Trader-Spawn-Bias
- [ ] T-114 Spawn-Service liest Region → wählt Profile mit Region-passendem Inventar
- [ ] CORE-Region → mehr Hauler mit Erzen, weniger Luxury-Barge
- [ ] LUXURY-Region → mehr Luxury-Barge, weniger Hauler

### Threat-Region-Encounter-Hook
- [ ] T-074 Pirate-Spawn-Service liest Region.threatLevel → höhere Encounter-Wahrscheinlichkeit in BORDER/FRONTIER
- [ ] Player-Schiff-Movement durch BORDER/FRONTIER (T-017): Pirate-Encounter-Probability +25% pro Region-Threat-Stufe

### UI / Discovery
- [ ] Galaxy-Map (T-160): Regionen visuell unterscheidbar (Color-Coding)
- [ ] Strategy-Forecast (T-163): "Beste Trade-Routen" Widget — listet Top-Arbitrage-Möglichkeiten basierend auf bekannten Posten
- [ ] Region-Type sichtbar im System-Detail-View

## Affected Tests

- tests/Trading/Service/RegionAssignmentTest.php (Galaxy-Init zu Regionen)
- tests/Trading/Service/RegionPricingMultiplierTest.php (CRITICAL/SURPLUS-Wirkung)
- tests/Trading/Service/RegionDefaultInventoryTest.php (Posten-Seed-Profile)
- tests/Trading/Service/RegionThreatEncounterBoostTest.php (Pirate-Spawn-Bias)
- tests/Trading/Service/ArbitrageProfitabilityTest.php (sanity-check: Iron-Ore CORE-zu-BORDER profitabel nach Treibstoff)

## Fixtures Needed

Yes — Test-Galaxy mit allen 4 Region-Types, Posten + Pricing-Sample-Trades

## Open Questions

1. **Region-Anzahl pro Galaxy**: bei 5-System-Init (T-007) noch zu klein. Brauchen Galaxy-Skalierung erst (Folge-Ticket?). Vorschlag: bis Galaxy 50+ Systems hat, ggf. nur 2 Regionen (CORE + BORDER).
2. **Region-Boundaries-Berechnung**: zentral basierend auf Galaxy-Center? Oder via NPC-Faction-Outpost-Distanz? Konkrete Algorithmus-Wahl pending.
3. **Region-Modifier-Magnitude**: ×2.0 / ×0.5 — passend? Tuning iterativ. Zu hoch = Min-Maxing-Pflicht, zu niedrig = Mechanik irrelevant.
4. **CRITICAL ↔ SURPLUS extreme**: kann ein Resource in derselben Region beides sein abhängig vom aktuellen Markt-Stand, oder ist Profile statisch? Decision: Profile statisch, dynamic Inventory-Ratio kommt on-top via T-112.

## Notes

- **Strategische Tiefe** (User-Decision): Geo-Arbitrage = strategischer Spieler-Hebel. Long-Time-Spieler optimieren Trade-Routen, casual nutzt lokale Posten.
- **Risiko-Reward**: hohe Profite gehen mit hohem Schiff-Verlust-Risiko einher (T-074 Pirate, T-105 Treibstoff). Ohne Risk-Komponente wäre Arbitrage zu dominant.
- **Anti-Steamroller**: Region-Profile sind statisch. Whale kann Region nicht "leerkaufen und dauerhaft monopolisieren" — Inventory regeneriert (T-112), Reisedauer + Risiko begrenzen Hub-Hopping.
- **Lieferzeit-Strategie**: passt zur Game-Design-Vorgabe "weite Strecken können sich lohnen". Spieler entscheidet pro Trip ob Risiko/Treibstoff den Profit rechtfertigt.
