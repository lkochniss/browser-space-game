# T-167: Cleanup Loose Ends — Status-Sync, Stale Stubs, Folge-Tickets

**Type:** TechDebt
**Status:** Done
**Severity:** Low
**Effort:** S (~1h)
**Risk if ignored:** Akkumulierende technische Inkonsistenz, verwirrende Stale-Entries, fehlende Verfolg-Verbindungen für deferred-Out-of-Scope-Punkte aus jüngsten Tickets (T-026/T-064/T-094).

## Beschreibung

Audit-Liste der Loose Ends nach den letzten ~15 Tickets. Pragmatische Aufräum-
Aktion: Status-Inkonsistenzen fixen, dead Stubs entfernen, deferred-Punkte als
expliziten Folge-Ticket-Drafts anlegen (statt vergraben in Out-of-Scope-Sektionen).

## Audit-Items

### A. Status-Sync zwischen Ticket-Files und README (2)

| Ticket | File-Status | README-Status | Action |
|--------|-------------|---------------|--------|
| T-003 erzeugnis-eisenbarren | Open | Done | File auf Done setzen |
| T-063 planet-bonus-system | In Progress | Done | File auf Done setzen |

Reicht: 2× `Status:`-Zeile aktualisieren. Keine Code-Änderung.

### B. Stale ResearchTree-Stub-Nodes (2)

`mining_efficiency_1` (3 Levels, keine Wirkung) und `ftl_tier_1` (1 Level, keine
Wirkung) sind T-025-Foundation-Stubs, die heute keine Funktion haben:

- `mining_efficiency_1`: Hook war für T-127 Mining-Industry-Branch geplant. T-127
  ist Draft mit eigenen Nodes — Stub vermutlich nicht das Bindeglied
- `ftl_tier_1`: Hook war für T-026. T-026 ist Done und nutzt eigene 7 Nodes
  (`propulsion_*` + `ftl_hyperdrive/warp/jumpdrive`)

**Decision (zu klären):**
- (a) Entfernen, Tests anpassen (`ResearchTreeTest::test_stub_nodes_registered`)
- (b) Behalten als Future-Hook + Doc/Comment vermerken

**Empfehlung:** (a) — Tree wird sonst mit Toten-Pfaden zugemüllt. Tests werden
schmäler.

### C. Wormhole.requiredTechSlug — Field gesetzt, nicht validiert

- `Wormhole::requiredTechSlug` Field existiert + wird in ClaimStartPlanet +
  WorldFixture + Demo gesetzt (heute alle = `'ftl_warp'`)
- **Aber:** wird nirgends gelesen / validiert für Travel
- T-026 hat globalen Inter-System-Travel-Lock auf `ftl_hyperdrug L1+`, aber
  Wormhole-spezifischer-Lock (z.B. nur via `ftl_warp` möglich) fehlt

**Action:** kein heutiger Cleanup — eigenes Folge-Ticket anlegen
**T-026b: Wormhole-spezifischer Travel-Tech-Lock** (Draft).

### D. T-024 Raumschlacht-Resolution

Status: `Open`. Ticket selbst dokumentiert: "abgelöst durch T-103".

**Action:** Status auf `Superseded` setzen (analog T-025b-Pattern).

### E. Folge-Tickets explizit anlegen für Out-of-Scope-Defers

Aktuell sind diese Punkte nur in Notes/Out-of-Scope-Sektionen vergraben:

| Quelle | Defer-Punkt | Neuer Folge-Ticket |
|--------|-------------|--------------------|
| T-026 | PropulsionType-Enum + Speed/Range pro Antrieb | **T-026c PropulsionType-Field** (Draft) |
| T-026 | Treibstoff-Mechanik | (existiert bereits als T-066 Draft) |
| T-064 | CONSTRUCTION_HUB Building | **T-064b Construction-Hub-Building** (Draft) |
| T-094 | Cancel + Refund-Mechanik | **T-094b Build-Queue-Cancel-Refund** (Draft) |
| T-094 | Hub-Slot-Bonus + Logistics-Forschungs-Slot-Bonus | **T-094c Build-Queue-Slot-Erweiterung** (Draft) |
| T-015b | Pop-Transfer Station ↔ Ship | **T-015c Station-Pop-Transfer** (Draft, blocked von T-023b) |
| T-015b | Owner-Restriction Station-Cargo | (in T-093 Allianz-Stationen vermerkt) |

5 neue Drafts. Jedes 3-5 Zeilen, klar Scope umrissen.

### F. T-082b ensureDemoGalaxyContent — Refactor-Kandidat

`InteractiveDemoCommand::ensureDemoGalaxyContent()` mutiert Galaxy direkt im
Demo-Command (Persist + flush). Funktioniert, ist aber Production-Code in
Demo-Domäne mit Mutation — etwas unsauber.

**Action:** Refactor optional — in `DemoGalaxyContentService` extrahieren.

**Empfehlung:** Skip — Foundation-Demo ist OK so; Refactor wäre Goldplattieren.
Später wenn Test-Coverage es ergibt.

## Acceptance Criteria

- [ ] **A:** T-003 + T-063 File-Status auf Done aktualisiert
- [ ] **B:** mining_efficiency_1 + ftl_tier_1 aus ResearchTree entfernt;
      ResearchTreeTest angepasst (count + remove `has`-Asserts)
- [ ] **C:** T-026b-Draft "Wormhole-spezifischer Tech-Lock" angelegt
- [ ] **D:** T-024 auf Status "Superseded by T-103"
- [ ] **E:** 5 neue Drafts angelegt (T-026c, T-064b, T-094b, T-094c, T-015c)
- [ ] **F:** Skip (siehe Empfehlung)
- [ ] Suite grün
- [ ] README-Index für alle neuen/geänderten Tickets aktualisiert
- [ ] Doc-Update: research.md (Stub-Sektion entfernen)

## Files

**Geändert:**
- `src/Research/Service/ResearchTree.php` (2 Stub-Nodes entfernen)
- `tests/Research/Service/ResearchTreeTest.php` (Asserts updaten)
- `.claude/tickets/003-erzeugnis-eisenbarren.md` + `063-planet-bonus-system.md` + `024-raumschlacht.md` (Status)
- `.claude/tickets/README.md` (alle Updates)
- `.claude/docs/research.md` (Stub-Sektion entfernen)

**Neu (Drafts):**
- `.claude/tickets/026b-wormhole-tech-validation.md`
- `.claude/tickets/026c-propulsion-type-field.md`
- `.claude/tickets/064b-construction-hub-building.md`
- `.claude/tickets/094b-build-queue-cancel-refund.md`
- `.claude/tickets/094c-build-queue-slot-extensions.md`
- `.claude/tickets/015c-station-pop-transfer.md`

## Out of Scope

- Wormhole-Tech-Validation **selbst** (das ist T-026b)
- PropulsionType-Implementation (T-026c)
- Cancel/Refund-Implementation (T-094b)
- Refactor `ensureDemoGalaxyContent` — Goldplattieren

## Notes

- Pure Aufräum-Ticket — keine Mechanik-Änderung am Spiel
- ResearchTreeTest wird kleiner (count: 16 → 14)
- Folge-Tickets bekommen jeweils eine `Depends on:`-Zeile + 2-3 AC-Bullets als Draft-Skelett
