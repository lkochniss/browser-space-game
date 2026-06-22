# T-025c: Multi-Lab Opt-In + Kosten-Trade-off

**Type:** Feature
**Status:** Done
**Effort:** M (~3-4h)
**Depends on:** T-025 (Forschungs-Foundation), T-025b (Auto-Aggregator → wird ersetzt)
**Blocks:** —
**Supersedes:** T-025b Auto-Aggregator-Verhalten (Single-Source bleibt)

## Beschreibung

T-025b stackt aktuell **automatisch alle** RESEARCH_LABs des Players mit
geometric Decay → kostenlos. User-Vision: Multi-Lab ist eine **bewusste
Entscheidung pro Forschung** mit Kosten:

> "Man wählt einen Planeten, auf dem geforscht wird und kann weitere zur
> Forschung hinzufügen. Diese geben einen Bonus abhängig vom eigenen Lab-
> Level. ABER: Das Hinzufügen kostet weitere Rohstoffe. Es ist möglich,
> wenn auch nicht sinnvoll, einer Lab L10-Forschung eine Lab L1
> hinzuzufügen, da die Zusatzkosten den Bonus fressen."

Dezentralisierte Multi-Planet-Forschung soll Aufwand verursachen, der sich
in marginal-cost wiederspiegelt — nicht-triviales Multi-Min-Maxing.

## Zielmechanik

**Beim StartResearch:**
1. Player wählt **Primär-Lab-Planet** (muss eines haben)
2. Player kann optional **N weitere Labs** aus eigenen Planeten hinzufügen (kein Hard-Cap — Cost reguliert)
3. Pro hinzugefügtem Lab:
   - Bonus auf effektives Lab-Level (Speed-Reduktion auf Forschungs-Dauer) via Geometric-Decay
   - Marginal-Kosten on top der Target-Level-skalierten Forschungs-Kosten

## Decisions (locked)

### D1 — Bonus-Formel: Geometric Decay 0.5

```
boosterLvls = sort(boosterLevels, desc)            // höchstes zuerst
effectiveLab = primaryLvl + sum_i(boosterLvls[i] × 0.5^(i+1))
```

Beispiel (Primary L10 + Booster [L10, L8, L1]):
`10 + 10×0.5 + 8×0.25 + 1×0.125 = 17.125`

Speed-Reduktion via existierender T-025-Formel:
`durationSeconds = node.baseDurationSeconds × 2^(targetLvl-1) ÷ pow(1.18, effectiveLab-1)`

(`effectiveLab` ist `float`, Formel akzeptiert das.)

### D2 — Marginal-Cost-Formel: Pauschal + Lvl-Mismatch-Penalty

```
baseScaled = node.resourceCostBase × 2^(targetLvl-1)
N = count(boosterLabs)
mismatchPenalty = sum(max(0, primaryLvl - boosterLvl)²)  // primary=anchor
costMultiplier  = 1 + (0.10 × N) + (0.02 × mismatchPenalty)
finalCost       = baseScaled × costMultiplier
```

**Verhalten** (Base 1000, Primary L10):
- `+L10 Booster`: ×1.10 = 1100 (10% Aufschlag, lohnt klar)
- `+L5 Booster`:  ×1.10 + 0.02×25 = ×1.60 = 1600 (grenzwertig)
- `+L1 Booster`:  ×1.10 + 0.02×81 = ×2.72 = 2720 (klar unrentable)
- `+L10+L8 Booster`: ×1.20 + 0.02×4 = ×1.28 = 1280

### D3 — Persistence: JSON-Spalte auf ActiveResearch

```
active_research.booster_planet_ids: json (list<UUID-String>)
```

- Doctrine `JSON_ARRAY`-Type, max ~5 Booster realistisch
- Kein eigener Repo nötig, kein M:1-Join — nur Read-Side beim Status-Display
- Migration: ALTER TABLE active_research ADD booster_planet_ids JSON NOT NULL DEFAULT (JSON_ARRAY())

### D4 — Mid-Research: Frozen-at-Start

- Beim StartResearch wird `effectiveLab` berechnet, `finished_at` fixiert, Booster-Planet-IDs persistiert
- Lab-Upgrade während laufender Forschung: **ignored** (kein Recompute) — konsistent mit T-064-Decision
- Lab-Verlust (zukünftig durch Battle/Übernahme): **ignored** — Forschung läuft fertig
- Foundation hat aktuell keinen Demolish/Battle → Lab-Loss nur theoretisch, keine Edge-Case-Behandlung nötig

### D5 — Demo-CLI-UX: Multi-Select native

Symfony `ChoiceQuestion` mit `multiple=true` für Booster-Auswahl. Flow:
1. Choice "Primary Lab" (single) — alle Player-Lab-Planeten
2. Choice "Booster Labs" (multi, comma-separated, blank = keine) — verbleibende Lab-Planeten
3. Live-Preview: `effectiveLab`, `Cost`, `Duration` vor Confirm

### D6 — T-025b Cleanup: Löschen + neu schreiben

- Alte Methode `ResearchTree::getEffectiveLabLevelForPlayer($player)` (Auto-Aggregator) wird gelöscht
- Neue Methode: `ResearchTree::computeEffectiveLabLevel(int $primaryLvl, array<int> $boosterLvls): float` (reine Funktion, testbar ohne Player)
- Alle Caller in StartResearchCommandService + Demo-CLI auf neue Methode umstellen

### D7 — Cost-Base: Target-Level-skaliert

`baseScaled = node.resourceCostBase × 2^(targetLvl-1)` — siehe D2. Booster-Cost wächst proportional mit Forschungs-Stufe (späte Forschung = teure Forschung = teure Booster).

### D8 — Booster-Cap: keiner

User-Decision: kein Hard-Cap. Cost-Curve (D2) sorgt natürlich dafür dass irgendwann jeder weitere Booster mehr kostet als er bringt. UI muss mit "vielen Labs" umgehen können — sortiert + scrollbar.

## Acceptance Criteria

### Cleanup T-025b

- [ ] `ResearchTree::getEffectiveLabLevelForPlayer` gelöscht
- [ ] Neue Methode `ResearchTree::computeEffectiveLabLevel(int $primaryLvl, array $boosterLvls): float` (geometric decay 0.5, sortiert desc)
- [ ] T-025b-Decision-Doc / Notes als "Superseded by T-025c" markieren

### Command + Service

- [ ] `StartResearchCommand` Signatur: `playerId, nodeSlug, targetLevel, primaryLabPlanetId: PlanetId, boosterLabPlanetIds: list<PlanetId>` (default `[]`)
- [ ] `StartResearchCommandService` Validation:
  - Primary-Planet gehört Player + hat fertiges RESEARCH_LAB (`isReady($now)`)
  - Jeder Booster-Planet gehört Player + hat fertiges RESEARCH_LAB
  - Primary ∉ Booster + Booster sind unique
  - Genug Resources für `finalCost` (über alle Player-Planeten aggregiert, analog T-025-Foundation)
- [ ] `ResearchDurationConfig::durationSeconds(node, targetLevel, effectiveLab: float)` akzeptiert float
- [ ] Neue Methode `ResearchDurationConfig::resourceCost(node, targetLevel, primaryLvl, array $boosterLvls): array<resVal,int>` — implementiert D2-Formel

### Persistence

- [ ] `ActiveResearch` bekommt `booster_planet_ids: list<UUID-String>` (JSON-Spalte)
- [ ] Migration `Version20260620XXXXXX` — `ALTER TABLE active_research ADD COLUMN booster_planet_ids JSON NOT NULL`
- [ ] Default-Wert für bestehende ActiveResearch-Rows = `[]` (leer, single-lab-Verhalten)

### Demo-CLI

- [ ] Forschung-Action ersetzt aktuelle Single-Click-Auswahl durch:
  - Schritt 1: Primary-Lab-Choice (single)
  - Schritt 2: Booster-Labs-Choice (multiple, blank = keine)
  - Schritt 3: Preview-Block (effectiveLab, finalCost, Duration) + Confirm
- [ ] Wenn Player nur 1 Lab-Planet hat → Step 2 entfällt (auto-empty)

### Tests

- [ ] `ResearchTreeMultiLabTest` — `computeEffectiveLabLevel` mit:
  - Single (no booster) → primary
  - Single L10 + [L10] → 15.0
  - L10 + [L10, L8, L1] → 17.125
  - L1 + [L1, L1, L1] → 1.875
- [ ] `ResearchDurationConfigCostTest` — D2-Formel:
  - L10 + [L10] = ×1.10 (lohnt)
  - L10 + [L5]  = ×1.60 (grenzwertig)
  - L10 + [L1]  = ×2.72 (unrentable)
  - L10 + [L10, L8] = ×1.28
- [ ] `StartResearchCommandServiceMultiLabTest` (IT):
  - Primary nur → bestehender Pfad
  - Primary + Booster → Cost + Duration korrekt
  - Booster nicht ready → ValidationException
  - Booster gehört anderem Player → ValidationException
  - Primary ∈ Booster → ValidationException
- [ ] `ActiveResearchPersistenceTest` — JSON-Roundtrip booster_planet_ids
- [ ] Suite grün

### Docs

- [ ] `.claude/docs/research.md` — Multi-Lab-Sektion (Formeln D1+D2, Frozen-at-Start, JSON-Persistence)
- [ ] `.claude/docs/decisions.md` — Eintrag "T-025c Multi-Lab Opt-In"

## Affected Tests

- `tests/Research/Service/ResearchTreeMultiLabTest.php` (neu, Unit)
- `tests/Research/Service/ResearchDurationConfigCostTest.php` (neu, Unit)
- `tests/Research/Service/StartResearchCommandServiceMultiLabTest.php` (neu, IT)
- `tests/Research/Model/ActiveResearchPersistenceTest.php` (neu, IT)
- `tests/Research/Service/ResearchTreeTest.php` (anpassen — alte `getEffectiveLabLevelForPlayer`-Tests entfernen)
- `tests/Demo/Action/StartResearchActionTest.php` (anpassen — neue Multi-Lab-UX)

## Fixtures Needed

No — bestehende WorldFixture liefert Multi-Planet-Player + LAB-Builder; reicht für IT.

## Out of Scope (Folge-Tickets)

- **Cancel-Forschung mit Refund** — eigenes Ticket bei Bedarf (aktuell: Forschung läuft immer durch)
- **Specialist-Track-Bonus auf Branches** — T-098
- **Allianz-Forschung mit fremden Labs** — T-117
- **Booster-Lab-Soft-Lock** (Booster-Lab kann während Research keine eigene Forschung starten) — Foundation hat 1-Active-pro-Player-Constraint, also nicht relevant. Wenn Multi-Active später kommt, neues Ticket.

## Notes

- Cost-Curve-Tuning kann nach Demo-Feedback iteriert werden — D2-Konstanten (0.10 + 0.02) sind Tuning-Knobs in `ResearchDurationConfig`-Klasse, kein Magic-Number
- D2-Formel ist asymmetrisch: Booster-Lvl > Primary-Lvl bekommt **keine** Mismatch-Penalty (`max(0, ...)`) → starke Booster sind günstig, schwache teuer. Das ist Absicht (User-Vision)
- Effective-Lab als `float` ist neu (alte Foundation hatte int) — sicherstellen dass alle Caller den Type-Hint auf `float` umstellen
