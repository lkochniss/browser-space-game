# T-020: Asteroidenfeld POI

**Type:** Feature
**Epic:** POI System
**Domain:** POI
**Blocked By:** T-019
**Status:** Done
**FX:** No
**MIG:** Yes (`Version20260619000009` — pois.asteroid_contents JSON-column)
**Depends on:** T-019

## Description

Erster konkreter POI-Subtype. Endliche FINITE-Resources als JSON-Map auf
`pois.asteroid_contents`-Spalte. AsteroidField extends Poi via STI-Discriminator
`asteroid_field`. Galaxy-Generation seedet 0-2 Felder pro System mit zufälligem
Inhalt.

## AC

- [x] `AsteroidField` POI-Subtype (extends Poi)
- [x] `Poi`-DiscriminatorMap aktualisiert: `'asteroid_field' => AsteroidField::class`
- [x] AsteroidField hält `contents: array<string, int>` (Map<ResourceType-value, amount>)
  als JSON-Spalte `asteroid_contents` (nullable, da nur AsteroidField sie nutzt)
- [x] API: `getAmount(ResourceType)`, `setAmount`, `extract`, `getTotalAmount`,
  `isEmpty`, `getContents`
- [x] Validation: negative Amounts werfen InvalidArgumentException;
  setAmount(0) entfernt Key aus Map (clean state)
- [x] `extract`: clamped bei 0, returns tatsächlich entnommene Menge
- [x] Galaxy-Generation: 0-2 Felder pro System bei `ClaimStartPlanetCommandService`,
  jedes Feld hat 1-3 zufällige FINITE-Resources × 500-2000 Amount
- [x] Migration `Version20260619000009`
- [x] Tests: 7 Unit (AsteroidField API), 2 IT (STI-Persistence + extract-Persistence),
  1 IT (Galaxy-Spawn-Logic mit 10-Run-RNG-Validation)
- [x] Suite grün (349/349, 1262 assertions)

## Geklärte Fragen

1. **Erzeugnisse oder nur Erze:** Foundation nur FINITE-Erze. Erzeugnisse-Asteroiden
   ggf. als Folge-Idee (z.B. T-021 Trümmerfeld droppt REFINED-Goods statt FINITE).
2. **Spawning:** Random pro System-Generate. Dynamische Re-Spawns über Zeit kommen
   später (kein Foundation-Bedarf).

## Out of Scope (Folge-Tickets)

- **Bergungsschiff-Mining** → T-016 (Salvage-Action konsumiert AsteroidField.extract)
- **POI-Cleanup bei isEmpty** → T-016 ist Owner — bei Mining-Action den isEmpty-
  Check + em->remove(field) wenn 0
- **REFINED-Goods in Asteroiden** → künftige Erweiterung
- **Discovery-Required vor Mining** → T-087 Fog-of-War

## Files

**Neu:**
- `src/POI/Model/AsteroidField.php` (extends Poi, STI-Subtype)
- `migrations/Version20260619000009.php`
- `tests/POI/Model/AsteroidFieldTest.php`
- `tests/POI/Persistence/AsteroidFieldPersistenceTest.php`

**Geändert:**
- `src/POI/Model/Poi.php` (DiscriminatorMap: 'asteroid_field' → AsteroidField::class)
- `src/Planet/Service/ClaimStartPlanetCommandService.php` (generateAsteroidFields-Hook)
- `tests/Planet/Service/ClaimStartPlanetCommandServiceTest.php` (+1 Spawn-Test)
