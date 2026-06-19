# T-085 Wurmloch-POI

**Type:** Feature
**Status:** Done
**Effort:** M
**MIG:** Yes (`Version20260619000011` — pois.wormhole_twin_id + wormhole_tech_slug)
**Depends on:** T-019 (POI)
**Blocks:** —

## Beschreibung

Wormhole-POI Foundation. Pair-Verlinkung A↔B zwischen 2 Systems via `twin`-FK.
Tech-Lock-Slug als String-Stub für T-026 FTL-Tier-2. Travel-Time-Reduktion,
Cooldown, Treibstoff-Multiplier sind Out-of-Scope (Folge-Tickets via T-017
MoveFleet-Erweiterung).

## Acceptance Criteria

- [x] `Wormhole` POI-Subtype (extends Poi)
- [x] DiscriminatorMap aktualisiert: `'wormhole' => Wormhole::class`
- [x] `twin: ?Wormhole` (OneToOne self-referencing, nullable)
- [x] `requiredTechSlug: ?string` (Stub für T-026 FTL-Tier-2)
- [x] `pairWith(Wormhole)` Helper für bidirektionales Linking
- [x] Galaxy-Init: 1 Wurmloch-Pair pro Galaxy zwischen 2 zufälligen Systems
- [x] Migration `Version20260619000011` (twin_id FK self-ref + tech_slug)
- [x] Tests: 5 Unit (pair/idempotent/tech-slug), 1 IT (Persistence + bidirektional),
  1 IT (Galaxy-Spawn-Verification mit cross-system check)
- [x] Suite grün (366/366, 1249 assertions)

## Out of Scope (Folge-Tickets)

- **Travel-Time-Reduktion** → T-017 MoveFleetCommand-Erweiterung: wenn origin
  und target beide Wormholes mit gleichem twin-Pair → kürzere Duration
- **Cooldown 24h pro Schiff** → T-017 Erweiterung mit Schiff-Cooldown-Tracking
- **Treibstoff-Multiplier 5×** → T-066 Treibstoff + T-017 Erweiterung
- **Tech-Lock-Validation** → T-026 (Forschung-Service) prüft requiredTechSlug
- **Galaxy-Map-Visualisierung** → T-160
- **Discovery-Required vor Use** → T-087 Fog-of-War
- **3-5 Pairs pro Galaxy / instabile Wurmlöcher (T-076 Galaxy-Events)** →
  Foundation hat 1 Pair, Skalierung kommt mit größeren Galaxies

## Files

**Neu:**
- `src/POI/Model/Wormhole.php` (extends Poi, STI-Subtype, OneToOne self-ref `twin`)
- `migrations/Version20260619000011.php`
- `tests/POI/Model/WormholeTest.php`
- `tests/POI/Persistence/WormholePersistenceTest.php`

**Geändert:**
- `src/POI/Model/Poi.php` (DiscriminatorMap: 'wormhole' → Wormhole::class)
- `src/Planet/Service/ClaimStartPlanetCommandService.php` (generateWormholePairs)
- `tests/Planet/Service/ClaimStartPlanetCommandServiceTest.php` (+1 Test)
