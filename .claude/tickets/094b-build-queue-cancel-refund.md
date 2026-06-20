# T-094b: Bau-Queue Cancel + Refund

**Type:** Feature
**Status:** Done
**Effort:** S (~1.5h)
**Depends on:** T-094 (Bau-Queue Foundation)
**Blocks:** —

## Beschreibung

T-094 hat Slot-Cap für parallele Builds. Player kann gerade laufende Bauten
NICHT abbrechen. Bei Fehlentscheidung blockt der Slot bis fertig.

T-094b: `CancelBuildCommand(planetId, buildingId)`. Bricht laufenden Build/
Upgrade ab. Refund 50% der Resources (Pop wird voll zurückgegeben).

## Acceptance Criteria

- [ ] `CancelBuildCommand` + Handler
- [ ] `CancelBuildCommandService`:
  - prüft Building gehört Planet, Building.finishedAt > now (= unfinished)
  - bei Initial-Build: Building löschen, 50% Resource-Refund, voll Pop-Refund
  - bei Upgrade: Level zurücksetzen + finishedAt=null, 50% Cost-Refund
- [ ] `BuildingNotInProgressException` wenn Building bereits ready
- [ ] Demo-CLI Menu-Action "Cancel Build"
- [ ] Tests: cancel-initial-refund, cancel-upgrade-restores-level, cancel-finished-throws
- [ ] Doc: buildings.md Cancel-Sektion

## Out of Scope

- Refund-Rate via Forschung erhöhen
- Cancel von Forschung (eigenes Ticket bei Bedarf)
