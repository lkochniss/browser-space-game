# T-163 Strategy-Forecast / Dashboard

**Type:** Feature
**Status:** Draft
**Effort:** L
**Depends on:** T-045 (Game-Dashboard)
**Blocks:** —

## Beschreibung
Strategie-Cockpit für Spieler. Aggregiert kritische KPIs + Forecasts pro Planet/Account-weit.

Komponenten:
- Resource-Forecast: "Bei aktueller Production: Iron-Bar reicht 12h, Steel reicht 3 Tage"
- Build-ETAs: alle aktiven Bauprojekte in einer Liste mit Progress + ETA
- ROI-Calculator: für anstehendes Building/Upgrade — Cost vs Output-Increase, Break-Even-Time
- Forschung-Progress: aktive Research mit ETA
- Galaxy-Threat-Indicator: Pirate-Aktivität in Nachbar-Systems

## Acceptance Criteria
- [ ] StrategyDashboardController + DTO-API
- [ ] Forecast-Service: aggregate aller Production/Consumption für jede Resource
- [ ] ROI-Service: Cost-vs-Output für Building-Upgrades
- [ ] Multi-Planet-Aggregate-View
- [ ] Customizable Widgets (welche Sektionen sichtbar)
- [ ] Auto-Refresh per Stimulus (5min Intervall)
- [ ] Mobile-Layout

## Affected Tests
- tests/Dashboard/Service/ResourceForecastTest.php
- tests/Dashboard/Service/RoiCalculatorTest.php

## Fixtures Needed
Yes — Multi-Planet-Player

## Notes
- Wertvoll für Long-Time-Spieler — Min-Maxing-Tool ohne P2W
- ROI-Calculator besonders wichtig bei T-098 Specialist-Tracks (welche Building-Investition lohnt mit meinem Track)
