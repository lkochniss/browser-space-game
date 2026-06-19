# Tick-System

## TickEngine

`TickEngine::run(GameState)` iteriert `iterable<TickProcessorInterface>` (autowired
via `_instanceof`-Tag + `tagged_iterator app.tick_processor`) und ruft pro Player-
Planet `processor->process(planet, $now)`.

`$now = gameState.getClock()->now()`. ClockInterface ist alias to:
- `SystemClock` (default, prod/dev)
- `AdjustableClock` (demo/test, time-travel via `advanceSeconds(int)`)

## Tick-Reihenfolge (deterministic)

1. **`ConstructionCompletionProcessor`** (T-062) — recalc Pop-Cap mit `$now`,
   sodass fertig gewordene Hubs **im selben Tick** Bonus geben
2. **`ResourceProductionProcessor`** — Mining via `BasicResourceExtractionPolicy`,
   T-151 SoftCap (Stockpile-Drag) angewandt
3. **`RefinementProductionProcessor`** — IRON_SMELTER 2:1:1 Recipes
4. **`PopulationConsumptionProcessor`** — Pop verbraucht 1 W/F/O pro Tick (T-005);
   Logistic-Growth + T-151 Pop-Soft-Cap
5. **`ShipSupplyProcessor`** (T-012) — Ship-Life-Support, killShip + DebrisField-Spawn (T-021)
6. **`RecyclingProcessor`** (T-021) — DEBRIS_* aus Planet-Storage → random output
   via `RecyclingTable` × `Randomizer`

## Globale Tick-Services (NICHT TickProcessorInterface)

Werden direkt nach `TickEngine.run` von Demo-CLI / T-044 Scheduler aufgerufen:

- **`FleetArrivalService::resolveArrivedFleets()`** (T-017) — Fleets mit `arrivedAt ≤ now` docken
- **`SalvageProcessor::runTick()`** (T-016) — aktive Salvage-Schiffe extrahieren
- **`TelescopeDiscoveryService::runTickForPlayer($player)`** (T-018) — N=Total-Telescope-Level random unseen Systems

## SoftCap-Hooks (T-151)

`SoftCapConfig` zentral verwaltet Diminishing-Returns:
- `popGrowthMultiplier(popTotal)` — clamp 0.1 ab 1M Pop
- `buildingCostMultiplier(level)` — 1.05^(level-20) ab Level 20+
- `miningMultiplier(stockpile)` — clamp 0.5 ab 100k

## Files

- `src/Tick/Engine/TickEngine.php` (atomic, `tagged_iterator`)
- `src/Tick/Interface/TickProcessorInterface.php`
- `src/Tick/Processor/*Processor.php` (6 Processors)
- `src/Tick/Policy/BasicResourceExtractionPolicy.php` (Mining-Hook für T-151)
- `src/Common/Service/{SoftCapConfig,Randomizer,AdjustableClock,SystemClock}.php`
- `src/GameState/Model/GameState.php` (Player + Clock-Wrapper)

## Cross-Domain

Jeder Processor wirkt auf `Planet` als Aggregat-Wurzel: Buildings, Resources,
Population, Ships am Planet werden mutiert. Cross-Aggregat (Fleet, Salvage,
Discovery) läuft über die globalen Services oben.

## Geplant

- **T-044** Tick-Scheduler (Cron/Messenger) — automatischer Trigger statt manueller Demo-CLI-Tick
- **T-064** Construction-Speed-Boost — Multiplier in Build/Upgrade-Services (decisions vorab)
