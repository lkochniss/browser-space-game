# T-105 Schiff-Maintenance (Treibstoff + Crew-Versorgung)

**Type:** Feature
**Epic:** Ships & Fleet
**Domain:** Ship
**Blocked By:** T-066, T-102, T-005, T-178, T-179
**Status:** Blocked (by T-178 + T-066 — Ship-Cargo statt eigener Fuel/Supply-Fields)
**Effort:** M
**Depends on:** T-066 (Treibstoff), T-102 (Schiff-Klassen), T-005 (Pop-Verbrauch), T-178 (Ship-Cargo-Universal), T-179 (Pop-Storage)
**Blocks:** —

## Beschreibung
Schiffe verbrauchen während Flug:
- Treibstoff (per Antriebs-Tech: H2 / Promethium / Antimaterie)
- Crew-Versorgung (W/F/O analog Pop)

Kein Hull-Wear (Decision: zu komplex). Permadeath bei Combat-Loss bleibt einzige Schiff-Verlust-Quelle.

## Acceptance Criteria
- [ ] Schiff-Entity bekommt `currentFuel: int`, `currentSupplies: { water, food, oxygen }`
- [ ] Pre-Flight-Check: vor Movement-Start Resources reservieren (vom Heimat-Planet abgebucht)
- [ ] Tick-Processor: pro Stunde Flugzeit → Verbrauch von Treibstoff + Supplies (proportional zu Crew-Anzahl)
- [ ] Treibstoff alle: Schiff stranded (status STRANDED), bleibt im aktuellen System
- [ ] Supplies alle: Crew-Mortality (10%/Tick bis Resupply oder Gesamt-Verlust)
- [ ] Refuel-Service: in eigenem System mit Stationsdocking → Resupply aus Planet-Storage
- [ ] Allianz-Station (T-093) bietet Refuel-Service auch für Mitglieder
- [ ] **KEIN** Hull-Wear, **KEIN** Wartungskosten im Stand (Schiff in Hangar = gratis)

## Affected Tests
- tests/Ship/Service/ShipFuelConsumptionTest.php
- tests/Ship/Service/ShipStrandingTest.php (kein Fuel = stranded)
- tests/Ship/Service/ShipResupplyTest.php

## Fixtures Needed
Yes — Test-Schiffe in verschiedenen Fuel/Supply-Zuständen

## Notes
- Permadeath nur bei Combat-Loss (T-103) — Stranding ist recoverable via Allianz-Rescue (T-153)
- Anti-Spam: hohe Treibstoff-Cost macht "Flotten-Spam" unwirtschaftlich
