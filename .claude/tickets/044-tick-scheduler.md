# T-044: Tick-Scheduler — Background-Worker

**Type:** Feature
**Status:** Open
**FX:** No
**MIG:** No
**Depends on:** TD-032

## Description

Aktuell läuft Tick nur in `PlayerStartUpScenario` synchron. Browser-Game braucht Tick alle 15 min für ALLE Spieler — automatisiert. Optionen:

1. **Cron** ruft `tick:run` Command (einfachster Weg)
2. **Symfony Messenger** mit Scheduler-Component
3. **Kombi:** Cron triggert Messenger-Dispatch, Worker verarbeitet asynchron

## AC

- [ ] Entscheidung dokumentiert (`docs/decisions.md`)
- [ ] `bin/console game:tick:run` Command — verarbeitet alle Player/SolarSystems
- [ ] Idempotenz: doppeltes Ausführen pro Tick-Slot tut nichts (`lastTickAt` pro GameState/SolarSystem)
- [ ] Lock gegen parallele Runs (`lock`-Component oder DB-Advisory-Lock)
- [ ] Logging: pro Tick Anzahl verarbeitet, Dauer, Fehler
- [ ] Falls Messenger: `messenger:consume` Worker-Definition + Supervisor-Doc
- [ ] Health-Check (T-048) prüft "letzter Tick < 20min"

## Affected

- Neu: `src/Tick/Command/RunTickCommand.php` (Console-Command)
- Neu: `src/Tick/Service/TickScheduler.php`
- evtl. `composer require symfony/messenger symfony/scheduler symfony/lock`
- `docker-compose.yaml` (Worker-Service falls Messenger)

## Open Questions

1. Cron-Only vs Messenger? Cron reicht initial. Messenger sinnvoll wenn Async-Aktionen wachsen (Schlachten, Mails, etc.).
2. Lock-Backend: Datei, Redis, DB? Vorschlag: DB (`PdoStore`).
3. Multi-Tenant-Tick: alles in einem Run, oder pro SolarSystem ein Job (parallelisierbar)?
