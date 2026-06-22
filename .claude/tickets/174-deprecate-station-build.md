# T-174: Deprecate Station-Build-Command (Lost-Tech-Lore)

**Type:** TechDebt (Feature-Refactor)
**Status:** Done (Soft-Deprecate-Variante; Hard-Remove erst nach T-175)
**Effort:** S (~1-1.5h)
**Depends on:** T-175 (Pirate-Owned-Station-Spawn — sonst gibt es keine Stations im Universum)
**Blocks:** —

## Beschreibung

Lore-Pivot (T-023b): Station-Bau-Technologie ist im Universum verschollen
(40k-Style). T-023 implementiert aktuell `BuildSpaceStationCommand` via
Shipyard L3. Dieser Path ist mit der neuen Lore inkonsistent und muss
entfernt werden.

Player können Stations nur über **Claim** (T-023b ABANDONED) oder **Combat-
Capture** (T-176, Folge zu T-103) erhalten. Galaxy-Spawn (T-175) gibt die
existierenden Stations.

## Acceptance Criteria

- [x] `BuildSpaceStationCommandService` wirft `StationConstructionDeprecatedException` —
      Command/Handler/Service-Stubs bleiben (Hard-Remove erst nach T-175)
- [x] Demo-CLI hatte nie eine Station-Build-Action — nichts zu entfernen
- [x] `BuildSpaceStationCommandTest` reduziert auf einen Deprecation-Test
- [x] Shipyard-L3-Gate aus Service entfernt (gesamter Validation-Path tot)
- [x] 6 orphan POI-Exceptions entfernt: `InsufficientPopulation`,
      `InsufficientResources`, `MissingShipyardInSystem`, `PlayerNotFound`,
      `SolarSystemNotFound`, `StationAlreadyExistsInSystem`
- [x] Doc `poi.md` updated (T-023-Sektion mit Deprecation-Notiz)
- [x] Doc `decisions.md` Eintrag "Stations sind Lost-Tech; Build deprecated"
- [x] README-Update T-174 Status Done

## Out of Scope

- Galaxy-Spawn-Logik (T-175)
- Combat-Capture-Mechanik (T-176)
- Maintenance-Tick (T-023b)

## Notes

- Sequenz wichtig: T-175 muss **vor** T-174 deployed werden, sonst gibt es
  während Deploy-Window keine Stations im Universum (Bootstrap-Lücke)
- Wenn T-174 vor T-175 fertig wird, BuildCommand erstmal deprecated mit
  Domain-Exception lassen — nicht hart entfernen
