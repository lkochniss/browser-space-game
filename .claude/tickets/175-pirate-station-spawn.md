# T-175: Pirate-Owned-Station-Spawn (Galaxy-Bootstrap)

**Type:** Feature
**Epic:** NPC Factions
**Domain:** POI
**Blocked By:** T-073, T-049a, T-023
**Status:** Draft (Decisions pending)
**Effort:** M (~2-3h)
**Depends on:** T-073 (Faction-Foundation), T-049a (WorldFixture), T-023 (Station-Entity)
**Blocks:** T-174

## Beschreibung

Galaxy-Initial-Spawn verteilt existierende Stations als Lost-Tech-Erbe.
Einige davon sind initial **Pirate-owned** (T-073 Faction `pirate`) und
müssen via Combat-Capture (T-176) eingenommen werden. Andere starten als
ABANDONED und können via T-023b geclaimed werden.

Aktuell entstehen Stations nur über `BuildSpaceStationCommand` (deprecated
in T-174). Dieser Pivot füllt die Galaxy mit Stations zur Bootstrap-Zeit.

## Open Questions

### Q1: Anzahl Stations pro Galaxy / pro System

- (a) Fixe Zahl pro Galaxy (z.B. 5 Stations in 5 Systems)
- (b) Pro System maximal 1 (T-023 Constraint), mit Wahrscheinlichkeit (z.B.
  40% der Systems haben Station)
- (c) Pro Region/Cluster (T-118 Trade-Regions hat das Konzept)

### Q2: Owner-Distribution

- (a) Alle Pirate-owned bei Bootstrap
- (b) Mix: X% Pirate, Y% ABANDONED (sofort claimable)
- (c) Mix + andere Factions (T-073 hat Renegade/Xenos/MerchantGuild — könnten
  auch Stations besitzen)

### Q3: Initial-Storage-Content

- (a) Leer (claim/capture = nackte Station)
- (b) Fixed Loot (z.B. 50% Storage-Cap voll mit gemischten Resources)
- (c) Random-Loot (deterministisch via WorldFixture-Seed)

### Q4: Pop-Initial auf Pirate-Stations

- (a) Standard-200 (analog T-023 Build-Default)
- (b) Höher (z.B. 500-1000) — Pirate-Garrison
- (c) Variable je Threat-Level der Region

### Q5: Maintenance auch für Pirate-Stations?

- (a) Ja, dieselbe Logik (T-023b) — Pirate-Stations können auch ABANDONED
  werden wenn Resources ausgehen
- (b) Nein, Pirate-Stations haben "magic" Maintenance (NPC-Resources unendlich)
- (c) Ja, aber mit auto-Refill aus Pirate-Faction-Pool (T-073 hat Reputation,
  aber keinen Resource-Pool yet)

## Acceptance Criteria (Draft — final nach Q1-Q5)

- [ ] `WorldFixture` (T-049a) erweitert um Station-Spawn-Block
- [ ] Spawn-Anzahl + Verteilungs-Regel implementiert (Q1)
- [ ] Owner-Assignment-Logik (Q2)
- [ ] Storage-Initial-Content-Generator (Q3)
- [ ] Pop-Initial-Setup (Q4)
- [ ] Maintenance-Behavior für NPC-owned (Q5)
- [ ] T-023-Constraint (max 1 Station/System) eingehalten
- [ ] Tests: Galaxy-Bootstrap erzeugt N Pirate-Stations + M ABANDONED, korrekte
      Verteilung
- [ ] Doc: `poi.md` + ggf. `factions.md` Update

## Out of Scope

- Combat-Capture-Mechanik (T-176)
- Build-Path-Deprecation (T-174)
- Maintenance-Logik selbst (T-023b)
- Trade-Region-Integration (T-118)

## Notes

- WorldFixture aktuell deterministisch (Seed-based) — Spawn-Pattern muss
  reproduzierbar sein
- Dependency-Reihenfolge: T-175 vor T-174 (sonst leeres Universum nach T-174)
