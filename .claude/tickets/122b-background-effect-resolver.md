# T-122b Player-Background Effect-Resolver (Folge zu T-122)

**Type:** Feature
**Status:** Draft
**Effort:** M
**Depends on:** T-122 (Foundation, Done), T-073 (Faction-Rep, Done),
T-025c (Research, Done), T-013 (Probe, Done)
**Blocks:** —

## Beschreibung

T-122 etabliert nur `Player.background` + Setter. Die eigentlichen Multiplier-
Effekte hängen an Hooks in verschiedenen Services und sind hier zusammen-
gefasst.

## Multiplier-Tabelle (final)

| Background | Mining | Reputation-Speed | RP-Output | Pop-Growth | Ship-Speed/Crit | Probe-Range | Trade-Income |
|------------|--------|------------------|-----------|------------|-----------------|-------------|--------------|
| `IMPERIAL_NOBILITY` | ×0.98 | ×1.05 | — | — | — | — | — |
| `COMMON_BORN` | ×1.05 | ×0.98 | — | — | — | — | — |
| `TECH_ADEPT` | — | — | ×1.05 | ×0.98 | — | — | — |
| `VETERAN_PILOT` | — | — | — | ×0.98 | ×1.05 | — | — |
| `FRONTIER_BORN` | — | — | — | — | — | ×1.05 | ×0.98 |

Nullable: NULL = alle Multi ×1.0 (kein Effekt).

## Acceptance Criteria

- [ ] `PlayerBackgroundEffectResolver`-Service (zentrale Lookup-API)
- [ ] **Mining-Hook**: `Planet::getEffectiveMiningMultiplier()` stackt mit
      `Player.background.getMiningMultiplier()` (oder Service-Lookup falls
      Player-Reference in Planet aufwendig)
- [ ] **Reputation-Hook**: `ReputationService::changeReputation()` multipliziert
      mit Background-Speed
- [ ] **RP-Hook**: `ResearchDurationConfig::durationSeconds()` reduziert
      Duration zusätzlich mit Background-RP-Multiplier (oder als Eff.-Lab-Bonus)
- [ ] **Pop-Growth-Hook**: `Planet::getEffectivePopGrowthMultiplier()` stackt
- [ ] **Ship-Speed-Hook**: `Ship::getEffectiveSpeed()` stackt mit Background
      (T-026c bereits dort, +Multiplier ergänzen)
- [ ] **Probe-Range-Hook**: T-013 ProbeRange (sobald implementiert)
- [ ] **Trade-Income-Hook**: T-110/T-111 (Draft) — Auction-Listing-Tax oder Sale-Price
- [ ] Tests pro Hook
- [ ] Doc `player.md` Background-Sektion mit Multi-Tabelle + Stack-Reihenfolge

## Open Questions

### Q1: Stack-Reihenfolge mit Specialist-Track (T-098)?

T-098 ist Mechanik (±30%), T-122 ist Flavor (±5%/-2%). Beide kombinierbar:
- (a) Additiv (Cap +35% / -12%)
- (b) Multiplikativ (×1.30 × 1.05 = ×1.365)

### Q2: Background-Change durch Allianz-Verlassen?

(Aus T-117 Diskussion) — Wenn Player Allianz verlässt, behält Background ja
(kein Allianz-Effekt). Stricht.

### Q3: NULL-Background-Default explicit?

- (a) NULL = ×1.0 alle (silent default)
- (b) Force-Choice bei Onboarding (T-046) — kein NULL-Zustand in Game-Flow

## Out of Scope

- T-098 Specialist-Tracks-Implementation
- T-117 Allianz-Forschung
- T-122c Cosmetic-Anteil (Welcome-Message, Banner-Default)

## Notes

- T-122 ist Foundation; T-122b implementiert Effekte wenn die Services
  ausgebaut sind (Reputation/Probe/Trade noch nicht fertig wired)
- Konsistent mit T-098 Pattern: zentraler Effect-Resolver-Service
