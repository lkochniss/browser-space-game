# T-002: Endliche Rohstoffe — Extend

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** No

## Description

`docs/Rohstoff.md` definiert endliche Rohstoffe: Eisenerz (✓), Kohle, Kupfererz, Silizium, Aluminiumerz, Titanerz, Uranerz. Aktuell nur Eisenerz im Code. Rest als `ResourceType` Cases ergänzen — Voraussetzung für Building-Chains (T-003) + Ship-Kosten.

## AC

- [ ] `ResourceType`: `COAL`, `COPPER_ORE`, `SILICON`, `ALUMINUM_ORE`, `TITANIUM_ORE`, `URANIUM_ORE`
- [ ] `ResourceProductionConfig` Base-Werte je Typ
- [ ] `ResourceBuildingMap` zukunftsfähig — Mining-Building je Erz (oder generische Mine + Type-Switch — entscheiden)
- [ ] Planet kann Deposits dieser Typen halten

## Affected

- `src/Resource/ValueObject/ResourceType.php`
- `src/Resource/Service/ResourceProductionConfig.php`
- `src/Building/Service/ResourceBuildingMap.php`

## Open Questions

1. Pro Erz eigene Mine (CoalMine, CopperMine…) oder generische Mine mit Erz-Typ? Doc sagt "Eisenhütte" speziell — also Trend zu spezialisierten Buildings.
2. Forschung gating für bestimmte Erze? Doc sagt "Um den Abbau einiger Rohstoffe freizuschalten müssen entsprechende Forschungen freigeschaltet werden" — welche?
