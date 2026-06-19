# T-094 Bau-Queue

**Type:** Feature
**Status:** Draft
**Effort:** M
**Depends on:** T-009 (Building-Cost), T-062 (Echtzeit-Bauzeit)
**Blocks:** T-162 (Build-Templates)

## Beschreibung
Mehrere Building-Tasks queuen pro Planet. MVP: 3 Slots in Queue. Höhere Slots durch Forschung/Buildings unlocked.

Queue-Slot-Reservation: Zweiter Build wird erst gestartet wenn erster fertig (oder upgrade analog).

## Acceptance Criteria
- [ ] BuildQueue-Entity pro Planet (slots: int, queue: List<BuildJob>)
- [ ] BuildJob: { type: 'CONSTRUCT'|'UPGRADE', buildingType, level, scheduledStartAt, scheduledEndAt, status }
- [ ] BuildBuildingCommand (T-009) checkt Queue-Slot frei → reserviert oder rejected
- [ ] Cancel-Job: Refund-Mechanik (50% Resources zurück)
- [ ] Queue-Sloterhöhung via Hub-Upgrade: +1 Slot pro Hub-Lvl-5
- [ ] Forschung: Logistics-Branch erhöht Slots weiter (T-025 Branch)
- [ ] Auto-Start: nächster Job startet automatisch wenn vorheriger fertig
- [ ] UI (sobald Web-Layer): Queue-Anzeige mit ETAs

## Affected Tests
- tests/Building/Service/BuildQueueTest.php (slot-reservation, auto-start)
- tests/Building/Service/BuildQueueRefundTest.php

## Fixtures Needed
No — testbar mit existing setup

## Notes
- Pop-Reservation während gequeueter Job pending: erst beim Start, nicht beim Queueing
- Resource-Reservation analog: erst beim Start abgebucht (verhindert Blockade durch leere Queue)
