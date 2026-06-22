# T-174: Deprecate Station-Build-Command (Lost-Tech-Lore)

**Type:** TechDebt (Feature-Refactor)
**Status:** Draft
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

- [ ] `BuildSpaceStationCommand` + Handler entfernen (oder deprecated mit
      Domain-Exception "Station construction tech is lost")
- [ ] Demo-CLI-Action für Station-Build entfernen (`app:demo:run` Choice-Menü)
- [ ] T-023-bezogene Build-Tests entfernen / als Skip markieren
- [ ] Shipyard-L3-Gate für Build-Path entfernen (Gate bleibt nur für
      Claim-Validation via T-023b)
- [ ] Doc `poi.md` updaten: Stations sind Galaxy-Fixed, kein Build-Path
- [ ] Doc `decisions.md` Eintrag: "Stations sind Lost-Tech; Build deprecated"
- [ ] README-Update T-023 mit "Build-Path deprecated via T-174"

## Out of Scope

- Galaxy-Spawn-Logik (T-175)
- Combat-Capture-Mechanik (T-176)
- Maintenance-Tick (T-023b)

## Notes

- Sequenz wichtig: T-175 muss **vor** T-174 deployed werden, sonst gibt es
  während Deploy-Window keine Stations im Universum (Bootstrap-Lücke)
- Wenn T-174 vor T-175 fertig wird, BuildCommand erstmal deprecated mit
  Domain-Exception lassen — nicht hart entfernen
