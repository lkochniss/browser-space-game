# T-066 Treibstoff-Resource (Promethium / H2)

**Type:** Feature
**Status:** Blocked (by T-177)
**Effort:** M
**Depends on:** T-002 (Endliche Rohstoffe), T-003 (Erzeugnis Iron-Bar)
**Blocks:** T-105 (Schiff-Maintenance), T-012+ (Schiff-Bau)

## Beschreibung
Schiffe brauchen Treibstoff für Antrieb. Antriebs-Tech (T-026) bestimmt welchen Treibstoff:
- Wasserstoff-Antrieb → H2 (raffiniert aus Wasser)
- Promethium-Antrieb → Promethium (Erz-Deposit, neuer Type)
- Antimaterie-Antrieb → Antimaterie (Tier-3, T-115)

Schiff verbraucht Treibstoff während Flug. Ohne Treibstoff = stranded.

## Acceptance Criteria
- [ ] `ResourceType::H2`, `ResourceType::PROMETHIUM` als neue Werte
- [ ] `ResourceCategory::FUEL` als neue Kategorie (neben RAW, REFINED, RENEWABLE)
- [ ] Promethium-Deposit auf bestimmten Planet-Types (DESERT, VOLCANIC, ICE)
- [ ] H2-Refinery (Building) wandelt Wasser → H2 (Ratio 5:1, langsam)
- [ ] Promethium-Mine (Building) extrahiert Promethium-Deposit
- [ ] Schiff-VOs bekommen `fuelType: ResourceType` und `fuelPerHour: int`
- [ ] Storage akzeptiert Fuel-Resources

## Affected Tests
- tests/Resource/Model/FuelResourceTest.php
- tests/Building/Service/H2RefineryTest.php (production-tick)
- tests/Building/Service/PromethiumMineTest.php

## Fixtures Needed
Yes — Promethium-Deposits in Test-Planets, H2-Refinery + Promethium-Mine als BuildingTypes

## Notes
- Antimaterie kommt erst mit T-115 Tier-3 Resources
- Treibstoff-Verbrauch eigentlich erst in T-105 implementiert; hier nur Resource + Production-Path

## Resolved Decisions

- **ResourceCategory::FUEL** verworfen → `isFuel()`-Flag-Pattern (Q1=b).
  H2 bleibt REFINED, Promethium bleibt FINITE. Fuel ist Verwendungs-
  Property, keine Herkunfts-Kategorie.
- **Storage-Q (geplant Q2) obsolet** durch Storage-Vision-Pivot:
  Generic-Volume-Storage-Refactor in T-177/T-178/T-179/T-180. Fuel-Resources
  nutzen generic Planet-Storage + Ship-Cargo wie alle anderen Items.

## Dependency-Update

- **Depends on:** T-002, T-003, T-180 (Volume-Config — Foundation für
  Storage-Verhalten), T-177 (Generic-Planet-Storage)
- **Blocks unverändert:** T-105 (Schiff-Maintenance), T-012+ (Schiff-Bau)
