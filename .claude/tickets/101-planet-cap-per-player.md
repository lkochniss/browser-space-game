# T-101 Planet-Cap pro Player

**Type:** Feature
**Status:** Draft
**Effort:** S
**Depends on:** T-014 (Kolonisationsschiff), T-150 (Bubble-Schutz)
**Blocks:** —

## Beschreibung
Anti-Steamroller: max Planeten pro Spieler. Garantiert dass kein Spieler ganze Galaxie besiedelt.

## Acceptance Criteria
- [ ] PlayerPlanetCap-Constant (default = 5)
- [ ] Erweiterbar via Forschung (Logistics-Branch +1 pro Tier, max 10)
- [ ] ColonizationCommand (T-014) checkt Cap → rejected wenn überschritten
- [ ] Cap berücksichtigt nicht-Heimat-Planeten (Heimat = T-081 + zusätzliche kolonisierte)
- [ ] Aufgabe-Mechanik: Spieler kann Planet `abandon`-en (Resources verloren, Slot frei)
- [ ] UI-Anzeige: Cap-Progress im Dashboard

## Affected Tests
- tests/Planet/Service/PlanetCapTest.php
- tests/Planet/Service/PlanetAbandonTest.php

## Fixtures Needed
No

## Notes
- Konfligiert nicht mit "Bubble-bis-2-Planet" (T-150): Bubble-Phase = max 2, danach bis Cap-5
- Tier-3-Forschung erweitert auf 10 → endgame-spielbar
