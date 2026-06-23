# T-114 Roving-Trader-Schiffe (Spawn / Despawn-Cycle)

**Type:** Feature
**Epic:** Trade & Economy
**Domain:** Ship
**Blocked By:** T-112, T-073, T-007
**Status:** Draft
**Effort:** L
**Depends on:** T-112 (Need-Based-Pricing), T-073 (Faction), T-007 (SolarSystem)
**Blocks:** —

## Beschreibung

NPC-Trader-Schiffe als zweiter Trader-Layer (zusätzlich zu Static-Posts in T-112).

**Spawn-Mechanik**:
- Tauchen "magisch" im System auf — KEIN echtes Movement durch Galaxy
- Bleiben für 6h–72h (random) im System, danach despawnen
- Pro System pro Tag X% Chance auf Trader-Spawn (Tuning-Wert)
- Kommen mit zufälligem Inventar (kleinere Cargo-Bandbreite als Stationen)
- Verkaufen + kaufen analog T-112-Pricing

**Charakteristika**:
- Inventar 1k–10k Units pro Schiff (nach Schiffs-Klasse)
- Pricing per T-112 Need-Based-Service (selbe Engine, andere Inventar-Größe)
- Nicht angreifbar (sind NPC-Magic, kein Battle-Target — analog Static-Posts)
- Unterschiedliche Schiffs-Profile: Frigate-Trader (klein, schnell-Despawn), Hauler (groß, lange Anwesenheit)

## Acceptance Criteria

### Trader-Schiff-Entity
- [ ] `RovingTrader` Entity (id, location: SolarSystemId, factionId = MerchantGuild oder andere, profile: enum FRIGATE_TRADER|HAULER|LUXURY_BARGE, inventory: Map<ResourceType, int>, spawnedAt: DateTimeImmutable, despawnsAt: DateTimeImmutable)
- [ ] TraderProfile-Enum mit Cargo-Capacity-Bandbreiten (FRIGATE_TRADER 1k–3k, HAULER 5k–10k, LUXURY_BARGE 2k–5k spezialisiert auf Refined/Tier-3)
- [ ] Inventar bei Spawn: zufälliger Mix aus Profile-relevanten Resources

### Spawn-/Despawn-Service
- [ ] `RovingTraderSpawnService` (Cron, alle 1h):
  - Iteriert Galaxy-Systems
  - Pro System: X% Spawn-Chance (z.B. 5%/h)
  - Wählt Profile basierend auf System-Region (T-118 wenn ready, sonst uniform)
  - Spawnt mit despawnsAt = now + random(6h..72h)
- [ ] `RovingTraderDespawnService` (Cron): entfernt expired Trader, persistiert Final-State NICHT (Schiff weg = Inventar weg)
- [ ] Spawn-Cap pro System: max 3 gleichzeitige Trader (verhindert Overcrowding)
- [ ] Spawn-Logik schlägt erst zu nach Bubble-Phase (T-150) — Bubble-Player sieht keine Trader

### Trade-Mechanik
- [ ] `TradeWithRovingTraderCommand` (analog `TradeWithPostCommand` aus T-112)
- [ ] Pricing identisch zu T-112 (Need-Based + Reputation-Modifier)
- [ ] Cargo-Transfer via Player-Schiff (T-015) — Player muss am System sein
- [ ] Bei Despawn mit ausstehendem Trade: Trade fails (Notification)

### Discovery / UI
- [ ] Trader sichtbar nur in SCANNED-System (T-087 Fog-of-War)
- [ ] Galaxy-Map (T-160) zeigt aktive Trader mit Verbleibe-Zeit
- [ ] Notification (T-161): "Hauler im Sektor X, lädt Adamantium" für entscheidungsrelevant Resources
- [ ] Notification-Filter via T-165 Settings (z.B. nur Tier-3-Trader-Spawn-Notifications)

## Affected Tests

- tests/Trading/Service/RovingTraderSpawnTest.php (Cron-Trigger, Spawn-Cap)
- tests/Trading/Service/RovingTraderDespawnTest.php (expired removal)
- tests/Trading/Service/TraderProfileInventoryTest.php (Bandbreiten + Profile-Match)
- tests/Trading/Service/RovingTraderBubblePauseTest.php (T-150 respect)
- tests/Trading/Service/RovingTraderBattleImmunityTest.php

## Fixtures Needed

Yes — Galaxy + Test-Spawns für Cron-Tests

## Open Questions

1. **Spawn-Frequenz**: 5%/h pro System ergibt im Schnitt ~1 Trader alle 20h pro System. Genug oder zu wenig? Abhängig von Galaxy-Größe. Tuning iterativ.
2. **Profile-Verteilung**: Frigate-Trader 60%, Hauler 30%, Luxury-Barge 10% als Default? Oder regional unterschiedlich (Border-Region mehr Hauler weil weniger Static-Posts da)?
3. **Trader-Reputation-Effekt**: alle Roving-Trader = MerchantGuild-faction? Oder sollten manche Pirate-Trader (Black-Market-Vorgeschmack zu T-113) sein? Decision needed.
4. **Sichtbarkeit über Posten hinweg**: Spieler kann mehrere Trader gleichzeitig in einem System haben. UI-Liste mit Pricing-Vergleich relevant?

## Notes

- **Magic-Spawn-Decision** (User-Direktive): kein echtes Movement durch Galaxy. Reduziert Performance-Komplexität (keine Trader-Pathfinding-Logik) und ist erzählerisch ok ("Sie sind durch's Warp-Netz gekommen").
- **Geo-Arbitrage-Mechanik** (T-118): Roving-Trader haben profile-fitting Inventories — wenn Region "Industrie" ist, kommen mehr Hauler mit Erzen; wenn "Luxus", mehr Luxury-Barges. T-118 finalisiert das.
- **Risiko-Komponente** (User-Direktive): Spieler-Schiff fliegt zu fernem Trader = Pirat-Encounter-Risk (T-074) + Treibstoff (T-105). Roving-Trader selbst sind nicht angreifbar — Schiff-Verlust nur unterwegs.
- **Persistent vs. Transient**: Static-Posts (T-112) = Persistent State, Roving-Trader = Transient (Spawn/Despawn ohne Persistence post-despawn).
