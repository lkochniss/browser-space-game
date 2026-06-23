# T-183: Station-Generic-Storage-Refactor

**Type:** Feature (Refactor)
**Epic:** Storage Vision
**Domain:** POI
**Blocked By:** T-180, T-177, T-023, T-023b
**Status:** Blocked (by T-180 + T-177 — Storage-Vision-Foundation)
**Effort:** M (~3-4h)
**Depends on:** T-180 (Volume-Config, Done), T-177 (Planet-Generic-Storage, Ready), T-023 (Station-Foundation, Done), T-023b (Station-Maintenance, Draft)
**Blocks:** —

## Beschreibung

Storage-Vision-Pivot auf **Raumstationen** angewandt (analog T-177 für Planet,
T-178 für Schiffe). Stations haben **generischen Volume-Storage**, keine
per-Resource-Kategorie. Alle Items (Resources, Erzeugnisse, Pop) wandern
in einen Pool, Volume-bezogen über Size-Multi (T-180).

### Heute (T-023 Done)

- `SpaceStation` hat `storageCapacity = 100_000` (fix, generic-typed Counter)
- Storage-Cargo-Transfer via T-015b funktioniert per-Resource
- Kein Volume-Konzept

### Mit T-183

- `SpaceStation::storageVolumeCapacity: int` (live-computed analog T-177)
- Items im Storage zählen über `T-180::getMultiForResource` × quantity
- Refactor T-023-Hardcoded-100k auf Volume-Model

## Open Questions

### Q1: Wo kommt Station-Volume her?

- (a) **Fix pro Station** — alle Stations gleich, z.B. 100_000 m³ statt
  100_000 Units. Einfach, kein Upgrade-Path.
- (b) **Variabel je Spawn-Tier** — T-175 Pirate-Station-Spawn könnte
  unterschiedliche Storage-Tiers spawnen (Small/Medium/Large Stations).
- (c) **Internes Station-Module-System** — Station hat "Modules" die
  upgegradet werden können (analog Planet-Buildings, aber Station-spezifisch).
  Storage-Module = ein Module-Typ unter mehreren (Refuel-Module, Defense-Module).
- (d) **Pop-basiert** — populationOnStation × Multi (z.B. 100 m³/Pop).
  Größere Crew = größeres Lager. Aber problematisch wenn Pop stirbt
  (T-023b ABANDONED).

### Q2: Upgrade-Path für Station-Storage

Mit Lost-Tech-Lore (T-174) — kann Player Station-Storage erweitern?

- (a) **Nein — Storage ist fix** wie sie gespawnt wurde. Lost-Tech bedeutet
  auch kein Upgrade-Path.
- (b) **Ja — via Station-Pop-Investment** (Crafting-mäßig: Pop + Resources
  bauen Storage-Module). Setzt Q1=(c) voraus.
- (c) **Ja — via spezielle Schiffs-Lieferung** (z.B. neuer Ship-Type
  "Station-Tender" liefert Module-Komponenten). Setzt Q1=(c) voraus.

### Q3: Migration T-023 Storage-Wert

Heute: `SpaceStation.storage = 100_000` (Resource-Quantity-Counter).

- (a) **1:1 Replace** — neue `storageVolumeCapacity = 100_000 m³`, Demo-Reset
  cleart Daten.
- (b) **Reskalieren** — 100_000 als generic-Quantity könnten zu viel/wenig
  Volume sein. Neue Werte je nach Q1-Entscheidung.

### Q4: Volume-Multi-Konsistenz für Station-Pop

T-179 (Blocked) refactored Pop als Storage-Item mit Multi=10. T-023b
(Draft) hat populationOnStation als Counter.

- (a) **Station-Pop = Storage-Item** wie auf Planet (konsistent mit T-179).
  populationOnStation wird Aggregator über Storage-Inhalt.
- (b) **Station-Pop separat behalten** als dedizierter Counter (wie heute).
  Stations sind anders als Planeten, Pop ist nicht "Cargo".
- (c) **Hybrid** — populationOnStation ist Quick-Read; intern lebt es im
  Storage. Maintenance-Tick (T-023b) liest Storage.

## Acceptance Criteria (Draft — final nach Q1-Q4)

- [ ] `SpaceStation::storageVolumeCapacity: int` (computed je nach Q1)
- [ ] `SpaceStation::getStorageVolumeUsed(): int` (Summe Items × Multi)
- [ ] T-023 Hardcoded 100_000 entfernt / refactored (Q3)
- [ ] T-015b Station-Cargo-Transfer Volume-aware (analog T-178)
- [ ] T-023b Maintenance-Tick Volume-aware (Q4)
- [ ] Upgrade-Path (falls Q2=b/c) implementiert
- [ ] Migration: Demo-Reset, Tests refactored
- [ ] Tests: Station-Volume-Cap, Multi-Item-Storage, Cargo-Transfer
- [ ] Doc `poi.md` Station-Storage-Sektion refactored

## Out of Scope

- Planet-Storage (T-177)
- Ship-Cargo (T-178)
- Pop-als-Storage-Item (T-179)
- Volume-Multi-Config (T-180, Done)
- Pirate-Spawn-Storage-Content (T-175)

## Notes

- Konsistent zur Storage-Vision-Familie T-177/T-178/T-179
- Sequenz: T-180 ✓ → T-177 → T-178 → T-179 → T-183 (oder T-183 parallel zu
  T-179, da Station unabhängig von Planet-Pop-Refactor)
- Lost-Tech-Lore (T-174) impliziert: keine neuen Station-Build-Mechaniken;
  Storage-Upgrade nur via existing Stations-Manipulation
