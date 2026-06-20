# T-015c: Station-Pop-Transfer (Cargo-Pop ↔ Station-Pop)

**Type:** Feature
**Status:** Draft
**Effort:** S (~1.5h)
**Depends on:** T-015b (Station-Cargo-Transfer), T-023b (Station-Maintenance — Draft)
**Blocks:** —

## Beschreibung

T-015b implementiert Resources-Transfer Ship ↔ Station. **Pop-Transfer** ist
explizit deferred: Schiff lädt Pop am Planet, kann sie aber nicht in eine
Station entladen (oder umgekehrt). Folge: Stations können nicht via Schiff
versorgt werden — sind isoliert.

## Acceptance Criteria

- [ ] LoadCargoCommandService akzeptiert `popCount > 0` mit Station als Source
      (zieht aus `station.populationOnStation` ab, lädt in Ship-Cargo)
- [ ] UnloadCargoCommandService akzeptiert `popCount > 0` mit Station als Target
      (entlädt Ship-Cargo, addiert auf `station.populationOnStation`, mit
      Cap-Check via Station-MaxPopulation — siehe T-023b)
- [ ] Owner-Restriction: Cross-Player-Transfer rejected (Foundation; T-093
      Allianz-Stations relaxen das später)
- [ ] Tests
- [ ] Doc: ships.md Cargo-Sektion ergänzen

## Out of Scope

- Station-Pop-Wachstum-Mechanik (T-023b)
- Allianz-Stations (T-093)
