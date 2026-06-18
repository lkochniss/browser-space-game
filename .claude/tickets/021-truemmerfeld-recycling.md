# T-021: Trümmerfeld + Trümmer + Recycling-Anlage

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** No
**Depends on:** T-019, T-024 (Schlacht erzeugt Trümmer)

## Description

`docs/Trümmerfeld.md` + `docs/Trümmer.md` + `docs/Recycling-Anlage.md`: Trümmerfeld bleibt nach Raumschlacht zurück. Bergungsschiff (T-016) holt Trümmer raus. Trümmer haben Qualitäts-Tier. Recycling-Anlage konvertiert Trümmer → zufällige Erzeugnisse (Wahrscheinlichkeit pro Tier).

## AC

- [ ] `DebrisField` POI-Subtype
- [ ] `Debris` Entity mit `DebrisQuality` enum (z.B. `LOW`, `MEDIUM`, `HIGH`, `RARE`)
- [ ] DebrisField wird durch `BattleResolver` (T-024) erzeugt — Größe/Qualität abhängig von Verlusten
- [ ] `BuildingType::RECYCLING_PLANT`
- [ ] `RecyclingProcessor` (TickProcessor): pro Tick verbraucht Trümmer auf Planet, würfelt Erzeugnis nach Tier-Wahrscheinlichkeit

## Affected

- Neu: `src/POI/Model/DebrisField.php`
- Neu: `src/Debris/Model/Debris.php`, `ValueObject/DebrisQuality.php`
- `src/Building/ValueObject/BuildingType.php`
- Neu: `src/Tick/Processor/RecyclingProcessor.php`

## Open Questions

1. Wahrscheinlichkeits-Tabelle pro Tier — sinnvolle Defaults?
2. Trümmer als generische Cargo-Items oder eigene Entity?
3. Tier-Anzahl: 3 oder 4?
