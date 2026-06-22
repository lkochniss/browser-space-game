# Decisions

| Date | Decision | Reason |
|------|----------|--------|
| 2026-06-18 | Domain-Architektur (`src/<Domain>/`) | bereits etabliert in Code |
| 2026-06-18 | CommandBus + Handler pattern | bereits etabliert (`src/Common/Service/CommandBus.php`) |
| 2026-06-18 | Tick = 900s (15min) | aus `docs/Tick.md` + `TickEngine` default |
| 2026-06-18 | In-memory models, kein ORM-Mapping bislang | Doctrine installiert aber keine Entity-Attribute. Persistence-Strategie offen — siehe TD-005 |
| 2026-06-18 | Caveman-Tickets (DE/EN mix) | Token-Effizienz, User schreibt DE |
| 2026-06-18 | **Game-Mode = Multi-Player** | Game-Vision (Raumschlacht, Allianz, Handel) impliziert Multi. PvP-Mechaniken zentraler Bestandteil. Tickets T-052/053/054 bleiben relevant |
| 2026-06-18 | **Persistence = Doctrine ORM + Domain-Events** | ORM bereits installiert + Symfony-Standard. Domain-Events via Symfony Messenger als Async-Kanal (Schlachten, Notifications, Score). Volles Event-Sourcing bewusst NICHT initial — kann später ohne Code-Verlust nachgerüstet werden, da Domain-Events bereits etabliert sind |
| 2026-06-18 | **Event-Modell = B (Hybrid: ORM + Domain-Events)** | 90% der Event-Vorteile (Async, Decoupling, Scaling) ohne Wochen Plumbing. Read-Models in ORM, State-Mutations dispatchen Events; Subscriber/Handler über Messenger asynchron |
| 2026-06-18 | Tests via In-Memory SQLite | Standard-Approach. Reicht für Integration-Tests. Prod = MySQL (composer.json `doctrine/dbal:^3`) |
| 2026-06-18 | Aggregate-Inverse-Setter-Pattern | Building/Resource/Deposit haben nullable `?Planet $planet`-Setter; `Planet::addX()` setzt Inverse. Pragmatisch, weil Factories an mehreren Stellen ohne Planet-Ref entstehen. Alternative (Planet-Ref im Konstruktor) wäre breaking + factory-spread. |
| 2026-06-18 | Initial-Migration handgeschrieben (Schema-API) | `doctrine:migrations:diff` braucht Live-DB. Ohne MySQL-Container nutzen wir `Schema`-Builder-API (plattform-agnostisch) für `Version20260618000001`. Folge-Migrations können wieder per `diff` generiert werden, sobald MySQL läuft. |
| 2026-06-22 | **Storage-Volume in m³ (Generic-Storage Foundation T-180)** | Eine physikalische Einheit pro Storage-Bucket statt Resource-Type-spezifischer Caps. Player-intuitiv (1 m³ = bekannte Welt-Einheit). Vision T-177/T-178/T-179: Planet hat einen generischen Volume-Bucket, jede Resource + Pop nimmt m³ proportional zum `ResourceVolumeConfig`-Multiplier. Working-Pop = 10 m³/Person, Wasser = 1 m³ als Reference. T-061 Resource-Category-Caps werden in T-177ff superseded. |
