# T-170: Tech-Tree Building↔Research Gating

**Type:** Feature
**Status:** In Progress
**Effort:** M-L (~3h)
**Depends on:** T-009 (Building-Bau), T-025 (Forschungs-Framework)
**Blocks:** T-026, T-027, T-064, T-127ff (Tech-Branches gaten ihre Buildings über dieses Foundation-System)

## Beschreibung

Tech-Tree-Foundation, die Buildings via Forschung sperrt **und** Forschung
selbst durch zuvor gebaute Gebäude voraussetzt. Verhindert "1000 Optionen
ab Tag 1" und gibt einen klaren Progressionspfad:

```
Tier-0 frei  →  Tier-0-Gebäude bauen  →  Tier-1-Forschung freigeschaltet
                                       →  Tier-1-Forschung erforschen
                                       →  Tier-1-Gebäude freigeschaltet
                                       →  ... rekursiv ...
```

## Decisions (2026-06-19)

1. **Scope:** Foundation + Apply auf bestehende Buildings.
2. **Building-Prereq für Research:** "currently has, ready" — Player muss
   Building gerade besitzen + isReady. Lehnt Forschung ab wenn Building
   gerade upgraded wird (akzeptable Friktion).
3. **Tier-0 frei:** IRON_MINE, HUB, RESEARCH_LAB, WATER_TANK, FOOD_SILO,
   OXYGEN_STORAGE — Bootstrap ohne Lock.
4. **Demo-UX:** Locked-Buildings im Build-Menu anzeigen mit Reason
   ("🔒 Erfordert Forschung: basic_mining L1"). Volle Sicht auf den Tree.

## Tier-Mapping (T-170 Foundation)

**Tier-0 (frei, no lock):**
- IRON_MINE, HUB, RESEARCH_LAB, WATER_TANK, FOOD_SILO, OXYGEN_STORAGE

**Tier-1 Research-Nodes (T-170 ergänzt ResearchTree):**

| Slug | Building-Prereq | Research-Prereq | Unlocks Buildings |
|------|-----------------|-----------------|-------------------|
| `basic_mining` | IRON_MINE L1 | — | COAL_MINE, COPPER_MINE, IRON_STORAGE, COAL_STORAGE |
| `metallurgy` | IRON_MINE L2 | basic_mining L1 | IRON_SMELTER, IRON_BAR_STORAGE |
| `astronomy` | HUB L2 | basic_mining L1 | TELESCOPE, PROBE_LAB |
| `shipbuilding` | IRON_SMELTER L1 | metallurgy L1 | SHIPYARD |
| `advanced_mining` | IRON_SMELTER L1 | metallurgy L1 | SILICON_MINE, ALUMINUM_MINE, TITANIUM_MINE, URANIUM_MINE |
| `recycling` | HUB L2 | basic_mining L1 | RECYCLING_PLANT |

Stub-Nodes aus T-025 (`mining_efficiency_1`, `ftl_tier_1`) bleiben ergänzend
für Tests + spätere Branches (T-026 erweitert ftl_tier_1 zu echtem Antrieb-Tree).

## Acceptance Criteria

- [ ] `ResearchPrerequisite`-Interface + 2 Implementations:
  - `ResearchLevelPrerequisite(slug, level)`
  - `BuildingLevelPrerequisite(BuildingType, level)`
- [ ] `ResearchNode.prerequisites` umstellen auf `list<ResearchPrerequisite>`
- [ ] `StartResearchCommandService` Validation für beide Prereq-Typen
- [ ] `BuildingUnlockConfig` Service: `requiredResearch(BuildingType): ?array{slug,level}`
- [ ] `BuildingLockedException` (extends DomainException)
- [ ] `BuildBuildingCommandService` validiert Unlock vor Bau
- [ ] `BuildBuildingCommandService::isUnlockedFor(player, BuildingType): bool` Helper
  (Demo-CLI nutzt das für Lock-Anzeige)
- [ ] Demo-CLI Build-Building-Menu zeigt locked-Buildings mit 🔒 + Reason
- [ ] ResearchTree erweitert um 6 neue Nodes
- [ ] Tests:
  - ResearchPrerequisite-VOs
  - StartResearchCommandService building-prereq-Pfade
  - BuildBuildingCommandService unlock-Validation
  - BuildingUnlockConfig-Mapping
  - 1 IT-Test E2E: build IRON_MINE → research basic_mining → build COAL_MINE
- [ ] Doc: research.md (Prereq-Typen + Tier-Mapping), buildings.md (Unlock-Tabelle), demo.md
- [ ] Suite grün

## Open Questions

(keine — alle Decisions geklärt)

## Files

**Neu:**
- `src/Research/Model/Prerequisite/ResearchPrerequisite.php` (Interface)
- `src/Research/Model/Prerequisite/ResearchLevelPrerequisite.php`
- `src/Research/Model/Prerequisite/BuildingLevelPrerequisite.php`
- `src/Building/Service/BuildingUnlockConfig.php`
- `src/Building/Exception/BuildingLockedException.php`
- `tests/Research/Model/Prerequisite/*Test.php`
- `tests/Building/Service/BuildingUnlockConfigTest.php`
- `tests/Integration/TechTreeE2ETest.php`

**Geändert:**
- `src/Research/Model/ResearchNode.php` (prerequisites umgestellt)
- `src/Research/Service/ResearchTree.php` (+ 6 Nodes; mining_efficiency_1 bleibt)
- `src/Research/Service/StartResearchCommandService.php` (Prereq-Validation polymorph)
- `src/Building/Service/BuildBuildingCommandService.php` (Unlock-Validation)
- `src/Demo/Command/InteractiveDemoCommand.php` (Lock-Anzeige)
- `tests/Research/Service/StartResearchCommandServiceTest.php` (an neue Prereq-VOs anpassen)
- `.claude/docs/{research,buildings,demo}.md`

## Out of Scope (Folge-Tickets)

- **POI-Locks:** Asteroid/Wormhole-Discovery via Research (T-018b oder T-087)
- **Ship-Locks:** Schiff-Klassen via Research (T-128)
- **Probe-Locks:** ProbeType via Research (T-027)
- **Ever-had vs Currently-has** Switch — falls Foundation-Friktion zu groß
- **Research-Branch-Lock** (T-098 Specialist-Tracks lockt 1 Branch perma)
