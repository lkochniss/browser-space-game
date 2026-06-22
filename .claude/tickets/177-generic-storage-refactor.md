# T-177: Generic-Storage-Refactor (Planet)

**Type:** Feature (Refactor)
**Status:** Blocked (by T-180)
**Effort:** L (~6-8h, hoch wegen Migration + Test-Refactor)
**Depends on:** T-180 (Size-Multiplier-Config — Foundation)
**Blocks:** T-061 (Done-Status muss revidiert werden), T-066 (Fuel-Storage), T-178 (Ship-Cargo), T-179 (Pop-Storage)

## Beschreibung

Storage-Vision-Pivot: Statt 6 dedizierter Storage-Buildings pro Resource-
Kategorie hat jeder Planet **einen einheitlichen Storage** mit Volumen-Cap.
Alle Items (Resources, Erzeugnisse, Pop) belegen Volumen abhängig von ihrem
**Size-Multiplier** (T-180).

T-061 (Done) wird damit refactored — keine Category-Buckets mehr.

### Lore / Intuition

- Wasser braucht mehr Lager-Volumen als Wasserstoff (Aggregatzustand)
- Erze sind schwer und voluminös pro Einheit
- Pop ist nicht stapelbar → massiver Size-Multi
- Storage ist Lager-Halle, kein Resource-Lock-Pattern

## Open Questions

### Q1: Migration bestehender Storage-Buildings (T-061)

T-061 hat 6 Storage-Buildings (per Kategorie). Was passiert mit ihnen?

- (a) **Konsolidieren in 1 generischen "Warehouse"-Building** — die anderen 5 werden gelöscht; Player-State: bei Refactor wird höchstes Storage-Building pro Planet umgewandelt
- (b) **Alle 6 bleiben, alle erhöhen generic Volume** — semantisch verschieden gestaffelte Warehouse-Varianten (z.B. WAREHOUSE/SILO/REFRIGERATED_DEPOT/CRYO_TANK/HEAVY_DEPOT/CARGO_TERMINAL = Refactored zu 6 "Tiers" desselben Building-Trees)
- (c) **Reduce auf 2-3 Storage-Buildings** — z.B. STANDARD_WAREHOUSE + HEAVY_DEPOT + CRYO_VAULT (für besondere Items wie Pop oder Antimatter mit Spezial-Multi)
- (d) **Komplett neu — Storage-Building-Familie via T-097 (Pop-Tier-Buildings) / T-100 (Trade-Hub-Buildings)** überarbeitet, T-061-Buildings gelöscht

### Q2: Base-Storage-Volume ohne Storage-Building

- (a) Fix-Konstante (z.B. 1000 Volume-Units) — minimal Bootstrap-Lager
- (b) Skaliert mit Planet-Size (T-008 5 Sizes — Tiny → Huge)
- (c) Skaliert mit HQ-Level (T-172) — HQ als Logistik-Zentrum
- (d) Kombination (a)+(b) — Base + Planet-Size-Multi

### Q3: Overflow-Verhalten bei Volume-Cap

Heute T-061: Production stoppt bei Cap (Cap-Stop-Production). Mit generischem
Volume:

- (a) **Volume-Cap-Stop für ALLE Production** — wenn Storage voll, alles
  stoppt (Mines, Refineries, Renewable). Player muss aktiv Volume freimachen.
- (b) **Prio-System** — wichtige Resources verdrängen unwichtige bei Cap
  (z.B. Pop-Survival > Iron-Bar-Production). Komplex.
- (c) **Spillover-Loss** — Production läuft weiter, Overflow geht verloren.
  Ist gefährlicher aber realistischer (Pop sirbt nicht wegen "Storage voll
  für Wasser").
- (d) **Auto-Eject mit Priorität** — bei Cap werden niedrig-prio Items
  auto-discarded (T-110 Trade-Routes als Folge: Auto-Export).

### Q4: Storage-Read/Write-API

Heute T-061: `Planet::getResource(Type)` + Collection-Pattern. Mit Volume-
Model:

- (a) **Volume-aware-Operations** — `canAddItem(item, qty)` prüft Volume,
  `addItem(item, qty)` wirft Exception bei Overflow
- (b) **Backward-Compatible API** — existing `addResource()` ruft intern
  Volume-Check; Migration der Aufrufer
- (c) **Neue StorageService-Abstraction** — alle Storage-Operations gehen
  über `StorageService` (analog GameState-Wrapper), Volume-Logic gekapselt

### Q5: Display / UI-Logic

Heute: pro Resource eine eigene Anzeige mit `current/cap`. Mit generischem
Volume:

- (a) Volume-Display als Gesamtwert + Item-Liste mit Volume-Beitrag jedes
  Items (z.B. "Storage: 850/1000 — Iron-Ore: 400 (200 vol), Water: 100 (100 vol)")
- (b) Resource-Liste mit individueller Quantity (wie heute) + zusätzlich
  einer Gesamt-Volume-Bar
- (c) Beides — kompakt vs. detailliert via UI-Toggle

## Acceptance Criteria (Draft — final nach Q1-Q5)

- [ ] `Planet::storageVolumeCapacity: int` (live-computed analog T-061)
- [ ] `Planet::getStorageVolumeUsed(): int` (Summe aller Items × Size-Multi)
- [ ] Storage-Buildings (T-061-Refactor) erhöhen `storageVolumeCapacity`
- [ ] Volume-Cap-Validation bei `addResource`/`addItem`
- [ ] Overflow-Behavior implementiert (Q3)
- [ ] Storage-Building-Familie konsolidiert (Q1)
- [ ] Base-Volume-Bootstrap definiert (Q2)
- [ ] Migration: bestehende Demo-State + Tests refactored
- [ ] Tests: Volume-Cap, Overflow, Multi-Item-Storage
- [ ] Doc `buildings.md` Storage-Sektion refactored
- [ ] Doc `resources.md` Storage-Behavior aktualisiert
- [ ] Doc `decisions.md` Eintrag: "Storage = Generic Volume-Based, T-061 superseded"

## Out of Scope

- Ship-Cargo-Refactor (T-178)
- Pop-als-Storage-Item (T-179)
- Size-Multiplier-Tabelle (T-180 — Foundation)
- Trade-Routes / Auto-Export (T-110)

## Notes

- T-061 wird damit **Superseded** (analog T-024 → T-103 Pattern)
- Reset-fähigkeit der Demo-DB hilft — kein hartes Daten-Migration nötig
- Refactor-Reihenfolge: T-180 (Config) → T-177 (Planet) → T-178 (Ship) → T-179 (Pop)
