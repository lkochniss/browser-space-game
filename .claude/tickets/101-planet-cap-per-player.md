# T-101 Planet-Cap pro Player

**Type:** Feature
**Status:** Done (Foundation; Abandon-Mechanik in T-101b split)
**Effort:** S
**Depends on:** T-014 (Kolonisationsschiff, Done), T-094d (logistics_1, Done)
**Blocks:** —

## Beschreibung
Anti-Steamroller: max Planeten pro Spieler. Garantiert dass kein Spieler ganze Galaxie besiedelt.

## Acceptance Criteria
- [x] `PlayerPlanetCapCalculator` mit BASE_CAP=5, HARD_CAP=10
- [x] Erweiterbar via Forschung: +1 pro `logistics_1`-Level (max 3 mit existing
      Node), HARD_CAP=10 lässt Raum für T-136 Logistics-Branch-Erweiterung
- [x] `ColonizePlanetCommandService` checkt Cap → `PlanetCapReachedException`
- [x] Demo-CLI Status zeigt `Planets: N/M`
- [x] Tests: Calculator-Unit + Colonize-IT (Cap-Violation)
- [x] Doc `planets.md` Cap-Sektion + `decisions.md` Entry

## Out of Scope (in T-101b verschoben)

- **Abandon-Mechanik** (Player gibt Planet auf → Slot frei) — T-101b Draft mit
  Open Questions (Buildings/Cooldown/Heimat-Schutz/UI)
- **UI-Dashboard** — kein Web-Layer vorhanden (T-034 Open); Demo-CLI-Anzeige
  reicht für Foundation
- **Bubble-Interaktion** (T-150 noch Draft) — Cap gilt immer ab Player-Start

## Notes
- Konfligiert nicht mit "Bubble-bis-2-Planet" (T-150 Draft): Bubble-Phase = max 2, danach bis Cap-5
- Tier-3-Forschung (T-136) erweitert HARD_CAP-Range bis 10
