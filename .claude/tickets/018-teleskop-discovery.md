# T-018: Teleskop + System-Erkundung

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** No
**Depends on:** T-007 (SolarSystem), T-019 (POI-Foundation)
**Blocks:** T-087 Fog-of-War (eigene Persistence-Layer für Discovery-State)

## Description

Teleskop = Building auf Planet. Reichweite-basiert: entdeckt Sterne (zeigt
Sonnensysteme an) + im eigenen System Planeten + POIs. Meta-Ebene Erkundung.
Detail-Erkundung weiterhin per Sonde (T-013) / Schiff (T-017).

**Discovery-Subjects nach Stand T-019-T-023:**
- AsteroidField (T-020) — Mining-relevant
- Nebula (T-022) — Stealth-Effekt-Quelle (zukünftig)
- Wormhole (T-085) — Travel-Shortcut-Targets
- SpaceStation (T-023) — Player-Kontroll-Punkt
- DebrisField, UnknownFleet, BlackHole — sobald T-021 / T-074-T-075 / T-086 done

## AC

- [ ] `BuildingType::TELESCOPE` BuildingType + Cost + Duration
- [ ] Reichweite-Formel pro Level (z.B. `range = level * 1` System-Hops)
- [ ] `TelescopeDiscoveryProcessor` (TickProcessor) entdeckt unbekannte Systeme
  im Radius
- [ ] Discovered-State persistiert pro Player auf SolarSystem-Ebene → Foundation
  hier oder separat als T-087-Vorgriff
- [ ] Im eigenen System: Teleskop deckt POIs nach Difficulty + Scanner-Tech auf
- [ ] Scope-Decision: Foundation hier vs. T-087 Fog-of-War — wer hält
  PlayerSystemDiscovery-Entity?

## Out of Scope (Folge-Tickets)

- **POI-Difficulty-System** für Teleskop-vs-Sonde-Aufdeckung → T-027 Planetologie-
  Forschung
- **Tech-Boni für Teleskop-Reichweite** → T-127 Mining oder T-026 Antrieb-Branch
- **Galaxy-Map-UI** → T-160

## Affected

- `src/Building/ValueObject/BuildingType.php` (+ TELESCOPE)
- `src/Building/Service/{BuildingCostConfig,BuildingDurationConfig}.php`
- Neu: `src/Tick/Processor/TelescopeDiscoveryProcessor.php`
- Neu: `src/Discovery/Model/PlayerSystemDiscovery.php` (oder als Teil T-087)
- `src/Planet/Model/Planet.php` (`getTelescopeLevel` Helper analog Shipyard/ProbeLab)

## Open Questions

1. **PlayerSystemDiscovery-Owner**: T-018 oder T-087? Wenn T-018 minimal Foundation
   reicht → Single-Boolean-Flag (entdeckt ja/nein), T-087 erweitert um Tier-Levels
2. **Range-Formel**: 1 Hop pro Level, oder LightYear-Distance bei T-160 Galaxy-Map
3. **Tick-Frequenz**: jeder Tick voll scannen oder probabilistic?
