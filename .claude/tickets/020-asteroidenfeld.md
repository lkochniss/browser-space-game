# T-020: Asteroidenfeld POI

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** No
**Depends on:** T-019

## Description

`docs/Asteroidenfeld.md`: Endliche Rohstoffe. Per Bergungsschiff abbaubar, transportiert zu Planet/Station.

## AC

- [ ] `AsteroidField` POI-Subtype
- [ ] Hält `ResourceDepositCollection` (mehrere Erz-Typen möglich)
- [ ] Generierung beim System-Generate (zufällig Anzahl + Inhalt)
- [ ] Bei `amount == 0` aller Deposits → POI verschwindet (siehe T-016)

## Affected

- Neu: `src/POI/Model/AsteroidField.php`
- `src/SolarSystem/Service/...` (Generierung beim System-Generate)

## Open Questions

1. Asteroidenfeld kann auch Erzeugnisse halten (selten) oder nur Erze?
2. Spawning: random pro System-Generate, oder dynamisch über Zeit?
