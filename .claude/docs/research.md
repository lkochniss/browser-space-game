# Research (Forschungs-Framework)

## Zweck (T-025)

Wallclock-basierte Forschung. Pro Player läuft maximal **1 aktive Forschung**;
RESEARCH_LAB-Building auf einem Player-Planeten ist Voraussetzung; höheres
Lab-Level reduziert Forschungs-Dauer multiplikativ.

T-025 ist Foundation: Domain + Stub-Nodes + Demo-Action. Echte Tech-Trees mit
Wirkung folgen via Branch-Tickets (T-026 Antrieb, T-027 Planetologie, T-064
Bauzeit-Boost, T-127 Mining-Branch …).

## Decisions (2026-06-19)

1. **Wallclock, kein RP-Pool** — `finished_at = now + duration`; Tick-Resolver
   prüft `finished_at <= now` analog FleetArrivalService.
2. **1 Forschung gleichzeitig** — UNIQUE(player_id) auf `active_research`-Tabelle.
3. **Lab-Gate** — mindestens 1 fertiger RESEARCH_LAB irgendwo beim Player.
4. **Nur höchstes Lab-Level zählt** (Foundation). Multi-Lab-Stacking → T-025b.

## Wallclock-Formel

```
effectiveDuration = node.baseDurationSeconds × 2^(targetLevel-1) ÷ pow(1.18, maxLabLevel-1)
```

| Lab-Level | Speed-Multiplier | -% |
|-----------|------------------|----|
| 1 | 1.00× | 0% |
| 2 | 1.18× | -15% |
| 3 | 1.39× | -28% |
| 4 | 1.64× | -39% |
| 5 | 1.93× | -48% |

Resource-Cost skaliert ebenfalls `2^(targetLevel-1)` — analog Building-Upgrades.

## Domain-Modell

| Entity | Felder | Zweck |
|--------|--------|-------|
| `ResearchNode` (VO) | slug, name, description, baseDurationSeconds, maxLevel, prerequisites: list<{slug,level}>, resourceCostBase: array<resVal,int> | Deklarative Tree-Definition |
| `PlayerResearch` | id, player_id, node_slug, level — UNIQUE(player_id, node_slug) | Persistierter Forschungsstand |
| `ActiveResearch` | id, player_id (UNIQUE), node_slug, target_level, started_at, finished_at | Aktuell laufende Forschung |

## Services

| Service | Zweck |
|---------|-------|
| `ResearchTree` | Zentrale Node-Konfiguration (analog `BuildingCostConfig`); registriert alle verfügbaren Nodes |
| `ResearchDurationConfig` | Wallclock-Formel + Cost-Skalierung |
| `StartResearchCommandService` | Validation + Effect (Resources abziehen + ActiveResearch persistieren) |
| `ResearchCompletionService` | Globaler Tick-Resolver — `runTickForPlayer(player)` upsert PlayerResearch.level++ und löscht ActiveResearch wenn `finished_at <= now` |

## CommandFlow

```
StartResearchCommand(playerId, nodeSlug)
  ↓
StartResearchCommandService.__invoke
  ↓ Validation: Lab-Gate, kein Active, Node existiert, Max-Level, Prereqs, Resources
  ↓ Effekt: Resources abziehen + ActiveResearch persistieren mit finished_at
  ↓
... (Wallclock vergeht) ...
  ↓
TickForward → ResearchCompletionService.runTickForPlayer
  ↓ ActiveResearch.isFinished(now)? → ja
  ↓ Upsert PlayerResearch.level++ + Remove ActiveResearch
```

## Polymorphe Prerequisites (T-170)

ResearchNode.prerequisites ist `list<ResearchPrerequisite>`. 2 Implementations:

| Implementation | Bedingung |
|----------------|-----------|
| `ResearchLevelPrerequisite(slug, level)` | `PlayerResearch[slug].level >= level` |
| `BuildingLevelPrerequisite(BuildingType, level)` | Player hat Building auf >= level + isReady($now) auf irgendeinem Planeten |

`StartResearchCommandService` implementiert `PlayerResearchLookup` und ruft
`prereq->isMetBy($player, $now, $this)` für jeden Eintrag. `PrerequisiteNotMetException`
zeigt das fehlende Prereq via `describe()` an den Player ("Building iron_mine L2").

**Decision: "currently-has-ready":** Building-Prereq braucht das Gebäude im
aktuellen Tick + ready. Während Upgrade-Phase fällt es kurz aus dem Prereq —
akzeptable Friktion. Foundation hat keinen Demolish, also kein nachträgliches
Lock-Risiko.

## Tech-Tree-Tier-Mapping (T-170)

Tier-0 (frei): IRON_MINE, HUB, RESEARCH_LAB, WATER_TANK, FOOD_SILO, OXYGEN_STORAGE.

| Forschung | Building-Prereq | Forschungs-Prereq | Unlocks Buildings |
|-----------|-----------------|-------------------|-------------------|
| `basic_mining` | IRON_MINE L1 | — | COAL_MINE, COPPER_MINE, IRON_STORAGE, COAL_STORAGE |
| `metallurgy` | IRON_MINE L2 | basic_mining L1 | IRON_SMELTER, IRON_BAR_STORAGE |
| `astronomy` | HUB L2 | basic_mining L1 | TELESCOPE, PROBE_LAB |
| `shipbuilding` | IRON_SMELTER L1 | metallurgy L1 | SHIPYARD |
| `advanced_mining` | IRON_SMELTER L1 | metallurgy L1 | SILICON_MINE, ALUMINUM_MINE, TITANIUM_MINE, URANIUM_MINE |
| `recycling` | HUB L2 | basic_mining L1 | RECYCLING_PLANT |

`BuildingUnlockConfig` mappt BuildingType → required Research. `BuildBuildingCommandService`
prüft via `checkUnlock($planet, $type)` vor Cost-Validation und wirft `BuildingLockedException`.

`BuildBuildingCommandService::isUnlockedFor($player, $type)` als Public-Helper für
Demo-CLI / UI (zeigt 🔒 + Reason).

## Antriebs-Tree (T-026)

7 Nodes mit linearer Chain Standard-Antriebe + FTL:
`propulsion_hydrogen → propulsion_ion → propulsion_fusion → propulsion_antimatter`,
parallel `ftl_hyperdrive → ftl_warp → ftl_jumpdrive` (FTL braucht
`propulsion_fusion` als Prereq). `ftl_hyperdrive` L1 schaltet Inter-System-
Travel frei (siehe fleets.md). `ftl_warp` ist Wormhole-Tech-Slug für T-026b.

## Domain-Exceptions

| Exception | Trigger |
|-----------|---------|
| `ResearchLabMissingException` | Kein fertiges RESEARCH_LAB beim Player |
| `AlreadyResearchingException` | ActiveResearch existiert bereits |
| `ResearchNodeNotFoundException` | Slug nicht im ResearchTree |
| `MaxLevelReachedException` | targetLevel > node.maxLevel |
| `PrerequisiteNotMetException` | Prereq-Slug nicht auf required Level |
| `InsufficientResearchResourcesException` | Resources über alle Player-Planeten reichen nicht |

Alle extenden `\DomainException`. Validation vor Mutation — kein State-Change bei Failure.

## RESEARCH_LAB-Building

Cost: 200 IRON_ORE + 100 SILICON + 50 COPPER_ORE + 15 pop
Duration: 45min × 2^level

`Planet::getResearchLabLevel($now)` Helper liefert höchstes Level eines fertigen
LAB auf dem Planeten. `StartResearchCommandService` aggregiert max über alle
Player-Planeten (T-025b stackt später).

## Demo-CLI Integration

- "Forschung"-Action ersetzt T-025-Stub. Listet alle Tree-Nodes mit:
  - Cost + Duration-Preview (live nach maxLab-Level)
  - Aktuelles Level + ✓ MAX wenn ausgereizt
  - Aktive Forschung + Restzeit
- Tick-Forward callt `researchCompletion->runTickForPlayer($player)`; Status
  zeigt `Research-done: N`
- Demo-Goal #6 "Erste Forschung abschließen" (T-082c-Erweiterung)

## Files

- `src/Research/ValueObject/ResearchId.php` (UUID)
- `src/Research/Model/{ResearchNode,PlayerResearch,ActiveResearch}.php`
- `src/Research/Repository/{PlayerResearch,ActiveResearch}Repository.php`
- `src/Research/Service/{ResearchTree,ResearchDurationConfig,StartResearchCommandService,ResearchCompletionService}.php`
- `src/Research/Command/StartResearchCommand.php` + Handler
- `src/Research/Exception/*.php` (6 DomainExceptions)
- `src/Common/Doctrine/Type/ResearchIdType.php`

## Cross-Domain

- **Building/RESEARCH_LAB**: Voraussetzung + Speed-Source
- **Player + Planet**: Player-Aggregat (Research auf Player-Ebene), Planet hält Lab-Building
- **Resource**: Cost wird über alle Player-Planeten aggregiert + abgezogen
- **Demo/Goals**: Demo-Goal #6 + Forschung-Action

## Geplant

- **T-025b** Multi-Lab-Boost — mehrere Labs auf mehreren Planeten stacken Speed
- **T-026** Antrieb-Tree (echte FTL-Nodes inkl. ftl_tier_2 Unlock)
- **T-027** Planetologie-Forschung (Probe-Boost)
- **T-064** Bauzeit-Boost (Decisions vorab dokumentiert)
- **T-069** Lab-Tier-Mechanik (requiredLabLevel-Gates pro Node)
- **T-098** Specialist-Tracks (Branch-spezifischer Speed-Multiplier)
- **T-117** Allianz-Forschung (Cross-Player-Donate)
- **T-126/T-128/T-127/T-129/T-134/T-135/T-136/T-137/T-138** Echte Tech-Branches
- **T-139** Tech-Tree Master-Design (Cap, Branch-Lock-Konsistenz)
