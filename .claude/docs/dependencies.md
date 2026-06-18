# Domain Dependencies

Aktueller Zustand (knapp):

```
Player → Planet (owns PlanetCollection)
Planet → Building, Resource, ResourceDeposit
Tick → Planet (mutates resources/deposits via Processors)
GameState → Player + Clock
Simulation → CommandBus, TickEngine
```

Geplant (siehe Tickets):

```
Player → SolarSystem → Planet
SolarSystem → POI, Raumstation, Flotte
Flotte → Raumschiff
Raumschiff → Sonde (transport)
Forschung → Antriebstechnologie, Planetologie
Building (Eisenhütte) → Resource (Eisenerz + Kohle → Eisenbarren)
```
