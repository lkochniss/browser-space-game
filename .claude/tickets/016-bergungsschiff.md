# T-016: Bergungsschiff + Salvage

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** No
**Depends on:** T-012 (Schiff-Foundation), T-020 (AsteroidField done), T-021 (DebrisField — Folge)

## Description

Ziviles Schiff, baut Trümmerfelder und Asteroidenfelder ab und transportiert das
Material zurück.

**Stand nach T-019/T-020:** AsteroidField existiert mit `extract(ResourceType, int)`-
API. Salvage kann direkt darauf operieren. Trümmerfeld (T-021) ist noch Foundation-
Stub im POI-DiscriminatorMap (`debris_field` → Poi::class) — T-021 muss vor full
Salvage-Implementation finalisiert werden.

## AC

- [ ] `ShipType::SALVAGE` (oder generisches Bergungsschiff via existing Transport-
  Klassen-Erweiterung — entscheiden)
- [ ] `SalvageCommand` (shipId, targetPoiId)
- [ ] Per Tick / Per Action: Ship am AsteroidField → `AsteroidField::extract(type, amount)`
  ins Schiff-Cargo (T-015 CargoManifest)
- [ ] Per Tick: Ship am DebrisField → Debris ins Schiff-Cargo (T-021-dependent)
- [ ] **POI-Cleanup-Verantwortung**: Bei `field->isEmpty()` → `em->remove($field)`
  (für AsteroidField + DebrisField)
- [ ] ShipCostConfig erweitert um SALVAGE-Klasse mit eigener Cargo-Capacity
  (analog Transport-Klassen)

## Out of Scope (bisherige Folge-Tickets)

- **Discovery-Required vor Salvage** → T-087 Fog-of-War
- **Treibstoff** → T-066 + T-105
- **Salvage-Effizienz pro Schiff-Klasse / Tier-Forschung** → T-127 Mining/Industrie-Branch

## Open Questions

1. Salvage-Rate pro Tick (fix vs. Schiffsklasse)?
2. Cargo-Mechanik: Schiff hält geborgene Ware (CargoManifest) oder direkt Heimat-
   Planet — Empfehlung: CargoManifest, Player nutzt T-015 UnloadCargo zum
   Heim-Transfer.
3. Asteroid-Mining vs. Debris-Recycling: gleiche Mechanik oder getrennt?

## Affected

- `src/Ship/ValueObject/ShipType.php` (+ SALVAGE)
- Neu: `src/Ship/Command/SalvageCommand.php` + Handler + Service
- Neu: `src/Tick/Processor/SalvageProcessor.php` ODER Per-Action ohne Tick
- `src/POI/Repository/PoiRepository.php` (ggf. helper für POI-Cleanup wenn isEmpty)
