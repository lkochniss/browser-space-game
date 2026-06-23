# T-179: Pop als Storage-Item (Refactor)

**Type:** Feature (Refactor)
**Epic:** Storage Vision
**Domain:** Planet
**Blocked By:** T-177, T-178, T-180
**Status:** Blocked (by T-178)
**Effort:** L (~6-8h, hoch wegen Migration der Pop-Logik T-004/T-005/T-014)
**Depends on:** T-177 (Generic-Storage-Planet), T-178 (Ship-Cargo), T-180 (Size-Multiplier-Config)
**Blocks:** —

## Beschreibung

Storage-Vision-Pivot: Pop ist ein **Storage-Item mit hohem Size-Multiplier**
(z.B. 10× Wasser pro Person). Lebt im Storage statt als separates Embeddable.

Bricht mit T-004 (Done): Population als Embeddable (total/assigned/cap) auf
Planet. Neue Model: Pop wandert in Planet-Storage + Ship-Cargo wie alle
anderen Items, mit eigenem Size-Beitrag.

## Open Questions

### Q1: Pop-Cap-Semantik

Heute T-004: `Population.cap` ist Maximum-Wohnraum, kommt von HUB/HQ.
Mit Storage-Item-Model:

- (a) **Pop-Cap entfällt** — Storage-Volume ist alleiniger Limiter. Mehr
  Volume = mehr Pop-möglich. Wohnraum-Buildings werden Volume-Provider.
- (b) **Pop-Cap bleibt** — Storage-Volume IST verfügbar (man könnte mehr
  Pop reinquetschen), aber Pop-Cap ist Lebens-Qualitäts-Grenze. Drüber =
  Pop-Mortality. Volume + Cap-Constraint beide.
- (c) **Pop-Cap = Wohnraum-Item, separate von Storage** — HUB/HQ liefern
  abstrakte "Wohneinheiten", Pop kann nicht ohne Wohneinheit ankommen.
  Pop selbst sitzt im Storage, aber Wohnraum-Cap ist Zusatz-Constraint.

### Q2: Assigned vs Free Pop

Heute T-004: `assigned` (in Buildings arbeitend) vs `free` (verfügbar).
Mit Storage-Model: bleibt die Trennung?

- (a) **Bleibt unverändert** — Pop hat Sub-State (assigned/free), Storage
  zählt alle Pop unabhängig vom State
- (b) **Nur Free-Pop ist Storage-Item** — Assigned-Pop ist "im Building
  gebunden" und zählt extra (gemischt — komplex)
- (c) **Assigned/Free wird Property der Pop-Item-Entry** — Storage-Item
  ist nicht einfach "100 Pop" sondern "100 Pop, davon 60 assigned an
  Iron-Mine, 40 free"

### Q3: Pop-Wachstum bei Storage-Full

Heute T-005: Logistic-Growth mit `cap` als Asymptote. Wenn Pop-Cap entfällt
(Q1=a), wie wächst Pop?

- (a) Asymptote = `Storage-Volume × Pop-Multi-Ratio` — Storage-Verfügbarkeit
  ist neue Asymptote
- (b) Asymptote bleibt fest (z.B. via HQ-Level), Storage ist nur
  Speicher-Constraint
- (c) Wachstum stoppt wenn Storage > 80% voll (Lebensraum-Stress)

### Q4: Pop-Sterben bei Storage-Full

Wenn Storage zu klein und Wachstum überfüllt:

- (a) **Pop-Mortality** wie heute (T-005 Hunger-Tod analog)
- (b) **Wachstum-Stopp ohne Mortality** — Pop-Growth = 0 bis Volume frei
  oder Wohnraum erweitert
- (c) **Auto-Emigration** — Pop wandert auf andere eigene Planeten (T-110
  Trade-Routes-Folge), wenn dort Platz

### Q5: Pop-Transport via Ship (T-014, T-015c)

T-014 Colony-Ship hat dedizierten Pop-Transport. T-015c (Draft) macht
Pop-Transfer Schiff↔Station. Mit Pop-als-Storage-Item:

- (a) **Generic Cargo-Load für Pop** — Pop wird via T-178 LoadCargo geladen,
  COLONY_SHIP hat größeren Cargo + Spezial-Effekt (Planet-Founding)
- (b) **Pop bleibt im Cargo, aber spezielle Constraints** — z.B. Pop kann
  nur in Schiffen mit "Life-Support"-Property überleben (T-012 ShipSupplyProcessor
  done); ohne Life-Support stirbt Pop im Flug
- (c) **COLONY_SHIP-Spezialfall** — Colony-Ship hat eigenen Pop-Slot
  zusätzlich zum Cargo (legacy-Pattern)

### Q6: Pop-Items im Cargo: einheitlich oder unterschieden?

- (a) **Eine Pop-Klasse** — generic POP-Item, alle gleich
- (b) **Pop-Tiers** (T-089 Luxury-Pop, T-097 Pop-Tier-Buildings) — Working/
  Middle/Upper haben eigene Items mit unterschiedlichem Size-Multi
- (c) **Pop + Crew-Subtypes** — Pop = Civilian; Crew/Captain (T-104) sind
  separate Items

### Q7: Migration T-004 / T-005

T-004 + T-005 sind Done. Was passiert mit `Population.total/assigned/cap`?

- (a) **Population-Embeddable entfernen** — Storage-Items machen Population-
  Embeddable obsolet
- (b) **Population-Embeddable wird Storage-Aggregator** — bleibt als
  Read-Convenience, intern liest sie aus Storage
- (c) **Beides koexistiert** — Embeddable bleibt (Domain-Logik), Storage
  bekommt nur "Pop-Volume-Reservation" als Mirror (komplex, gefährlich)

## Acceptance Criteria (Draft — final nach Q1-Q7)

- [ ] Pop-Storage-Integration auf Planet (Q1, Q2, Q7)
- [ ] Pop-Wachstum mit neuer Cap-Semantik (Q3)
- [ ] Pop-Mortality bei Volume-Cap-Überschreitung (Q4)
- [ ] Pop-Transport via Ship-Cargo (Q5)
- [ ] Pop-Klassen-Modell (Q6)
- [ ] Migration / T-004 + T-005 Refactor (Q7)
- [ ] Demo-Buff (T-082b/T-082e) angepasst — Pop = Storage-Item
- [ ] Tests: Pop-im-Storage, Wachstum, Mortality, Transport-Volume-Limit
- [ ] Doc `population.md` komplett refactored
- [ ] Doc `decisions.md` Eintrag: "Pop = Storage-Item, T-004 superseded/refactored"

## Out of Scope

- Pop-Tier-System (T-089/T-097)
- Crew-Classes (T-104)
- Auto-Emigration über Planeten (T-110)
- Trade-Routes mit Pop-Transfer

## Notes

- Sehr breit gestreutes Refactor, betrifft T-004/T-005/T-014/T-015c
- Sequenz strict: T-180 → T-177 → T-178 → T-179
- Memory-Check: Demo-Buff T-082e gibt aktuell Initial-Pop; muss kompatibel
  bleiben
