# T-001: Renewable Rohstoffe

**Type:** Feature
**Status:** Open
**FX:** No (kein Persistence-Layer)
**MIG:** No

## Description

`docs/Rohstoff.md` listet erneuerbare Rohstoffe Wasser/Nahrung/Sauerstoff. Code kennt bislang nur `IRON_ORE`. Diese 3 als `ResourceType` ergänzen — Voraussetzung für Pop-Verbrauch (T-005) und Ship-Life-Support (T-012).

## AC

- [ ] `ResourceType` enum: `WATER`, `FOOD`, `OXYGEN` ergänzt
- [ ] Planet generiert mit diesen 3 als `Resource`-Einträgen (Start-Amount = 0 oder Default — entscheiden)
- [ ] `ResourceProductionConfig` kennt Base-Production für renewables (initial sinnvolle Defaults)
- [ ] Bestehende Tests/Sim-Run grün

## Affected

- `src/Resource/ValueObject/ResourceType.php`
- `src/Resource/Service/ResourceProductionConfig.php`
- `src/Planet/Service/ClaimStartPlanetCommandService.php` (oder wo Resources initialisiert werden)

## Open Questions

1. Wasser/Nahrung/Sauerstoff: per-Tick passive Produktion auf erdähnlichem Planet ja/nein? (Doc sagt: nur auf Planeten ohne Atmosphäre wird Sauerstoff "benötigt" — auf erdähnlichen kostenlos?)
2. Start-Amount nach Claim: 0, oder mit kleinem Vorrat?
