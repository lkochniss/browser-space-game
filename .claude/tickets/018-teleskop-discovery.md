# T-018: Teleskop + System-Erkundung

**Type:** Feature
**Epic:** Exploration & Probes
**Domain:** Probe
**Blocked By:** T-007, T-019
**Status:** Done
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

## Decisions (2026-06-19)

1. **Discovery-State-Owner:** T-018 Foundation. Single-Boolean-Marker via
   `PlayerSystemDiscovery`-Entity (Player + SolarSystem + discoveredAt).
   T-087 Fog-of-War erweitert später um Tier-Levels + POI-Discovery.
2. **Reveal-Mechanik:** pro Tick werden N=Total-Telescope-Level zufällige
   unbekannte Systems entdeckt (Fisher-Yates über Randomizer).
3. **Initial-Discovery:** ClaimStartPlanet markiert Heimat-System sofort
   als entdeckt; Rest unbekannt.
4. **POI-Discovery im eigenen System:** Out of Scope — kommt mit T-087 / T-027.

## AC

- [x] `BuildingType::TELESCOPE` + Cost (150 IRON / 200 SI / 100 CU / 10 pop) + Duration (45min)
- [x] `Planet::getTelescopeLevel($now)` Helper
- [x] `PlayerSystemDiscovery`-Entity (own ID + UNIQUE(player, system) + discoveredAt)
- [x] `PlayerSystemDiscoveryRepository` mit `findByPlayer` + `isDiscovered`
- [x] `TelescopeDiscoveryService` (global, nicht TickProcessor) mit:
  - `markDiscovered(player, system)` — idempotent
  - `runTickForPlayer(player)` — N random unseen reveals
- [x] `ClaimStartPlanetCommandService` markiert Heimat-System bei Claim
- [x] Demo-CLI Tick-Forward callt `telescopeDiscovery->runTickForPlayer($player)`
      und zeigt `Discovered: N` im Status
- [x] Demo-CLI Galaxy-Overview filtert auf entdeckte Systeme + zeigt Counter unbekannter
- [x] 5 IT-Tests (no-telescope/L1/L3/cap/idempotent) grün
- [x] Suite grün (451/451)

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
