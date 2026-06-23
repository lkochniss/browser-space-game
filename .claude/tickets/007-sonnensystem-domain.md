# T-007: Sonnensystem-Domain

**Type:** Feature
**Epic:** Foundation: Galaxy
**Domain:** SolarSystem
**Blocked By:** None
**Status:** Done
**FX:** No
**MIG:** Yes (`Version20260619000001` — solar_systems Tabelle + planets.solar_system_id FK)

## Description

Docs referenzieren `[[Sonnensystem]]`. Aktuell: `Player → Planeten` direkt, kein System-Layer. T-007 fügt SolarSystem als Domain hinzu — Planet gehört einem System, Player besitzt Planeten in Systemen (Systeme shared).

## AC

- [x] Neue Domain `src/SolarSystem/`
- [x] `SolarSystem` Entity (Repo, ID, Name, Planets-OneToMany via mappedBy='solarSystem')
- [x] `SolarSystemId` ValueObject + `SolarSystemIdType` Doctrine-Type registriert
- [x] `Planet::solarSystem` ManyToOne (nullable für Übergangsphase / unowned-Planeten)
- [x] `Player::planets` bleibt direkter Shortcut (orthogonale Achse — Player kennt seine Planeten direkt, System ist Lookup)
- [x] `ClaimStartPlanetCommandService` generiert 5 Systeme (`START_GALAXY_SYSTEM_COUNT`); Player startet in System 0, andere 4 Systeme haben je 1 unowned Planet
- [x] `SolarSystem::generate(id)` Auto-Name: `Sol-{4-hex-chars-of-uuid}` (z.B. `Sol-7A3F`)
- [x] `PlayerStartUpScenario` läuft weiter (alle bestehenden + neue Tests grün)
- [x] Migration `Version20260619000001` erstellt Tabelle + FK + Index
- [x] Bestehende Tests grün (122/122, +6 neu: 3 SolarSystemTest unit + 3 IT in Claim-Service)

## Geklärte Fragen

1. **Player ↔ System:** Player besitzt Planeten in Systemen; Systeme shared (kein Owner)
2. **`Player::getPlanets()`:** Bleibt direkter Shortcut (orthogonale Beziehung)
3. **Start-Galaxie:** 5 Systeme werden generiert; Player startet in System 0
4. **System-Name:** Auto-generiert aus UUID-Prefix (`Sol-{4hex}`)

## Implementation

- `src/SolarSystem/Model/SolarSystem.php` (neu, Entity)
- `src/SolarSystem/ValueObject/SolarSystemId.php` (neu)
- `src/SolarSystem/Repository/SolarSystemRepository.php` (neu)
- `src/Common/Doctrine/Type/SolarSystemIdType.php` (neu)
- `config/packages/doctrine.yaml` (+`solar_system_id` Type-Registrierung)
- `src/Planet/Model/Planet.php` (+ ManyToOne SolarSystem, Getter/Setter)
- `src/Planet/Service/ClaimStartPlanetCommandService.php` (refactor: 5-System-Galaxy)
- `migrations/Version20260619000001.php` (neu)
- `tests/SolarSystem/Model/SolarSystemTest.php` (3 Cases)
- `tests/Planet/Service/ClaimStartPlanetCommandServiceTest.php` (+3 Cases für galaxy/system/owner)

## Edge Cases (getestet)

- `SolarSystem::generate` mit known UUID erzeugt erwarteten Namen
- `addPlanet` setzt Inverse-Seite, idempotent
- Galaxy hat 5 Systeme, jedes mit 1 Planet
- Start-Planet hat zugewiesenes System
- Nur Start-Planet hat Player (4 unowned)

## Bekannte Lücken / Folge-Tickets

- **T-008** Planet-Typen + Größen: alle 5 Planeten heute identisch generiert
- **T-018** Teleskop-Discovery: Player kann andere Systeme noch nicht "sehen"
- **T-019/T-020** POIs in Systemen (Asteroidenfelder etc.)
- **T-014** Kolonisationsschiff: nutzt unowned Planeten in benachbarten Systemen
- `GeneratePlanetCommand` ist heute redundant geworden — der ID-Forward-Trick wird gar nicht mehr genutzt. Kandidat für TechDebt-Cleanup.

### Token Usage (estimate)
- Input: ~12k
- Output: ~5k
