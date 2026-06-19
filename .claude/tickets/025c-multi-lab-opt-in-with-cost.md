# T-025c: Multi-Lab Opt-In + Kosten-Trade-off

**Type:** Feature
**Status:** Open
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
2. Player kann optional **N weitere Labs** aus eigenen Planeten hinzufügen
3. Pro hinzugefügtem Lab:
   - Bonus auf effektives Lab-Level (Speed-Reduktion auf Forschungs-Dauer)
   - Marginal-Kosten on top der Base-Forschungs-Kosten

**Cost-Curve so tunen, dass:**
- L10-Primär + L10-Booster = ~ proportional günstig (sinnvoll)
- L10-Primär + L1-Booster = teurer als der Bonus reinholt (unrentable)

## Open Questions

1. **Bonus-Formel:** Gleiche geometric Decay 0.5 wie T-025b? Oder linearer
   Beitrag `bonus = bonusLab.level × 0.3`? Oder log-skaliert?
2. **Cost-Formel marginal:** Pauschal-Tax pro Booster (z.B. +25% Base-Cost
   pro Booster)? Oder gestaffelt nach Booster-Level (`cost += base × 0.05 × level²`)?
   Oder kombiniert (Pauschal + Level-Scale)?
3. **Persistence:** Welche Labs an aktiver Forschung beteiligt sind — separate
   Entity `ActiveResearchContribution(active_research_id, planet_id)` oder
   JSON-Spalte auf ActiveResearch?
4. **Lab-Verlust mid-research:** Booster-Lab wird zerstört (Foundation hat
   keinen Demolish, aber Übernahme/Battle später) → Forschung läuft weiter
   mit reduziertem Bonus oder pausiert/cancelt?
5. **Recompute on upgrade:** Booster-Lab upgraded mid-research → wirkt das
   retroaktiv? (T-064-Decision war: nein. Konsistent halten?)
6. **Demo-CLI-UX:** Multi-Select-Choice (Symfony hat das nativ)? Oder
   sequenziell: "Add another lab? [y/n]" pro Planet?

## Acceptance Criteria (Draft)

- [ ] T-025b Auto-Aggregator wird entfernt; `getEffectiveLabLevel` löscht oder
      wird zur reinen Single-Lab-Lookup-Methode
- [ ] `StartResearchCommand` Signatur erweitert: `primaryLabPlanetId` +
      `boosterPlanetIds: list<PlanetId>`
- [ ] Validation: alle Labs gehören dem Player + sind ready + Primary != Booster
- [ ] `ResearchDurationConfig::durationSeconds` nimmt `MultiLabContribution[]` /
      effectives Lab-Level
- [ ] `ResearchDurationConfig::resourceCost` nimmt zusätzlichen Cost-Aufschlag
      für Booster (formel-driven)
- [ ] `ActiveResearch` persistiert beteiligte Lab-Planet-IDs (Decision: separater
      Entity oder JSON-Spalte)
- [ ] Demo-CLI Forschung-Action: Lab-Auswahl-Flow + Live-Cost+Duration-Preview
      vor Confirm
- [ ] Tests: 6+ neu (Bonus-Calc, Cost-Calc, Single-Lab-Default, L1-Booster-zu-L10
      ist unrentable, Multi-Booster-stack, fehlende Booster-Validation)
- [ ] Doc: research.md vollständig überarbeiten (Multi-Lab-Sektion)
- [ ] Suite grün

## Out of Scope (Folge-Tickets)

- **Cancel-Forschung mit Refund** — eigenes Ticket, wenn nötig
- **Specialist-Track-Bonus auf Branches** — T-098
- **Allianz-Forschung mit fremden Labs** — T-117

## Notes

- Cost-Curve-Tuning kann nach Demo-Feedback iteriert werden — Baseline-Vorschlag
  wäre `marginal = base × 0.2 × (1 / (1 + bonusDelta))` damit L1-Booster zu
  L10-Primary tatsächlich Cost > Bonus erzeugt.
- T-025b-Decision-Doc ist veraltet sobald T-025c Done — das alte File markieren als
  "Superseded by T-025c".
