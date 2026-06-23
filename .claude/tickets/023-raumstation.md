# T-023: Raumstation pro System

**Type:** Feature
**Epic:** POI System
**Domain:** POI
**Blocked By:** T-007, T-011, T-019
**Status:** Done
**FX:** No
**MIG:** Yes (`Version20260619000012` — pois.station_*)
**Depends on:** T-007, T-011, T-019

## Description

SpaceStation als POI-Subtype. Erste exklusive Player-Station pro System. Foundation
deckt Build + Storage-Properties + Status-Tracking. Maintenance-Tick + Übernahme-
Mechanik (Doc-Vorgabe vom User: ABANDONED bei Maintenance-Failure → andere Player
können übernehmen) sind explizite Folge-Tickets.

User-Vision: Stationen sind **Raumdocks mit großem Inventar, ohne Resource-
Produktion**. Maintenance bewusst erschwert.

## AC

- [x] `SpaceStation` POI-Subtype (extends Poi via STI, DiscriminatorMap-Update)
- [x] `StationStatus` Enum (ACTIVE / ABANDONED)
- [x] Owner als ManyToOne ?Player (nullable für ABANDONED-State T-023b)
- [x] populationOnStation (int) als Pop-Counter auf Station
- [x] storage = embedded `CargoManifest` (Reuse aus T-015), Capacity 100k
- [x] `BuildSpaceStationCommand` + Handler + Service
  - Validation: Player+System exists, max 1 Station/System, Player hat Shipyard ≥ L3
    auf eigenem Planet im Ziel-System
  - Cost: 5000 Iron-Bar + 1000 Aluminum-Ore + 200 Titanium-Ore + 200 Pop
  - Pop-Mechanik: Pop wird vom Heimat-Planet `kill`'t (free first), Station bekommt
    `populationOnStation = 200` als initial Settler
  - Sofort ACTIVE (kein Wallclock-Build im Foundation)
- [x] 6 Domain-Exceptions in `src/POI/Exception/`
- [x] Migration `Version20260619000012` (station_owner_id FK + status + population +
  storage_capacity + cargo-Fields)
- [x] Tests: 9 IT (Build happy + alle Reject-Pfade + System-POI-Liste)
- [x] Suite grün (375/375, 1321 assertions)

## Geklärte Fragen

1. **Ownership:** Player-Stationen, max 1 pro System, übernehmbar bei Abandonment.
2. **Storage:** Foundation hat Storage-Properties (CargoManifest reuse aus T-015).
3. **Build-Gate:** Shipyard ≥ Level 3 auf eigenem Planet im Ziel-System.
4. **Wallclock-Build:** Out-of-Scope (POIs haben noch keine isReady-Mechanik).

## Out of Scope (Folge-Tickets)

- **T-023b: Maintenance + Übernahme** (User-Vision):
  - Tick-Processor verbraucht Resources (W/F/O × populationOnStation, Power)
  - Bei Resource-Mangel: Pop stirbt → wenn 0 → status=ABANDONED
  - Bei ABANDONED: ClaimAbandonedStationCommand ermöglicht Übernahme durch anderen
    Player
- **Wallclock-Build** (analog Buildings T-062 / Schiffe T-012)
- **Docking-Slots / Resupply via T-017** (Fleet kann Station als Target nutzen)
- **Storage-LoadCargo via T-015** (Schiff lädt von/zur Station)
- **Handel mit fremden Völkern** → T-073 Faction-Reputation + T-111 Auction
- **Allianz-Stationen** → T-093

## Files

**Neu:**
- `src/POI/Model/SpaceStation.php` (extends Poi, STI-Subtype, embedded CargoManifest)
- `src/POI/ValueObject/StationStatus.php`
- `src/POI/Command/{BuildSpaceStationCommand,BuildSpaceStationCommandHandler}.php`
- `src/POI/Service/BuildSpaceStationCommandService.php`
- `src/POI/Exception/{PlayerNotFoundException,SolarSystemNotFoundException,StationAlreadyExistsInSystemException,MissingShipyardInSystemException,InsufficientResourcesException,InsufficientPopulationException}.php`
- `migrations/Version20260619000012.php`
- `tests/POI/Command/BuildSpaceStationCommandTest.php`

**Geändert:**
- `src/POI/Model/Poi.php` (DiscriminatorMap: 'station' → SpaceStation::class)
