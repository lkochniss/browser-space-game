# T-024: Raumschlacht-Resolution

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** No
**Depends on:** T-017, T-021

## Description

Doc-Referenz `[[Raumschlacht]]` (kein eigenes Doc-File). Aus Flotte/Bevölkerung-Docs ableitbar: Begegnung gegnerischer Flotten → Kampf → Schiffsverluste → Trümmerfeld + Pop-Tod auf Heimatplanet.

## AC

- [ ] `BattleResolver` Service (deterministisch oder seedbar für Reproduzierbarkeit)
- [ ] Input: zwei Fleet, optional Nebel-Modifier (T-022)
- [ ] Output: `BattleResult` (Verluste pro Side, erzeugtes DebrisField)
- [ ] Resolver erzeugt `DebrisField` POI im System (T-021)
- [ ] Pop-Tod auf Heimatplanet pro zerstörtem Schiff (siehe `Bevölkerung.md`)
- [ ] Tick-Processor erkennt Begegnungen (zwei feindliche Flotten gleicher Location) und triggert Resolver

## Affected

- Neu: `src/Battle/Service/BattleResolver.php`, `Model/BattleResult.php`
- Neu: `src/Tick/Processor/BattleEncounterProcessor.php`
- Hooks: `src/Fleet/Model/Fleet.php` (apply losses)

## Open Questions

1. Kampf-Mechanik: Würfelroll (random), Kräfte-Vergleich (deterministisch), oder Round-by-round?
2. "Feindlich" via Allianz/Faction — gibt es Allianzen jetzt? (Multiplayer-Frage.)
3. Nicht-engagierte Flotten (z.B. Transporter ohne Eskorte): instant verloren oder Flucht-Möglichkeit?
