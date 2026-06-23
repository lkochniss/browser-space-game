# T-082b: Demo-CLI UX-Polish

**Type:** Feature
**Epic:** Demo CLI
**Domain:** Demo
**Blocked By:** T-082
**Status:** Done
**Effort:** S (~1.5h)
**Depends on:** T-082 (Demo-Foundation)
**Blocks:** —

## Beschreibung

UX-Polish für T-082 Demo-CLI nach Smoke-Test. Fokus: Player kann das ganze
Spielfeld sehen, weiß was Aktionen kosten, und stirbt nicht in den ersten
Ticks an Pop-Hunger.

## Acceptance Criteria

- [x] **Galaxy-Status-Action**: zeigt alle 5 SolarSystems mit Planeten + POIs
  (nicht nur Heimat-Planet). Owner-Marker pro Planet.
- [x] **Build-Choice-Format**: BuildBuilding / BuildShip / BuildProbe Choice-Menüs
  zeigen Cost + Pop-Cost direkt im Label statt nur Type-Name
- [x] **Demo-Galaxy-Garantie**: Demo-Setup garantiert mindestens 1 AsteroidField
  + 1 Wormhole-Pair in der Galaxy (statt zufällig). Wormhole verbindet Heimat-
  System mit System mit Asteroid → Salvage- und Travel-Demo immer testbar.
- [x] **Demo-Buff für Start-Planet**: Hub L1 vorab + 300 W/F/O initial statt 100,
  damit Pop nicht in den ersten 5 Ticks stirbt
- [x] **Pre-Validation-Help**: BuildShip → Shipyard-Check, BuildProbe → Probe-Lab-
  Check, StartSalvage → Asteroid-In-System-Check, ColonizePlanet → Colony-Ship +
  Unclaimed-Planet-Check, LoadCargo → Transport-Ship-Check (über `chooseXxxShip`-
  Helpers, freundliche Note statt Exception)
- [x] Smoke-Test grün: `app:demo:run --reset --env=demo` bootet sauber, Hub L1 +
  300 W/F/O + Wormhole-Pair nachweislich in DB
- [x] Suite grün (423/423)

## Out of Scope (Folge-Tickets)

- **Forschung im Demo** → T-025 separater Sprint
- **Multi-Resource-Cargo-Load in einem Step** → Folge-Erweiterung
- **Galaxy-Map als ASCII-Art** → Cluster-H UI

## Files

**Geändert:**
- `src/Demo/Command/InteractiveDemoCommand.php` (alle 5 Polish-Items: showGalaxy,
  Build-Cost-Preview, applyDemoBuff, ensureDemoGalaxyContent, hasShipyard/
  hasProbeLab Pre-Checks; Constructor um SolarSystemRepository,
  BuildingCostConfig, ShipCostConfig, ProbeCostConfig erweitert)
