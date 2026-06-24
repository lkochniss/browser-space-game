# T-102 Schiff-Klassen-Foundation

**Type:** Feature
**Epic:** Combat & Battle
**Domain:** Ship
**Blocked By:** T-011, T-012, T-067, T-104a
**Status:** Done
**Effort:** XL
**Depends on:** T-011 (Done), T-012 (Done), T-067 (Done), T-104a (Ready)
**Blocks:** T-103, T-105

## Beschreibung

5 Combat-Klassen × 3 Mark-Tiers + 4 Spezial-Klassen (Single-Tier). Fix-Klassen,
kein Modular-System. Hohe Build-Cost, wenige Schiffe pro Spieler.

**Combat-Klassen × Tiers:**
- Frigate Mk I/II/III (small, schnell)
- Destroyer Mk I/II/III (medium, ausbalanciert)
- Cruiser Mk I/II/III (large, hohe Damage)
- Battleship Mk I/II/III (capital, hohe HP+Damage)
- Carrier Mk I/II/III (carrier-damage absorbiert Fighter-Squadrons als Stats)

**Spezial-Klassen** (Single-Tier, erweitern existing T-014/T-015/T-016/T-013):
- Salvage-Ship
- Transport (TRANSPORT_SMALL/MEDIUM/LARGE existieren bereits via T-015)
- Probe-Carrier
- Colonization-Ship

## Resolved Decisions

- **Q1 Tier-Scaling:** Mk II = Mk I × 1.5 Stats × **3× Cost**. Mk III =
  Mk II × 1.5 Stats × 3× Cost (Cumulative: Mk III = ~2.25× Mk I Stats, ~9× Cost).
  Steile Cost-Curve verhindert Mk-III-Spam.
- **Q2 Captain-Required:** Alle 5 Combat-Klassen brauchen 1 Captain (T-104a).
  Combat-Build ohne Captain = `MissingCaptainException`. Spezial-Klassen frei.
- **Q3 Escape-Pod-Survival-Chance** (für T-104a Captain-Permadeath):
  | Klasse | Pod-Chance |
  |--------|------------|
  | Frigate | 30% |
  | Destroyer | 50% |
  | Cruiser | 65% |
  | Battleship | 80% |
  | Carrier | 70% |
- **Q4 Carrier-Squadrons:** Stats-absorbed. Carrier hat höheren Damage-Stat
  (Squadrons als Sub-Stat). KEINE separate Fighter-Entity. Squadron-Mechanik
  bleibt Out-of-Scope; ggf. Folge-Ticket falls UX/Lore das verlangt.
- **Q5 Mark-Tier-Research:** Mark-spezifische Research-Nodes via T-128
  Schiffbau-Tech-Branch:
  - `frigate_mk2`, `frigate_mk3`
  - `destroyer_mk2`, `destroyer_mk3`
  - `cruiser_mk2`, `cruiser_mk3`
  - `battleship_mk2`, `battleship_mk3`
  - `carrier_mk2`, `carrier_mk3`
  → 10 neue Research-Nodes (in T-128 angelegt, T-102 referenziert nur Slugs).
- **Q6 Spezial-Klassen:** Single-Tier. Existing T-013/T-014/T-015/T-016
  Implementation bleibt. T-102 erweitert NICHT die Spezial-Klassen.

## Acceptance Criteria

### Enum + Blueprint-Registry

- [x] `ShipClass` Enum mit 15 Combat-Werten (5 Klassen × 3 Tiers):
      `FRIGATE_MK1`, `FRIGATE_MK2`, ..., `CARRIER_MK3`
- [x] `ShipBlueprint` readonly-VO mit hp/damage/shieldCapacity/populationCost/
      buildDurationSeconds/buildCost/escapePodChance/captainRequired.
      _Fuel-Felder deferred — fließen via T-066 Fuel-Mechanik nach_
- [x] `ShipBlueprintRegistry` Service mit allen 15 Stats hardcoded
- [x] Per Klasse Mk II = Mk I × 1.5 Stats × 3 Cost (Q1); Mk III = Mk II × 1.5 × 3

### Base-Stats-Tabelle (Mk I)

| Klasse | HP | Damage | Schild | Pop | Build (h) | Cost |
|--------|-----|--------|--------|-----|-----------|------|
| Frigate Mk I | 1000 | 200 | 300 | 30 | 6 | 500 Steel + 200 IB |
| Destroyer Mk I | 2500 | 400 | 800 | 60 | 12 | 1500 Steel + 500 IB + 50 Chip |
| Cruiser Mk I | 5000 | 800 | 1500 | 120 | 36 | 4000 Steel + 1500 IB + 200 Chip + 50 Composite |
| Battleship Mk I | 12000 | 1500 | 3000 | 250 | 72 | 10000 Steel + 3000 IB + 500 Chip + 200 Composite + 50 Hull-Plate |
| Carrier Mk I | 8000 | 1800 | 1800 | 180 | 60 | 7000 Steel + 2500 IB + 400 Chip + 150 Composite + 30 Hull-Plate |

(Mk II/III via Multiplier-Formel aus Q1)

### Shipyard-Min-Level pro Klasse

- [x] Frigate: SHIPYARD ≥ L1
- [x] Destroyer: ≥ L3
- [x] Cruiser: ≥ L5
- [x] Battleship: ≥ L8
- [x] Carrier: ≥ L10
- [x] `MissingShipyardLevelException` wenn unterschritten

### Build-Service

- [x] `BuildShipCommand` erweitert um `shipClass: ?ShipClass` (Default null)
- [x] `BuildShipCommandService` validiert:
  - Shipyard-Min-Level (s.o.)
  - Mark-Tier-Research (Q5): Mk II/III braucht `<family>_mk<tier>` Lvl 1
  - Captain-Available (Q2): Combat-Klassen brauchen IDLE-Captain
  - Resource + Pop-Cost via Blueprint
- [x] `MissingCaptainException` bei Build ohne verfügbaren Captain
- [x] 10 neue Research-Nodes (`<family>_mk2/mk3` × 5 Familien) in ResearchTree

### Integration mit existing Ship-System

- [x] `Ship::shipClass: ?ShipClass` Field (nullable, Doctrine ORM column)
- [x] `Ship::isCombatShip()` + `getEscapePodSurvivalChance()` prefer ShipClass
      über ShipType-Stub
- [ ] `Ship::getEffectiveDamage/Hp/Shield()` lesen Blueprint — _deferred: kommt
      in T-103 Battle-Resolver der die Stats konkret nutzt; Foundation legt nur
      Blueprint-Registry an_
- [ ] Captain-Stats-Boost (T-104a +3%/Lvl) stackt multiplikativ — _Wiring in
      T-103 Battle-Resolver (effective-stats-Berechnung dort)_
- [x] Migration `Version20260624000001` für ships.ship_class Spalte

### Tests

- [x] `ShipBlueprintRegistryTest` (7): alle 15 Klassen registered, Stats
      korrekt skaliert, Escape-Pod-Chance je Familie
- [x] `BuildCombatShipTest` (6): Shipyard-Level-Gate, Captain-Gate,
      Research-Gate, Build-Success, ShipType bleibt GENERIC

### Docs

- [x] `ships.md` erweitert: ShipClass-Tabelle + Tier-System + Escape-Pod-Tabelle
- [x] `decisions.md` Eintrag T-102

## Out of Scope

- Squadron-Mechanik (Q4 superseded — Stats-only Carrier)
- Modular-Ship-Customization (rejected Decision)
- Defense-Building-Munition (T-088 Folge)
- Battle-Resolution selbst (T-103)
- Schiff-Maintenance (T-105)

## Fixtures Needed

Yes — `ShipFixture` mit Test-Schiff je Klasse, Test-Player mit High-Level Shipyard
+ Captain-Pool + Resources.

## Notes
- Captain-Engpass + 3× Cost-Tier-Curve = Anti-Spam-Sicherung
- Permadeath bei Loss (T-105) — keine billige Replacement-Spam-Strategie
- Carrier-Squadrons-Decision-Folge: falls Player tiefere Mechanik will,
  separates Ticket "Carrier-Squadron-Mechanik" (nicht T-102)
- T-128 Schiffbau-Branch muss vor T-102-Implementation die 10 Research-Nodes
  bereitstellen (oder T-102 setzt sie auf seinen Path als Stub)

### Refinement Tokens (estimate)
- Input: ~14k
- Output: ~5k

### Implementation Tokens (estimate)
- Input: ~180k
- Output: ~22k

### Deferred / Follow-Ups

- `Ship::getEffectiveDamage/Hp/Shield()` Read-API → T-103 Battle-Resolver
  (Stats werden dort konkret konsumiert)
- Captain-Stats-Multiplier auf Combat-Schiff → T-103
- Fuel-Felder im Blueprint → T-066 Fuel-Mechanik
- `ShipFixture` mit Test-Schiffen pro Klasse — Tests bauen inline, Fixture
  nicht zwingend für Foundation
