# T-025: Forschungs-Framework (Wallclock-based Foundation)

**Type:** Feature
**Status:** Paused (T-082d zwischengeschoben 2026-06-19)
**Effort:** M-L (~3-4h)
**FX:** No
**MIG:** No (neue Tabellen via SchemaTool)
**Blocks:** T-026, T-027, T-064, T-069, T-098, T-115, T-117, T-126

## Beschreibung

Foundation für das Research-System. **Wallclock-basiert** (analog T-062 Real-
Time-Construction), kein RP-Pool, kein Tick-RP-Akkumulation.

Pro Player: 1 aktive Forschung gleichzeitig. RESEARCH_LAB-Building auf Planet
ist Voraussetzung; höheres Lab-Level reduziert Forschungs-Dauer multiplikativ
(Speed-Boost). Höheres Forschungs-Level erhöht Dauer exponentiell (analog
Building-Upgrades).

## Decisions (2026-06-19)

1. **Scope:** T-025 Foundation only. T-026 (Antrieb-Tree) + T-069 (Lab-Tier-
   Erweiterung) als Folge-Tickets nach Bestätigung der Foundation.
2. **Lab-Gate:** RESEARCH_LAB-BuildingType ist Voraussetzung. T-025 enthält
   **Minimal-Lab** (Type + Cost + Speed-Multiplier). T-069 erweitert um
   Tier-Gates / Cap-Levels / Multi-Lab-Stacking.
3. **Parallel:** 1 aktive Forschung pro Player. Multi-Lab-Boost (Speed via
   N Labs auf N Planeten) → Folge-Ticket T-025b (Draft).
4. **Punkte-Modell:** **Wallclock-Sekunden, kein RP-Pool**.
   - `effectiveDuration = baseDuration(node) × levelMultiplier(targetLevel) ÷ labSpeedMultiplier(maxLabLevel)`
   - Lab-Speed: L1=1.0×, L2=0.85×, L3=0.72×, ... (Decreasing factor pro Level)
   - Level-Skalierung: `2^(targetLevel-1)` analog Buildings (T-010 Pattern)

## Acceptance Criteria

- [ ] Domain `src/Research/`:
  - `ValueObject/ResearchId.php` (UUID)
  - `Model/ResearchNode.php` (readonly VO: slug, name, description, baseDurationSeconds, maxLevel, prerequisites: list<{slug, level}>, resourceCostBase: array<resourceVal,int>)
  - `Service/ResearchTree.php` (zentrale Config — analog BuildingCostConfig — startet mit 2 Stub-Nodes für Foundation-Test, T-026 ergänzt echte FTL-Nodes)
  - `Model/PlayerResearch.php` (Entity: player_id + node_slug + level, UNIQUE(player, slug))
  - `Model/ActiveResearch.php` (Entity: player_id UNIQUE, node_slug, target_level, started_at, finished_at)
  - `Repository/{PlayerResearch,ActiveResearch}Repository.php`
  - `Service/ResearchDurationConfig.php` (Berechnungs-Formel inkl. Lab-Boost)
  - `Service/StartResearchCommandService.php` (Validation: prereqs, lab-level, no-active, cost; Effekt: Resources abziehen + ActiveResearch persistieren)
  - `Service/ResearchCompletionService.php` (global, kein TickProcessor — analog FleetArrivalService/TelescopeDiscoveryService): findet ActiveResearch mit `finished_at <= now`, upsert PlayerResearch.level++, löscht ActiveResearch
  - `Command/StartResearchCommand.php` + Handler
  - `Exception/*.php` (Lab-Missing, AlreadyResearching, PrerequisiteNotMet, MaxLevelReached, ResearchNodeNotFound)
- [ ] `BuildingType::RESEARCH_LAB` + Cost (~200 Iron + 100 Si + 50 Cu + 15 pop) + Duration (45min)
- [ ] `Planet::getResearchLabLevel($now)` Helper
- [ ] Demo-CLI: "Forschung" Menu-Action (ersetzt T-025-Stub) — listet Nodes mit ✓/Active-State/Locked-Reason, dispatcht StartResearchCommand
- [ ] Demo-CLI: Tick-Forward callt `researchCompletion->runTickForPlayer($player)`
- [ ] Tests: ResearchTree-Stub, DurationConfig (Formel), StartResearchCommandService (alle Validation-Pfade), ResearchCompletionService (advance time → level++)
- [ ] Demo-Goal-Liste (T-082c) erweitern um "Erste Forschung abschließen" (6. Goal)
- [ ] Doc: `.claude/docs/research.md` neu, README-Index ergänzen, buildings.md (RESEARCH_LAB), demo.md (Goals + Action)
- [ ] Suite grün

## Open Questions

(keine — Decisions geklärt)

## Files

**Neu:**
- `src/Research/` Domain-Folder (siehe AC)
- `src/Common/Doctrine/Type/ResearchIdType.php`
- `tests/Research/Service/{ResearchTree,DurationConfig,StartResearch,Completion}Test.php`
- `.claude/docs/research.md`

**Geändert:**
- `src/Building/ValueObject/BuildingType.php` (+ RESEARCH_LAB)
- `src/Building/Service/{BuildingCostConfig,BuildingDurationConfig}.php`
- `src/Planet/Model/Planet.php` (+ getResearchLabLevel-Helper)
- `src/Demo/Command/InteractiveDemoCommand.php` (Forschung-Action + Tick-Forward-Hook)
- `src/Demo/Service/DemoGoalChecker.php` (+ "Erste Forschung" Goal)
- `config/packages/doctrine.yaml` (+ research_id Custom-Type)
- `.claude/docs/{buildings,demo,resources}.md`
- `.claude/docs/README.md` (Index)

## Stub-Nodes für Foundation-Test

T-025 startet mit 2 Demo-Nodes (T-026 ersetzt durch echten Antrieb-Tree):

| Slug | Base-Duration | Max-Level | Prereqs | Cost (L1) |
|------|---------------|-----------|---------|-----------|
| `mining_efficiency_1` | 300s | 3 | — | 100 IRON_ORE + 50 COAL |
| `ftl_tier_1` | 600s | 1 | — | 200 IRON_BAR + 100 SILICON |

Wirkung der Nodes ist out-of-scope — Foundation prüft nur, dass Nodes
forschbar sind und Levels persistiert werden. Effekt-Hooks (Mining-Boost,
Wormhole-Unlock) folgen mit T-127 / T-026.

## Out of Scope (Folge-Tickets)

- **Multi-Lab-Boost:** mehrere Labs auf mehreren Planeten stacken Speed → **T-025b** (Draft)
- **Lab-Tier-Gates:** L3 unlockt höhere Tech-Tiers etc. → **T-069**
- **Antrieb-Tree:** echte Tech-Nodes statt Stubs → **T-026**
- **Construction-Speed-Boost:** Forschung beschleunigt Bauzeit → **T-064** (Decisions vorab)
- **Allianz-Forschung:** Cross-Player Donate → **T-117**
- **Player-Skill-Slots:** → T-098, T-126
