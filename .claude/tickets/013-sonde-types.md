# T-013: Sonden (Systemsonde, Orbitalsonde, Tiefenscan-Sonde)

**Type:** Feature
**Status:** Done
**FX:** No
**MIG:** Yes (`Version20260619000005` — probes table)
**Depends on:** T-012

## Description

Sonden-Foundation. 3 Probetypen mit unterschiedlichen Cost/Duration/Capabilities. Discovery-Effekt absichtlich Out-of-Scope — kommt mit T-018 (Teleskop) und T-087 (Fog-of-War). Hier nur Build/Persist + Capabilities-Stub.

## AC

- [x] `ProbeType` Enum: `SYSTEM`, `ORBITAL`, `DEEP_SCAN`
- [x] Capabilities-Stubs: `getRange()` + `isOneShot()` pro Type
- [x] `Probe` Entity (id, type, planet, finishedAt für Wallclock-Build)
- [x] `ProbeId` ValueObject + `ProbeIdType` Doctrine-Custom-Type, registriert in `doctrine.yaml`
- [x] `ProbeRepository` (mit `findByPlanet`)
- [x] Migration `Version20260619000005` (probes table mit FK auf planets)
- [x] `BuildingType::PROBE_LAB` als neues Building (Voraussetzung für Sondenbau)
- [x] `BuildingCostConfig` PROBE_LAB-Eintrag (200 Iron + 100 Silicon + 50 Copper, 15 Pop)
- [x] `BuildingDurationConfig` PROBE_LAB-Eintrag (1800s = 30min)
- [x] `Planet::getProbeLabLevel(?DateTimeImmutable): int` + `Planet::hasProbeLab(?DateTimeImmutable): bool`
- [x] `ProbeCostConfig` mit Cost+Duration pro ProbeType
  - SYSTEM: 30 IRON_BAR, 10min
  - ORBITAL: 80 IRON_BAR + 30 SILICON, 20min
  - DEEP_SCAN: 200 IRON_BAR + 80 SILICON + 50 COPPER_ORE, 60min
- [x] `BuildProbeCommand` + `BuildProbeCommandHandler` + `BuildProbeCommandService`
- [x] 3 Domain-Exceptions (PlanetNotFound, MissingProbeLab, InsufficientResources)
- [x] Tests: 6 Unit (ProbeType, ProbeCostConfig), 5 Unit (PlanetProbeLab), 8 IT (BuildProbeCommand) + bestehende Tests erweitert
- [x] Suite grün (273/273, 589 assertions)

## Out of Scope (Folge-Tickets)

- **Discovery-Effekt** → T-018 Teleskop + T-087 Fog-of-War
- **Probe-Transport via Schiff** → T-015 (Transportschiff) + T-017 (Flotte-Movement)
- **Forschungs-Locks** → T-027 Planetologie-Forschung
- **Capabilities-Erweiterung** (Scan-Resolution, hidden POI detection) → T-018 / T-087

## Geklärte Fragen

1. **Domain-Setup:** Eigene `Probe`-Domain (analog `Ship`) — passt nicht in Ship-Tabelle (kein Pop, kein W/F/O).
2. **Build-Voraussetzung:** Eigenes `PROBE_LAB` Building (User-Decision: mehr Buildings).
3. **Effect-Scope:** Build-Only — nur Entity persistieren, kein Discovery-Effect.
4. **Kein Pop-Cost:** Sonden sind unbemannte Geräte (Doc-konform, einfacher Foundation-Stub).

## Files

**Neu:**
- `src/Probe/ValueObject/{ProbeId,ProbeType}.php`
- `src/Common/Doctrine/Type/ProbeIdType.php`
- `src/Probe/Model/Probe.php`
- `src/Probe/Repository/ProbeRepository.php`
- `src/Probe/Command/BuildProbeCommand.php`, `BuildProbeCommandHandler.php`
- `src/Probe/Service/{ProbeCostConfig,BuildProbeCommandService}.php`
- `src/Probe/Exception/{PlanetNotFoundException,MissingProbeLabException,InsufficientResourcesException}.php`
- `migrations/Version20260619000005.php`
- `tests/Probe/ValueObject/ProbeTypeTest.php`
- `tests/Probe/Service/ProbeCostConfigTest.php`
- `tests/Probe/Command/BuildProbeCommandTest.php`
- `tests/Planet/Model/ProbeLabLevelTest.php`

**Geändert:**
- `src/Building/ValueObject/BuildingType.php` (PROBE_LAB enum-case + storage-cases)
- `src/Building/Service/BuildingCostConfig.php` (PROBE_LAB cost)
- `src/Building/Service/BuildingDurationConfig.php` (PROBE_LAB duration)
- `src/Planet/Model/Planet.php` (Helper `getProbeLabLevel`/`hasProbeLab`)
- `config/packages/doctrine.yaml` (probe_id type registriert)
- `tests/Building/ValueObject/BuildingTypeTest.php` (+1 Test)
- `tests/Building/Service/BuildingCostConfigTest.php` (+1 Test)
- `tests/Building/Service/BuildingDurationConfigTest.php` (+1 Provider-Eintrag)
