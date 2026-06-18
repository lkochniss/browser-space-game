# T-026: Antriebstechnologie-Tree

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** No
**Depends on:** T-025

## Description

`docs/Antriebstechnologie.md`: 4 Standardantriebe (Wasserstoff/Ionen/Fusion/Antimaterie) + 3 FTL (Hyperraum/Warp/Sprung). Progression durch Forschung. Sondengeschwindigkeit (T-013) durch Wasserstoff verbessert.

## AC

- [ ] 7 ResearchNodes registriert im ResearchTree
- [ ] Sinnvolle Prerequisites (Standard-Tier vor FTL, etc.)
- [ ] `PropulsionType` enum für Schiff-Antriebs-Mapping
- [ ] Schiffe nutzen `PropulsionType` für Speed/Range/Fuel-Verbrauch (T-017 Hook)
- [ ] FTL-Antriebe schalten Inter-System-Reise frei

## Affected

- `src/Research/Service/ResearchTree.php` (Konfiguration)
- Neu: `src/Ship/ValueObject/PropulsionType.php`
- `src/Ship/Model/Ship.php` (propulsion field)

## Open Questions

1. Speed-Werte / Range pro Antrieb?
2. Fuel-Type pro Antrieb (Wasserstoff = H2, Antimaterie = Antimaterie-Resource)? Eigene Resources nötig?
