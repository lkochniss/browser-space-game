# TD-060: Tick-Mutationen persistieren

**Type:** TechDebt
**Status:** Done
**Severity:** High
**Effort:** M (1-4h)
**Affected Domain(s):** Tick, Simulation
**Depends on:** TD-032 ✓

## Beschreibung

`ResourceProductionProcessor` mutiert in-memory. Vor TD-060 flusht nur `PlayerStartUpScenario` am Ende — fragil bei Multi-Tick-Setups, kein Atomicity-Schutz, Worker-Crash → Datenverlust.

## Acceptance Criteria

- [x] **Strategie: Option A** (pragmatisch, ohne T-057 Domain-Event-Bus). Sauberer Re-Implement via Events ist zukünftig möglich.
- [x] `TickEngine` bekommt `EntityManagerInterface` injiziert
- [x] `TickEngine::run()` umschließt Processor-Loop in `EntityManagerInterface::wrapInTransaction(...)`; `flush()` am Ende der Transaction
- [x] `PlayerStartUpScenario`: redundanter Trail-Flush entfernt (Engine flusht jetzt pro Tick)
- [x] Integration-Test `tests/Persistence/TickPersistenceTest`:
  - [x] Single-Tick: 1000 Deposit → nach `run()` + `clear()` + Reload → 990 Deposit / 10 Resource
  - [x] Multi-Tick (3×): 100 Deposit → 70 / 30 nach 3 Engine-Calls (jeder atomar)
- [x] Bestehende Tests grün (17/17)

## Refactor Strategy

- TickEngine ctor: `(iterable $processors, EntityManagerInterface $em, int $intervalSeconds = 900)`
- `run()` nutzt `wrapInTransaction(fn() => process loop + flush)`
- Scenario passt Engine-Konstruktion an, entfernt eigenen Trail-Flush
- IT erzeugt persistiertes Aggregat → reload → Engine-Lauf → erneuter reload → assert

## Performance-Note

- Bei vielen Planeten pro Tick kann ein einziger `flush()` viel UnitOfWork-Tracking bedeuten. Heute akzeptabel (1 Planet/Player). Für späteres Scaling → batch via `iterate()` + intermittentes `clear()` (siehe `persistence.md`)
- Ein Tick = eine Transaktion. Multi-Tick-Loops sind serielle Transaktionen — kein DB-Lock-Contention bei Single-Worker

## Folge-Optionen (nicht in Scope)

- **Option B (sauber):** ProductionProcessor dispatcht `ResourceProducedEvent` → Persistence-Subscriber speichert. Setzt T-057 voraus. Vorteil: Audit-Trail, Async-Erweiterung.
- **Per-Planet-Transactions:** Ein FAIL pro Planet rollback’t nur den, nicht den ganzen Tick.
- **Optimistic Locking:** Versionsfeld auf Planet/Resource für Multi-Worker-Schutz.

## Affected Tests

- `tests/Persistence/TickPersistenceTest` (neu) — 2 Cases
- `tests/Tick/Processor/ResourceProductionProcessorTest` (Unit, unverändert)

## Fixtures Needed

- Nein (IT baut Aggregat im Setup)

### Token Usage (estimate)
- Input: ~5k
- Output: ~3k
