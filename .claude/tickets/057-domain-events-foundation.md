# T-057: Domain-Events Foundation

**Type:** Feature
**Epic:** Web Layer & Auth
**Domain:** Common
**Blocked By:** None
**Status:** Open
**FX:** No
**MIG:** No
**Depends on:** TD-031 (Tests), TD-032 (ORM)

## Description

Architektur-Entscheidung 2026-06-18: **ORM + Domain-Events** als Persistence/Event-Strategie (siehe `docs/decisions.md`). Diese Foundation legt Bus, Interfaces und Konventionen fest, bevor erste Subscriber/Producer (T-024 Battle, T-025 Research, T-047 Notifications, T-054 Score) gebaut werden.

## AC

- [ ] `composer require symfony/messenger`
- [ ] Konfiguration: `domain_events`-Bus + `async`-Bus + Default-Bus
- [ ] Transports: `sync` (Tests/Dev-Default für deterministische Tests), `doctrine` (Async-Default für Prod)
- [ ] Interface `App\Common\Event\DomainEvent` (mit `occurredAt`, `aggregateId`)
- [ ] Abstrakte Aggregate-Basis (oder Trait): `RecordsEvents` — sammelt Events bis Persistierung
- [ ] EventDispatcher-Helper im CommandBus integriert (Events nach erfolgreichem Handle dispatchen)
- [ ] Subscriber-Beispiel: `LogDomainEventSubscriber` (loggt jedes Event in `tick`-Channel)
- [ ] Test-Helper: `assertEventDispatched(EventClass)` für IT
- [ ] Doku in `docs/decisions.md` Verweis + Beispielcode in `docs/`-Doc

## Affected

- `composer.json`
- Neu: `src/Common/Event/DomainEvent.php`, `RecordsEvents.php`, `EventDispatcher`-Glue
- `config/packages/messenger.yaml`
- `src/Common/Service/CommandBus.php` (Events nach Handle dispatchen)

## Open Questions

1. Async-Default für ALLE Events oder pro Event entscheidbar via Stamp/Interface? Vorschlag: pro Event-Klasse via Marker-Interface (`AsyncDomainEvent`).
2. Outbox-Pattern jetzt schon (Events erst nach DB-Commit dispatchen) oder später? Vorschlag: jetzt — verhindert Inkonsistenzen wenn DB-Commit fehlschlägt aber Event schon raus ist.
3. Event-Versionierung jetzt schon vorsehen (z.B. `getVersion(): int`)?
