# T-095 Auto-Production-Routing (Folge-Ticket zu T-110 Trade-Routes)

**Type:** Feature
**Status:** Draft
**Effort:** M
**Depends on:** T-110 (Trade-Routes)
**Blocks:** —

## Beschreibung
Folge-Erweiterung von T-110: Auto-Trigger basierend auf Storage-Zustand. Wenn Storage X auf Planet A unter Threshold fällt, automatisch von Planet B beziehen (vorausgesetzt Trade-Route konfiguriert + Schiff verfügbar).

## Acceptance Criteria
- [ ] AutoRoutingRule-Entity (sourcePlanet, targetPlanet, resourceType, minThreshold, qty)
- [ ] Tick-Processor: pro Tick prüft alle aktive Rules → triggert Trade-Route wenn Threshold unterschritten
- [ ] Anti-Spam: Rule-Cooldown 1h zwischen Triggers
- [ ] Schiff-Allocation: nutzt nächst verfügbares Transport-Schiff (sonst: Rule-Pending mit Notification)
- [ ] UI: Rule-Konfiguration im Planet-Dashboard
- [ ] Pause/Resume per Rule
- [ ] Audit-Log: jeder Auto-Trigger geloggt

## Affected Tests
- tests/Trade/Service/AutoRoutingTriggerTest.php
- tests/Trade/Service/AutoRoutingShipAllocationTest.php

## Fixtures Needed
Yes — Multi-Planet-Setup mit Rules

## Notes
- Spätere Erweiterung: Chain-Routing (A → B → C falls Mehrweg-Transport)
- Konsistent mit "wenige Schiffe"-Design: Auto-Routing rationiert verfügbare Schiffe
