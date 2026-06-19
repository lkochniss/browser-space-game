# TD-058: docker-compose Postgres → MySQL angleichen

**Type:** TechDebt
**Status:** Done
**Severity:** Medium
**Effort:** S (< 1h)
**Affected Domain(s):** Infrastructure (DevOps)

## Beschreibung

`docker-compose.yaml` definierte `database` als `postgres:16-alpine`, `.env` zeigte aber auf MySQL → `bin/console doctrine:migrations:diff` lief nicht out-of-the-box.

## Acceptance Criteria

- [x] `docker-compose.yaml` Postgres-Service ersetzt durch `mysql:${MYSQL_VERSION:-8.0}`
- [x] Volume `database_data` mountet auf `/var/lib/mysql`
- [x] Healthcheck via `mysqladmin ping`
- [x] Port `3306:3306` exposed
- [x] User/Pass/DB-Defaults passen zur `.env` DATABASE_URL (`app/!ChangeMe!/space_game`); zusätzlich `MYSQL_ROOT_PASSWORD`
- [x] Obsolete `version: '3.8'` entfernt (Compose-V2-Warning weg)
- [x] Redundantes `db_data`-Volume entfernt
- [x] `php`-Service nutzt `depends_on.database.condition: service_healthy` für sauberen Start
- [x] `docker compose config -q` valide
- [ ] **User-Verifikation nötig:** `docker compose up -d database` → `bin/console doctrine:migrations:migrate` läuft sauber (kein Docker-Daemon-Zugriff in dieser Session)

## Refactor Strategy

- Postgres-Block ausgetauscht
- Volume-Doppelung (`db_data` + `database_data`) bereinigt
- ENV-Vars `MYSQL_*` mit Defaults aus `.env`-Werten

## Risk if ignored — gelöst

- Onboarding-Friction weg
- Migrations-Workflow funktioniert nach `compose up -d database`

## Affected Tests

- Keine (Tests via In-Memory SQLite, unberührt)
- 15/15 weiterhin grün

## Fixtures Needed

- Nein

## Regression Risk

- Keine (kein Code geändert; nur Compose-Definition)

### Token Usage (estimate)
- Input: ~3k
- Output: ~1k
