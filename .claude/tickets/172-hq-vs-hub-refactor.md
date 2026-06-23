# T-172: HQ-Building + HUB-Refactor (Pop-Building-Familie)

**Type:** Feature
**Epic:** Building System
**Domain:** Building
**Blocked By:** T-006, T-171
**Status:** Done
**Effort:** M (~2.5h)
**Depends on:** T-006 (HUB-Foundation), T-171 (Uniqueness/Slot-Cap)
**Blocks:** T-070 Pop-QoL-Buildings, weitere Pop-Tier-Folge

## Beschreibung

Heute ist `HUB` unique + zentral (Pop-Cap +50/Level + W/F/O-Storage +200/Level).
T-171 hat es als strikt-unique markiert. T-172 spaltet das auf:

- **HQ** (neu): strikt-unique, zentrale Planet-Verwaltung
- **HUB** (refactor): multi-instance, kann mehrfach gebaut werden (Wohnsiedlungen)

Hintergrund: Genre-typisch ist ein zentrales Verwaltungs-Building unique
(Headquarter, Command-Center), aber Wohngebäude/Lebensraum (Hub, District,
Habitat) skalieren über Anzahl × Level.

Außerdem: `CONSTRUCTION_HUB` (T-064b) hat suboptimales Naming — Hub deutet
auf Pop, aber das Building ist Industrial. Sollte umbenannt werden.

## Open Questions (vor Implementation klären)

### Q1: Was tut HQ konkret?

- (a) **Nur Pop-Cap-Foundation:** Basis-Cap (z.B. 100), kein W/F/O-Storage,
  rein Lebensraum-Foundation. Hub übernimmt restliche Pop-Mechanik.
- (b) **Multi-Funktion:** Pop-Cap + Storage + Foundation für andere Funktionen
  (Trade-Output, Building-Slots, Defense-Coordinator).
- (c) **Reine Voraussetzung:** kein eigener Effekt; existiert als L1 ab
  Planet-Claim, kann hochgelevelt werden für Building-Slots-Bonus oder
  ähnliches (T-094c-Folge).

### Q2: Was tut HUB neu?

- (a) **Pop-Cap +X / Storage +Y per Instance × Level** — multi-stacking
  möglich. Player kann 3× HUB L2 = 6× HUB L1-Wert.
- (b) **Nur Pop-Cap, kein Storage** — Storage-Cap kommt nur über
  dedizierte Tank/Silo/Storage-Buildings.

### Q3: Existing HUB-Refs (Forschung-Prereqs, Demo-Buff)?

`astronomy` + `recycling` Forschung haben `HUB L2` als Building-Prereq
(T-170). Mit Refactor: bleibt das HUB oder wird HQ?

- (a) Bleibt HUB (Pop-Wohngebäude muss L2 sein → spiegelt Bevölkerungswachstum)
- (b) Wird HQ (zentrale Verwaltung muss aufgerüstet sein → spiegelt
  technologischen Reifegrad)

Demo-Buff (T-082b) legt heute Hub L1 vorab fertig. Wird das HQ L1 oder HUB L1?

### Q4: Auto-Build HQ bei ClaimStartPlanet?

- (a) **Ja:** Jeder Planet hat automatisch HQ L1 ab Bootstrap (analog
  Sims-City-Konzept "es gibt immer ein Rathaus"). Player muss es nicht
  bauen; nur upgraden.
- (b) **Nein:** Player muss HQ als erstes bauen (Tier-0, kostet aber).

### Q5: Slot-Size HQ + neuer HUB?

- HQ unique, vermutlich Slot-Size **3** (Heavy-Strategic) oder **2** (analog
  alter HUB)
- HUB neu non-unique, Slot-Size **1** (small Building, multi-stack lohnt)

### Q6: CONSTRUCTION_HUB-Rename?

In diesem Ticket gleich erledigen oder Folge-Ticket?
Vorschläge: `CONSTRUCTION_YARD`, `ENGINEERING_BAY`, `FABRICATION_PLANT`,
`CONSTRUCTION_DEPOT`.

### Q7: Migration / Existing-State?

Demo-DB hat bereits HUB-Buildings (z.B. via applyDemoBuff). Beim Reset wird
sowieso alles dropped — daher Foundation kann frisch starten. Aber Demo-Migration
ist trivial: HUB-Field bleibt, Semantik ändert sich.

## Acceptance Criteria (Draft)

Konkrete AC erst nach Q1-Q7-Klärung. Grob:

- [ ] `BuildingType::HQ` neu (unique)
- [ ] `BuildingType::HUB` bleibt, isUnique() = false
- [ ] Pop-Cap-Logic aufgesplittet zwischen HQ + HUB
- [ ] Storage-Contribution analog aufgesplittet
- [ ] Forschungs-Prereqs angepasst (HUB → HQ wo logisch)
- [ ] Demo-Buff: HQ L1 vorab statt HUB L1
- [ ] ClaimStartPlanet: ggf. HQ L1 auto-buildg (Q4)
- [ ] CONSTRUCTION_HUB Rename mit erledigen (Q6)
- [ ] Migration / Test-Update
- [ ] Doc buildings.md überarbeiten

## Out of Scope

- Multi-HQ-Pattern (z.B. Provinz-Hauptstadt vs Reichs-HQ) — späteres Ticket
- HQ-bound Pop-Wachstum-Boni — eigenes Folge-Ticket bei Bedarf
