# Persistence

Doctrine ORM 3.x klassisch + Custom UUID-Types. MySQL 8 (Prod), In-Memory SQLite (Tests).

## Mapping (Stand TD-032)

| Entity | Table | ID-Type | Relations |
|--------|-------|---------|-----------|
| Player | `players` | `player_id` (CHAR 36) | OneToMany → Planet (mappedBy `player`, cascade persist) |
| Planet | `planets` | `planet_id` | ManyToOne → Player (nullable). OneToMany → Building/Resource/ResourceDeposit (cascade persist+remove, orphanRemoval) |
| Building | `buildings` | `building_id` | ManyToOne → Planet (nullable) |
| Resource | `resources` | `resource_id` | ManyToOne → Planet (nullable) |
| ResourceDeposit | `resource_deposits` | `resource_deposit_id` | ManyToOne → Planet (nullable) |

## Custom Doctrine Types

`src/Common/Doctrine/Type/AbstractUuidType.php` — Basis. `getStringTypeDeclarationSQL([length: 36, fixed: true])` ergibt `CHAR(36)` auf MySQL und SQLite.

| Name | Klasse | VO |
|------|--------|----|
| `player_id` | `PlayerIdType` | `Player\ValueObject\PlayerId` |
| `planet_id` | `PlanetIdType` | `Planet\ValueObject\PlanetId` |
| `building_id` | `BuildingIdType` | `Building\ValueObject\BuildingId` |
| `resource_id` | `ResourceIdType` | `Resource\ValueObject\ResourceId` |
| `resource_deposit_id` | `ResourceDepositIdType` | `Resource\ValueObject\ResourceDepositId` |

Registriert in `config/packages/doctrine.yaml` (`dbal.types`).

## Aggregate-Pattern

`Planet`/`Player` setzen Inverse-Seite über Helper:

```php
$planet->addBuilding($b);   // → $b->setPlanet($planet)
$planet->addResource($r);
$planet->addDeposit($d);
$player->claimPlanet($p);   // → $p->setPlayer($player)
```

Konstruktor von Building/Resource/ResourceDeposit ist Planet-frei (`setPlanet()`-Pattern). Begründung siehe `decisions.md`.

## Repositories

`src/<Domain>/Repository/<Entity>Repository.php` — extends `ServiceEntityRepository<Entity>`. Auto-wired via `repositoryClass: ...` Attribut auf Entity.

## Migrations

Pfad: `migrations/`. Namespace: `DoctrineMigrations`.

Initial-Migration `Version20260618000001` ist handgeschrieben (Schema-API). Folge-Migrations via:

```bash
bin/console doctrine:migrations:diff   # braucht laufenden MySQL
bin/console doctrine:migrations:migrate
```

## Tests

- **In-Memory SQLite** in `.env.test` (`DATABASE_URL="sqlite:///:memory:"`)
- `tests/Integration/IntegrationTestCase` boot-et Kernel und erzeugt Schema via `SchemaTool::createSchema(metadata)` pro Test
- Persistence-Tests in `tests/Persistence/`

## Wichtig

- Kein `setBuildings/setResources/setResourceDeposits/setPlanets`-Wholesale-Setter mehr — würde Doctrine `PersistentCollection` brechen. Stattdessen `addX()`/Collection-Methoden.
- `ClaimStartPlanetCommandService` persistiert + flusht nach Aggregat-Aufbau.
- `ResourceProductionProcessor` mutiert in-memory; `TickEngine` umschließt den Processor-Loop in `EntityManagerInterface::wrapInTransaction()` und flusht am Ende — pro Engine-Lauf eine Transaktion (TD-060). Saubere Variante via Domain-Events (T-057) ist zukünftig möglich.
