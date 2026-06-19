# T-022: Nebel POI + Stealth

**Type:** Feature
**Status:** Done
**FX:** No
**MIG:** Yes (`Version20260619000010` — pois.nebula_concealment)
**Depends on:** T-019

## Description

Nebel als POI-Subtype mit `concealmentLevel` (1-10). Foundation-Stub: nur die
Stat-Daten. Effekte (Fleet-Verbergung, Battle-Modifier, Detection-Logic) werden
in Folge-Tickets integriert wenn die jeweiligen Engines bereit sind.

## AC

- [x] `Nebula` POI-Subtype (extends Poi)
- [x] `Poi`-DiscriminatorMap aktualisiert: `'nebula' => Nebula::class`
- [x] `concealmentLevel` (1-10) als nullable Integer-Spalte (nur Nebula nutzt sie)
- [x] Validation: Range [1, 10] sonst InvalidArgumentException
- [x] Galaxy-Spawn: 30% Chance pro System für 1 Nebel mit zufälligem Level [3, 9]
- [x] Migration `Version20260619000010`
- [x] Tests: 9 Unit (NebulaTest mit DataProvider), 1 IT (NebulaPersistenceTest)
- [x] Suite grün (359/359, 1369 assertions)

## Geklärte Fragen

1. **Buff/Debuff in Schlacht:** Foundation hält nur `concealmentLevel`. Battle-
   Modifier kommt mit T-103.
2. **Max Flotten pro Nebel:** Keine Begrenzung (Foundation pragmatisch).

## Out of Scope (Folge-Tickets)

- **Fleet-Hidden-State im Nebel** → T-074 Pirate-Encounter-Spawn (NPC ignoriert
  Schiffe im Nebel) + T-103 Battle (Modifier durch schlechte Sicht)
- **Detection-Hook beim Anflug** → T-018 Teleskop / T-087 Fog-of-War
- **MoveFleetCommand respektiert Nebula** → T-017 Erweiterung wenn Detection-
  Mechanik kommt

## Files

**Neu:**
- `src/POI/Model/Nebula.php` (extends Poi, STI-Subtype)
- `migrations/Version20260619000010.php`
- `tests/POI/Model/NebulaTest.php`
- `tests/POI/Persistence/NebulaPersistenceTest.php`

**Geändert:**
- `src/POI/Model/Poi.php` (DiscriminatorMap: 'nebula' → Nebula::class)
- `src/Planet/Service/ClaimStartPlanetCommandService.php` (maybeGenerateNebula)
