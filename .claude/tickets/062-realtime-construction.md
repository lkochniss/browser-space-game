# T-062: Echtzeit-Bauzeit (Wall-Clock Construction)

**Type:** Feature
**Epic:** Foundation: Buildings
**Domain:** Building
**Blocked By:** T-009
**Status:** Done
**FX:** No
**MIG:** No (`buildings.finished_at` Spalte schon in `Version20260618000003` durch T-009 angelegt)
**Depends on:** T-009 ✓

## Description

Lt. User-Vision (T-009-Klärung): "Gebäudebau ist Echtzeit und nicht teil der Tick-Engine". Buildings haben Wall-Clock-Bauzeit. Während Bauphase wirken sie nicht (keine Production, kein Cap-Bonus). Nach Ablauf werden sie aktiv.

## AC

- [x] `BuildingDurationConfig` Service mit Base-Duration pro `BuildingType` und exponentieller Skalierung `2^currentLevel` (analog T-010 Cost):
  - Mines: 5 Minuten (300s)
  - Hub / IRON_SMELTER: 30 Minuten (1800s)
  - Storage-Buildings: 15 Minuten (900s)
- [x] `Building::isReady(?DateTimeImmutable $now): bool`:
  - `finishedAt === null` → ready (Legacy/Test-Fixtures)
  - `finishedAt !== null && now === null` → konservativ NICHT ready
  - `finishedAt !== null && now !== null` → `finishedAt <= now`
- [x] `BuildBuildingCommandService` injiziert `ClockInterface` + `BuildingDurationConfig`. Setzt `finishedAt = now + duration`.
- [x] `UpgradeBuildingCommandService` analog: setzt `finishedAt` neu nach Level++ → Building ist während Upgrade-Phase nicht ready.
- [x] `Planet::recalculatePopulationCap(?DateTimeImmutable $now)`: zählt nur ready Buildings.
- [x] `Planet::addBuilding(building, ?now)`: leitet Clock zu Recalc weiter.
- [x] `TickProcessorInterface::process(Planet, ?DateTimeImmutable $now)`: alle Processors bekommen Clock-Context.
- [x] `TickEngine::run(GameState)` zieht Clock aus `$gameState->getClock()->now()` und reicht weiter.
- [x] `ResourceProductionProcessor` filtert nicht-ready Buildings.
- [x] `RefinementProductionProcessor` filtert nicht-ready Buildings.
- [x] **Strategie C-lite gewählt:** Neuer `ConstructionCompletionProcessor` (TickProcessor) ruft `recalculatePopulationCap($now)` auf — idempotent, läuft als ERSTER Processor pro Tick.
- [x] `ClaimStartPlanetCommandService` injiziert ClockInterface, setzt Start-IRON_MINE `finishedAt = clock->now()` → instant ready für Onboarding.
- [x] `ClockInterface` als Service alias auf `SystemClock` registriert (`config/services.yaml`).
- [x] `PlayerStartUpScenario` startet Game-Clock bei Wall-Clock-now (statt fixem Datum), damit Service-finishedAt mit Tick-Clock konsistent.
- [x] Bestehende Tests grün (180/180, +22: 5 isReady, 4 DurationConfig, 3 ConstructionCompletion, 2 Production-isReady, 1 Refinement-isReady, 7 angepasste IT)

## Geklärte Fragen

1. **Recalc-Strategie:** C-lite — ConstructionCompletionProcessor läuft jeden Tick und ruft Clock-aware recalc.
2. **Bauzeit-Werte:** Mines 5min, Hub/Smelter 30min, Storage 15min — exponentiell × 2^level pro Upgrade.
3. **Start-Mine:** instant ready (`finishedAt = clock.now()`).
4. **Upgrade-Bauzeit:** ja, exponentiell wie Cost. Building bleibt während Upgrade-Phase "not ready".
5. **Bau-Cancel:** out of scope, eigenes Sub-Ticket bei Bedarf.

## Implementation

- `src/Building/Service/BuildingDurationConfig.php` (neu)
- `src/Building/Model/Building.php` (`isReady`-Methode)
- `src/Tick/Interface/TickProcessorInterface.php` (signature `process(Planet, ?DateTimeImmutable)`)
- `src/Tick/Engine/TickEngine.php` (Clock aus GameState)
- `src/Tick/Processor/ConstructionCompletionProcessor.php` (neu)
- `src/Tick/Processor/ResourceProductionProcessor.php` (isReady-Filter)
- `src/Tick/Processor/RefinementProductionProcessor.php` (isReady-Filter)
- `src/Tick/Processor/PopulationConsumptionProcessor.php` (signature update; keine Logik-Änderung)
- `src/Building/Service/BuildBuildingCommandService.php` (Clock + Duration injected)
- `src/Building/Service/UpgradeBuildingCommandService.php` (Clock + Duration injected)
- `src/Planet/Model/Planet.php` (`addBuilding(building, ?now)`, `recalculatePopulationCap(?now)`)
- `src/Planet/Service/ClaimStartPlanetCommandService.php` (Clock injected, Start-Mine finishedAt = now)
- `src/Simulation/Scenario/PlayerStartUpScenario.php` (Wall-Clock-Start + ConstructionCompletionProcessor)
- `config/services.yaml` (`ClockInterface` → `SystemClock` alias)
- 7 Test-Files mit 22 neuen/angepassten Assertions

## Edge Cases (getestet)

- `Building::isReady` mit allen 4 Kombinationen (finishedAt null/set × clock null/set)
- `BuildingDurationConfig` exponential scaling L0/L1/L5
- `ConstructionCompletionProcessor` mit in-progress / completed / no-clock
- Production-Processor: in-progress Mine produziert nicht; completed Mine produziert
- Refinement-Processor: in-progress Smelter refines nicht
- Build-Service: finishedAt korrekt gesetzt; Hub-Bau erhöht Cap NICHT sofort
- Upgrade-Service: Hub während Upgrade-Phase verliert Cap-Bonus (level++, aber not-ready)
- Start-Mine instant ready (Claim setzt finishedAt = now)

## Behavior-Change vs T-006/T-009

- **T-006:** addBuilding triggerte sofort recalc; HUB → cap +50 sofort. **Jetzt:** recalc skipt nicht-ready Buildings; HUB-Bau braucht Bauzeit bis cap rises.
- **T-009:** Build instant; finishedAt=null. **Jetzt:** finishedAt=now+duration.
- **T-010:** Upgrade instant; cap auf neuem Level. **Jetzt:** während Upgrade ist Hub "not ready" → cap fällt auf base. Erst nach Wall-Clock-Wait → Cap reflektiert neues Level.

## Folge-Tickets

- **T-064 Bauzeit-Speed-Boost:** Forschung + Spezial-Buildings reduzieren Duration (User-Vision).
- **Bau-Cancel/Refund:** Spieler kann laufenden Bau abbrechen, Resources/Pop teilweise refunden.
- **Construction-Queue:** mehrere parallele Bauten — Reihenfolge + Worker-Limit.

### Token Usage (estimate)
- Input: ~22k
- Output: ~10k
