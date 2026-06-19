# T-102 Schiff-Klassen-Foundation

**Type:** Feature
**Status:** Draft
**Effort:** XL
**Depends on:** T-011 (Raumwerft), T-012 (Schiff-Base), T-067 (Erzeugnis-Tree), T-104a (Crew-Foundation)
**Blocks:** T-103, T-105

## Beschreibung
5 Combat-Klassen × 3 Mark-Tiers + 4 Spezial-Klassen. Fix-Klassen (kein Modular-System). Hohe Build-Cost, wenige Schiffe pro Spieler.

Combat-Klassen × Tiers:
- Frigate Mk I/II/III (small, schnell)
- Destroyer Mk I/II/III (medium, ausbalanciert)
- Cruiser Mk I/II/III (large, hohe Damage)
- Battleship Mk I/II/III (capital, hohe HP+Damage)
- Carrier Mk I/II/III (carrier mit Fighter-Squadrons)

Spezial-Klassen:
- Salvage-Ship (T-016 erweitert)
- Transport-Ship (T-015 erweitert)
- Probe-Carrier (T-013 erweitert)
- Colonization-Ship (T-014 erweitert)

## Acceptance Criteria
- [ ] ShipClass-Enum mit 5×3 + 4 = 19 Werten
- [ ] ShipBlueprint-VO (class, tier, hp, damage, shieldCapacity, fuelType, fuelPerHour, popCrewRequirement, captainRequired-bool, buildCost-Map)
- [ ] BlueprintRegistry mit allen Stats hardcoded
- [ ] Beispiel Cruiser Mk I: 5000 Steel, 2000 Iron-Bar, 200 Pop, 1 Captain, 3-4 Tage Bauzeit
- [ ] Mark-Upgrade: Mk II = Mk I × 1.5 Stats × 2x Cost; Mk III analog
- [ ] Raumwerft (T-011) muss Mindest-Tier haben pro Klasse (Frigate Lvl 1, Destroyer Lvl 3, Cruiser Lvl 5, Battleship Lvl 8, Carrier Lvl 10)
- [ ] Mark-Tier-Gate: Mk II/III braucht Forschung (Schiffbau-Branch)
- [ ] Build-Time skaliert mit Cost (Frigate Mk I 6h, Battleship Mk III ~2 Wochen)

## Affected Tests
- tests/Ship/Model/ShipBlueprintTest.php
- tests/Ship/Service/ShipBuildCommandTest.php (cost-validation, tier-gate)

## Fixtures Needed
Yes — Test-Player mit Raumwerft + Resources

## Notes
- "Wenige Schiffe teuer"-Decision: Build-Cost so hoch dass Solo-Spieler max 5-10 Schiffe gleichzeitig
- Captain-Requirement: ohne Captain kein Combat-Schiff (T-104a) — eigene Engpass-Quelle
- Permadeath bei Loss (T-105) — keine billige Replacement-Spam-Strategie
