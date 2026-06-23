# T-182: Remove UNIVERSITY Building (Wort-Mix-Up; Lab ist die einzige Forschungs-Einrichtung)

**Type:** TechDebt
**Status:** Done
**Effort:** S (~1-1.5h)
**Depends on:** —
**Blocks:** —

## Beschreibung

T-070 (Done) hat `BuildingType::UNIVERSITY` als eigenes strikt-unique Building
eingeführt — mit Cost/Duration/Slot=2 und geplantem RP-Output-Multiplier
(T-070b University-AC).

User-Klarstellung: **University == Lab**. Es soll nur EINE Forschungs-
Einrichtung geben (RESEARCH_LAB aus T-025). UNIVERSITY war ein Wort-Mix-Up
und ist im Code als toter / doppelter BuildingType.

Cleanup: UNIVERSITY entfernen, alle Referenzen säubern, T-070b University-
Section streichen.

## Acceptance Criteria

### Code-Cleanup

- [x] `BuildingType::UNIVERSITY` Enum-Entry entfernt
- [x] `BuildingCostConfig` UNIVERSITY-Eintrag entfernt
- [x] `BuildingDurationConfig` UNIVERSITY-Eintrag entfernt
- [x] Slot-Size match-branch (UNIVERSITY=2) entfernt
- [x] `BuildingUniquenessTest` UNIVERSITY-Case entfernt
- [x] `HospitalPopCapTest` UNIVERSITY-Assertion entfernt
- [x] Smoke-Test grün: 640/640

### Ticket-Updates

- [x] **T-070 (Done)** — AC-Listen ohne UNIVERSITY, Note "T-182: revoked"
- [x] **T-070b (Draft)** — "University RP-Output-Multiplier" Section
      durchgestrichen, Q1 als obsolet markiert
- [x] **T-097a (Done)** — "Krankenhaus, Universität, Kultur" → "Krankenhaus, Kultur, Tempel"
- [x] **T-089 (Draft)** — "RP-Output via University (T-070)" → "RP-Output via RESEARCH_LAB (T-025)"

### Doku-Updates

- [x] `buildings.md` UNIVERSITY-Zeile entfernt + T-182-Hinweis
- [x] `decisions.md` Eintrag für T-182-Revocation hinzugefügt

## Out of Scope

- Migration von existing UNIVERSITY-Buildings in Demo-State — Reset cleart
  alles, keine Migration nötig
- T-070 Hospital/Cultural/Temple bleiben unverändert
- T-025/T-069 Lab-Funktionen bleiben unberührt

## Notes

- Demo-State: bei nächstem Reset verschwindet UNIVERSITY automatisch
- Falls Player im Code-Test versucht UNIVERSITY zu bauen → fail-fast bei
  Enum-Lookup
- Reservierter Slot-Punkt 2 für UNIVERSITY = freier Planet-Slot für andere
  Buildings nach Refactor
