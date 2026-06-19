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

## Stub-Nodes (T-025 Foundation)

T-026 ersetzt diese Stubs durch echten Antrieb-Tree (inkl. `ftl_tier_2` für
Wormhole-Lock, der bereits in `Wormhole.requiredTechSlug` als Stub steht).

| Slug | Base-Duration | Max-Level | Prereqs | Cost (L1) |
|------|---------------|-----------|---------|-----------|
| `mining_efficiency_1` | 300s | 3 | — | 100 IRON_ORE + 50 COAL |
| `ftl_tier_1` | 600s | 1 | — | 200 IRON_BAR + 100 SILICON |

Wirkung der Nodes ist out-of-scope von T-025 — Foundation prüft nur, dass
Forschung mechanisch läuft. Effekt-Hooks (Mining-Boost, Wormhole-Unlock) folgen
mit T-127 / T-026.

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
