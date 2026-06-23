# T-003: Erzeugnis-Konzept + Eisenbarren via Eisenhütte

**Type:** Feature
**Epic:** Foundation: Resources
**Domain:** Resource
**Blocked By:** T-002
**Status:** Done
**FX:** No
**MIG:** No
**Depends on:** T-002 (Kohle)

## Description

`docs/Erzeugnis.md`: Erzeugnisse werden aus Rohstoffen erzeugt. Eisenhütte = Eisenerz + Kohle → Eisenbarren. Erstes Beispiel der Veredelungs-Kette.

## AC

- [ ] Konzept-Entscheidung: separater `RefinedProductType` enum ODER `ResourceType` mit `isRefined()` flag
- [ ] `IRON_BAR` als Type
- [ ] `BuildingType::IRON_SMELTER`
- [ ] Neuer `TickProcessor` (oder Extension von ResourceProductionProcessor): Eisenhütte verbraucht Eisenerz + Kohle, erzeugt Eisenbarren
- [ ] Verarbeitungsrate per Building-Level
- [ ] Wenn Input-Rohstoffe leer → keine Produktion (kein negativer Bestand)

## Affected

- `src/Resource/ValueObject/ResourceType.php` (oder neuer `RefinedProductType`)
- `src/Building/ValueObject/BuildingType.php`
- `src/Tick/Processor/` (neuer Processor)
- `src/Resource/Service/ResourceProductionConfig.php`

## Open Questions

1. Erzeugnisse als eigener Type-Enum oder im selben Enum mit Flag/Group?
2. Input-Verhältnis Eisenerz : Kohle : Eisenbarren? Vorschlag 2:1:1.
3. Reicht Output für Production weiterhin in Resource-Lager oder eigenes Erzeugnis-Lager?
