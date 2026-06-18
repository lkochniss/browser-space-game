# T-007: Sonnensystem-Domain

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** No

## Description

Docs referenzieren überall `[[Sonnensystem]]`. Kein `Sonnensystem.md` und kein Code. Aktuell `Player → PlanetCollection` direkt. Sollte: `Player → SolarSystems → Planets`. System enthält auch POIs, Stations, Flotten (zukünftig).

## AC

- [ ] Neue Domain `src/SolarSystem/`
- [ ] `SolarSystem` Entity mit `SolarSystemId`, `name`, `PlanetCollection`
- [ ] `Planet` referenziert sein `SolarSystem`
- [ ] `Player` besitzt eine Sicht "claimed planets" — gehört Planet einem Player, ist er "claimed"
- [ ] `ClaimStartPlanetCommandHandler` erstellt System + Planet im System
- [ ] `PlayerStartUpScenario` läuft weiter

## Affected

- Neu: `src/SolarSystem/Model/SolarSystem.php`, `ValueObject/SolarSystemId.php`, `Model/SolarSystemCollection.php`
- `src/Planet/Model/Planet.php` (System-Ref)
- `src/Player/Model/Player.php` (Planets-Beziehung neu denken)
- `src/Planet/Command/*`
- `src/Simulation/Scenario/PlayerStartUpScenario.php`

## Open Questions

1. Player besitzt **Planeten** in Systemen, **nicht Systeme**? (Mehrere Player können Planeten im selben System haben — Doc impliziert ja.)
2. `Player.getPlanets()` weiter direkter Shortcut, oder über Systeme aggregieren?
3. Start-Galaxie: 1 System mit 1 erdähnlichem Planet, oder schon mehrere Systeme generieren?
