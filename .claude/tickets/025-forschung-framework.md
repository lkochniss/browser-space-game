# T-025: Forschungs-Framework

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** No

## Description

`docs/Forschung.md`: Tech-Bäume. Aktuell nur Antriebstechnologie genannt — strukturell offen. Framework für Research-Nodes mit Levels + Voraussetzungen erstellen.

## AC

- [ ] `Research` Domain
- [ ] `ResearchNode` definiert (id, name, prerequisites, max-level, cost-per-level)
- [ ] `Player` hält `PlayerResearch` (Map node→level)
- [ ] `StartResearchCommand` + Handler — verbraucht Resources, prüft prereqs + Forschungs-Building?
- [ ] Tick-Processor: laufende Forschung erhöht Progress; bei Voll-Progress Level++
- [ ] Service `ResearchTree` zentral konfiguriert

## Affected

- Neu: `src/Research/Model/ResearchNode.php`, `PlayerResearch.php`
- Neu: `src/Research/Service/ResearchTree.php`
- Neu: `src/Research/Command/StartResearchCommand.php`
- Neu: `src/Tick/Processor/ResearchProgressProcessor.php`

## Open Questions

1. Eigenes Forschungs-Building (Labor) als prereq? Doc nennt keins explizit — aber typisch fürs Genre.
2. Multiple Forschungen parallel oder eine zur Zeit?
3. Punkte aus Bevölkerung/Buildings oder reine Tick-Zeit-basiert?
