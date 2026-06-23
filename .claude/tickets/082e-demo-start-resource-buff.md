# T-082e: Demo Start-Resource-Buff (Day-1 Spielfluss)

**Type:** Feature
**Epic:** Demo CLI
**Domain:** Demo
**Blocked By:** T-082b
**Status:** Done
**Effort:** XS (~15min)
**Depends on:** T-082b (applyDemoBuff existiert)
**Blocks:** —

## Beschreibung

Aktueller Demo-Start hat `iron_ore: 0` im Storage. User muss erst auf Mine-
Produktion warten bevor irgend etwas gebaut werden kann — das macht Day-1
zähflüssig.

Ziel: Starter-Player kann **erste 10–15 Bauten + erste Forschung sofort
absetzen** ohne Mining-Tick abwarten oder über Wasser/Nahrung nachdenken zu
müssen.

## Aktueller Stand (Log-Auszug)

```json
"resources": {
    "iron_ore": 0,
    "water": 300,
    "food": 300,
    "oxygen": 300
},
"pop": {"total": 50, "assigned": 0, "cap": 150}
```

## Ziel-Stand

| Resource | Heute | Neu | Begründung |
|----------|-------|-----|------------|
| IRON_ORE | 0 | **3000** | Tier-0 (~700) + alle Tier-1-Mines (90) + 3-4 Upgrades (~600) + 2-3 Forschungen (~400) + Buffer |
| COAL | 0 | **800** | HUB-Upgrades (100/level) + Forschung (50-100 pro) + URANIUM_MINE-Cost + Buffer |
| COPPER_ORE | 0 | **400** | RESEARCH_LAB (50) + astronomy (80) + shipbuilding (100) + recycling (100) + Buffer |
| SILICON | 0 | **300** | RESEARCH_LAB (100) + ftl_tier_1 (100) + Buffer |
| IRON_BAR | 0 | **200** | shipbuilding-Forschung (100) + ftl_hyperdrive-Cost (300, partial) — restlicher kommt aus Smelter |
| WATER | 300 | **1500** | Pop-Verbrauch 1/pop/tick × 50 pop = 50/tick → 30 Ticks Buffer |
| FOOD | 300 | **1500** | siehe WATER |
| OXYGEN | 300 | **1500** | siehe WATER |
| Pop total | 50 | 50 (unverändert) | Cap 150 reicht für viele Bauten; Wachstum kommt im Tick |

## Acceptance Criteria

- [ ] `InteractiveDemoCommand::applyDemoBuff` erweitern um:
  - IRON_ORE 3000
  - COAL 800
  - COPPER_ORE 400
  - SILICON 300
  - IRON_BAR 200
  - W/F/O je 1500 boosten (heute 300)
- [ ] Smoke-Test: nach `--reset` zeigt Status alle Ziel-Werte
- [ ] Suite grün
- [ ] doc demo.md "Demo-Buff (T-082b/T-082e)" aktualisieren

## Out of Scope

- **Tier-0 Buildings vorab fertigbauen** — würde Tutorial-Effekt verfälschen
- **Pop-Anheben** — 50 mit Cap 150 reicht; mehr wäre cheaty
- **Forschung vorab** — Player soll Tech-Tree selbst freischalten

## Files

**Geändert:**
- `src/Demo/Command/InteractiveDemoCommand.php` (`applyDemoBuff`)
- `.claude/docs/demo.md` (Buff-Sektion aktualisieren)
