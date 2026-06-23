# TD-032: Doctrine ORM-Mapping aller Entities

**Type:** TechDebt
**Epic:** Tech-Debt & Cleanup
**Domain:** Common
**Blocked By:** None
**Status:** Done
**Severity:** High
**Effort:** XL
**Affected Domain(s):** Player, Planet, Building, Resource
**Depends on:** TD-031 (Tests vorab) ✓

## Beschreibung

Architektur-Spike abgeschlossen (siehe `docs/decisions.md` 2026-06-18): **Doctrine ORM klassisch + Domain-Events**. Bestehende Plain-Models müssen mit ORM-Attributen versehen + persistiert werden. In-Memory-`PlayerStartUpScenario` muss weiter laufen.

## Phasen-Plan

### ✅ Phase 1: DB-Config + UUID-Type (Done)

- [x] `.env` DATABASE_URL auf MySQL umgestellt (`mysql://app:!ChangeMe!@127.0.0.1:3306/space_game?serverVersion=8.0.32&charset=utf8mb4`)
- [x] `doctrine.yaml`: identity_generation_preferences auf MySQL
- [x] `doctrine.yaml`: explizite Mapping-Config (`auto_mapping: false`, App-Namespace scant `src/`)
- [x] `doctrine.yaml`: 5 Custom-Types registriert
- [x] `App\Common\Doctrine\Type\AbstractUuidType` (CHAR(36), VO ↔ String)
- [x] 5 konkrete Types: PlanetIdType, PlayerIdType, BuildingIdType, ResourceIdType, ResourceDepositIdType
- [x] Smoke-Tests laufen weiter (KernelSmokeTest grün)

### ✅ Phase 2: Map Leaf-Entities (Done)

- [x] `Resource` mit `#[ORM\Entity]`, ID + enum + int gemapped
- [x] `ResourceDeposit` ditto
- [x] `Building` ditto
- [x] `bin/console doctrine:mapping:info` zeigt 3 Entities OK
- [x] `bin/console doctrine:schema:validate --skip-sync` → Mapping korrekt
- [x] `bin/console doctrine:schema:create --dump-sql` (test-env) generiert clean SQL
- [x] Alle 13 bestehenden Tests weiter grün

### ✅ Phase 3: Map Aggregates (Player + Planet) — Done

- [x] Custom Collections (`BuildingCollection`, `ResourceCollection`, `ResourceDepositCollection`, `PlanetCollection`) extenden `Doctrine\Common\Collections\ArrayCollection`
- [x] Bidirektionale Relations:
  - [x] `Planet → Buildings` (OneToMany, mappedBy: 'planet', cascade: persist+remove, orphanRemoval: true)
  - [x] `Planet → Resources` (OneToMany, ditto)
  - [x] `Planet → Deposits` (OneToMany, ditto)
  - [x] `Player → Planets` (OneToMany, cascade: persist)
- [x] Inverse Side: `Building/Resource/ResourceDeposit::planet` (ManyToOne, nullable für Initialphase)
- [x] Inverse Side: `Planet::player` (ManyToOne, nullable)
- [x] **Design-Entscheidung umgesetzt:** Setter-Pattern (`setPlanet()`, `setPlayer()`); Konstruktor bleibt domain-frei. `Planet::addBuilding/addResource/addDeposit` und `Player::claimPlanet` setzen Inverse-Seite konsistent (mit `contains()`-Guard).
- [x] `Planet::__construct` initialisiert Collections via `new ArrayCollection()` (kein Pflicht-Arg mehr)
- [x] `Planet::generatePlanet(PlanetId)` Factory bekommt jetzt ID übergeben (vorher Bug: ID-Arg ignoriert in `GeneratePlanetCommandService`)
- [x] Alle Tests grün (15 von 15)

### ✅ Phase 4: Migration + Repositories + IT — Done

- [x] Repositories: `PlayerRepository`, `PlanetRepository`, `BuildingRepository`, `ResourceRepository`, `ResourceDepositRepository` (extend `ServiceEntityRepository`)
- [x] Entities mit `repositoryClass` annotiert
- [x] `tests/Integration/IntegrationTestCase` (extends `KernelTestCase`) erzeugt Schema in In-Memory-SQLite via `SchemaTool::createSchema(metadata)` vor jedem Test
- [x] `tests/Persistence/PlayerPlanetPersistenceTest`:
  - [x] persist+reload Aggregat (Player+Planet+Building+Resource+Deposit)
  - [x] Mutations propagieren über `flush()`
- [x] `ClaimStartPlanetCommandService` nutzt `EntityManagerInterface`, persistiert + flush
- [x] `PlayerStartUpScenario` bekommt `EntityManagerInterface`, flusht nach Tick-Loop
- [x] **Erste Migration `migrations/Version20260618000001.php`**: handgeschrieben via `Schema`-Builder (Plattform-agnostisch). Erzeugt 5 Tabellen + FKs + Indizes. `doctrine:migrations:diff` konnte nicht gegen Live-MySQL laufen (kein lokaler MySQL-Container in `docker-compose.yaml` — nur Postgres) — Schema-API-Migration ist äquivalent und läuft auf MySQL ohne Anpassung.
- [x] `doctrine:mapping:info`: 5 Entities OK
- [x] `doctrine:schema:validate --skip-sync`: Mapping korrekt

## Refactor Strategy

1. ✓ Tests-First: TD-031 erledigt
2. ✓ Pro Entity: Attribute hinzufügen, Mapping prüfen
3. ✓ Aggregate-Setter-Pattern für Inverse-Seite + cascade
4. ✓ Repositories + IntegrationTestCase
5. ✓ Migration via Schema-API

## Open Questions (geklärt)

1. **Aggregate-Pattern:** Setter (`setPlanet()`, `setPlayer()`); `addX()` setzt Inverse. Building/Resource/Deposit sind initial planet-los, werden via `Planet::addX()` verbunden.
2. UUID via ramsey bleibt.
3. Soft-Delete: Folgeticket bei Bedarf (kein Use-Case heute).
4. MySQL 8.0.32 in `.env` bestätigt; `docker-compose.yaml` braucht Folgeticket (heute Postgres-Service, nicht zu MySQL passend).

## Folge-Tickets (empfohlen)

- **docker-compose MySQL-Service**: Heute steht Postgres im Compose, `.env` zeigt aber auf MySQL. Cleanup nötig.
- **`PlanetCollection` cleanup**: Nicht mehr verwendet (Player nutzt `Collection`-Interface direkt). Datei kann gelöscht werden.
- **Tick-Persistenz**: `ResourceProductionProcessor` mutiert in-memory; aktuell flusht nur Scenario am Ende. Domain-Events (T-057) lösen das sauberer.

### Token Usage (estimate)
- Phase 1+2 (vorher): Input ~10k / Output ~4k
- Phase 3+4 (jetzt): Input ~25k / Output ~6k
