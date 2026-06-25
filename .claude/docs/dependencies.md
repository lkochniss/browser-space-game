# Domain Dependencies

Aktueller Zustand (knapp):

```
Player ⇄ Planet  (Player::planets OneToMany; Planet::player ManyToOne, nullable)
Planet ⇄ Building / Resource / ResourceDeposit  (OneToMany cascade persist+remove + orphanRemoval)
Planet → Population  (Embedded VO — total/assigned/cap)
Planet → SolarSystem  (ManyToOne, nullable, T-007 — System ist Container)
Planet → PlanetType + PlanetSize  (Enums, T-008 — Type → Consumption-Multi + Deposit-Bias, Size → Deposit-Multi)
SolarSystem ⇄ Planet  (OneToMany cascade persist)
Tick → Planet (mutates resources/deposits/population via Processors; flush via Engine wrapInTransaction)
PopulationConsumptionProcessor → Resource (W/F) + Population (× PlanetType-Multi T-008)
RefinementProductionProcessor → Resource (consumes Erze, produces Erzeugnisse via RefinementConfig recipes)
Tick-Order: ResourceProduction (Mining) → RefinementProduction → PopulationConsumption
GameState → Player + Clock
Simulation → CommandBus, TickEngine, EntityManager
ClaimStartPlanetCommandService → EntityManager (persist + flush Aggregat)
BuildBuildingCommandService → PlanetRepository + BuildingCostConfig + EM (debit/assign/persist)
UpgradeBuildingCommandService → PlanetRepository + BuildingCostConfig + EM (scaled cost + cap-recalc)
```

T-178 Ship-Cargo-Universal:
```
Ship → ShipCargo (Embeddable, volume-based, T-178)
Ship.cargoVolumeCapacity → ShipCargoVolumeConfig (per ShipType + ShipClass+Mk)
LoadCargoCommand / UnloadCargoCommand → all Ships (kein Transport-Filter)
SalvageProcessor → Ship.maxAddableResource (volume-aware extract-cap)
TradeRouteProcessor / CreateTradeRouteCommandService → volume-based check
SpaceStation.storage bleibt CargoManifest (units) bis T-183
```

Geplant (siehe Tickets):

```
User (Account) → Player (Spielfigur)
Player → SolarSystem → Planet
SolarSystem → POI, Raumstation, Flotte
Flotte → Raumschiff
Raumschiff → Sonde (transport)
Forschung → Antriebstechnologie, Planetologie
Higher-Tier Buildings → Erzeugnisse (z.B. Raumwerft → IRON_BAR statt IRON_ORE)
```

## Event-Flow (geplant — siehe T-057)

```
TickProcessor → dispatch DomainEvent → Messenger Bus →
   ├→ Persistence-Subscriber (ORM-Save)
   ├→ Notification-Subscriber (T-047)
   ├→ Score-Subscriber (T-054)
   └→ Mailer-Subscriber (T-053-Email)

Beispiele:
- ResourceProducedEvent
- BuildingCompletedEvent
- BattleResolvedEvent → erzeugt DebrisFieldCreatedEvent + PopulationKilledEvent
- ResearchCompletedEvent
- FleetArrivedEvent
- ProbeReturnedEvent
```
