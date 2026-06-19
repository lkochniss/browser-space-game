# T-071 Power-Plants (Solar / Fusion / Antimaterie)

**Type:** Feature
**Status:** Draft
**Effort:** M
**Depends on:** T-065 (Energy-System)
**Blocks:** —

## Beschreibung
Dedizierte Power-Plants jenseits Hub-Reaktor. 3 Tiers mit unterschiedlichen Trade-offs.

- Solar-Array: günstig, planet-type-abhängig (gut auf TROPICAL/DESERT, schlecht auf ICE/VOLCANIC)
- Fusion-Reactor: stabil, hoher Build-Cost, braucht H2-Input pro Tick
- Antimaterie-Reactor: Tier-3, exorbitanter Build-Cost, höchste Power, braucht Antimaterie pro Tick

## Acceptance Criteria
- [ ] BuildingType::SOLAR_ARRAY, FUSION_REACTOR, ANTIMATTER_REACTOR
- [ ] Solar: 50 Power/lvl × Planet-Type-Multiplier (TROPICAL 1.5, DESERT 1.4, OCEAN 1.0, ICE 0.5, VOLCANIC 0.6, TOXIC 0.8, GAS_GIANT 0.7)
- [ ] Fusion: 200 Power/lvl, verbraucht 5 H2/h × lvl (kein H2 = 0 Output)
- [ ] Antimaterie: 800 Power/lvl, verbraucht 1 Antimaterie/h × lvl
- [ ] Build-Cost steigt exponentiell (Solar günstig, Fusion 10× teurer, Antimaterie 100× teurer)
- [ ] Power-Output skaliert linear mit Level
- [ ] Wenn Treibstoff fehlt: Reactor stoppt komplett, kein Power-Output

## Affected Tests
- tests/Building/Service/SolarArrayMultiplierTest.php (planet-type)
- tests/Building/Service/FusionReactorH2Test.php (consume + stop)

## Fixtures Needed
Yes — Test-Planets verschiedener Types, H2-Storage gefüllt/leer

## Notes
- Antimaterie-Reactor ist Endgame; Antimaterie-Resource erst T-115
- Fusion-Reactor als Mid-Game-Workhorse
