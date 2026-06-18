# TD-032: Models ohne ORM-Mapping — Persistence offen

**Type:** TechDebt
**Status:** Open
**Severity:** High
**Effort:** XL
**Affected Domain(s):** alle

## Beschreibung

Doctrine ORM + Migrations sind als Composer-Deps installiert, aber **kein einziges** Model hat ORM-Attribute. Spiel läuft nur als In-Memory-CLI-Sim (`PlayerStartUpScenario`). Sobald Web-Layer / Multi-Session / Tick-Cron gewünscht ist → Persistence-Strategie nötig.

## Risk if ignored

Ohne klare Persistenz-Strategie wird jedes neue Feature-Ticket ad-hoc zwischen "in-memory" und "Wenn ORM dann anders" schwanken. Fundamentaler Architektur-Punkt — sollte vor zu vielen Feature-Tickets entschieden sein.

## AC

- [ ] Architektur-Entscheidung dokumentiert (`docs/decisions.md`): ORM oder Event-Sourcing oder reines In-Memory mit Snapshot
- [ ] Bei ORM: alle bestehenden Entities mit `#[ORM\Entity]` + Repos mappen
- [ ] Erste Migration für Schema
- [ ] `PlayerStartUpScenario` weiterhin ausführbar (mit oder ohne Persistenz-Mode)
- [ ] In-Memory-SQLite-Setup für Tests (T-031 Vorbedingung)

## Refactor Strategy

Erst **Spike-Ticket**: 1–2h Spike mit zwei Optionen (ORM vs. Event-Sourcing) + Empfehlung. Dann erst dieses Ticket umsetzen.

## Open Questions

1. Single-Player oder Multi-Player Browser-Game? Persistenz-Anforderung skaliert anders.
2. Tick-Verarbeitung: synchron pro Request, Cron-Worker, oder Messenger-Async?
