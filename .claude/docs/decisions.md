# Decisions

| Date | Decision | Reason |
|------|----------|--------|
| 2026-06-18 | Domain-Architektur (`src/<Domain>/`) | bereits etabliert in Code |
| 2026-06-18 | CommandBus + Handler pattern | bereits etabliert (`src/Common/Service/CommandBus.php`) |
| 2026-06-18 | Tick = 900s (15min) | aus `docs/Tick.md` + `TickEngine` default |
| 2026-06-18 | In-memory models, kein ORM-Mapping bislang | Doctrine installiert aber keine Entity-Attribute. Persistence-Strategie offen — siehe TD-005 |
| 2026-06-18 | Caveman-Tickets (DE/EN mix) | Token-Effizienz, User schreibt DE |
