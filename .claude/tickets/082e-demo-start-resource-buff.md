# T-082e: Demo Start-Resource-Buff (Day-1 Spielfluss)

**Type:** Feature
**Status:** Open
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
| IRON_ORE | 0 | 1500 | Deckt alle Tier-0-Bauten (HUB+WATER_TANK+FOOD_SILO+OXYGEN_STORAGE+IRON_MINE = 500) + 1-2 Upgrades + Forschungs-Cost |
| COAL | 0 | 300 | HUB-Upgrade (50/level), basic_mining-Forschung (50), Buffer |
| COPPER_ORE | 0 | 150 | RESEARCH_LAB-Cost (50) + astronomy-Forschung (80) |
| SILICON | 0 | 100 | RESEARCH_LAB-Cost (100) |
| WATER | 300 | 600 | Mehr Buffer für Pop-Wachstum + Schiff-Build |
| FOOD | 300 | 600 | Pop-Wachstum |
| OXYGEN | 300 | 600 | Pop-Wachstum |
| Pop total | 50 | 50 (unverändert) | Cap 150 reicht für viele Bauten; Wachstum kommt im Tick |

## Acceptance Criteria

- [ ] `InteractiveDemoCommand::applyDemoBuff` erweitern um:
  - IRON_ORE 1500 setzen
  - COAL 300
  - COPPER_ORE 150
  - SILICON 100
  - W/F/O auf 600 boosten (heute 300)
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
